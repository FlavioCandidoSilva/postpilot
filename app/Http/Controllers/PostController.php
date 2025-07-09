<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\QueueService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Carbon\Carbon;

class PostController extends Controller
{
    protected $openAIService;
    protected $queueService;

    /**
     * Construtor do controlador.
     */
    public function __construct(OpenAIService $openAIService, QueueService $queueService)
    {
        $this->middleware(['auth', 'verified'])->except(['apiIndex', 'apiShow']);
        $this->openAIService = $openAIService;
        $this->queueService = $queueService;
    }

    /**
     * Exibe a página de listagem de postagens.
     */
    public function index()
    {
        $user = Auth::user();
        $posts = Post::where('user_id', $user->id)
            ->with(['category', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('Posts/Index', [
            'posts' => $posts
        ]);
    }

    /**
     * Exibe o formulário de criação de postagem.
     */
    public function create()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        $tags = Tag::where('user_id', Auth::id())->get();
        
        return Inertia::render('Posts/Create', [
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    /**
     * Armazena uma nova postagem.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:rascunho,pronto,publicado',
            'scheduled_at' => 'nullable|date|after_or_equal:now'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->category_id = $request->category_id;
        $post->status = $request->status;
        $post->user_id = Auth::id();
        
        if ($request->scheduled_at) {
            $post->scheduled_at = $request->scheduled_at;
            $post->queue_status = 'pending';
        }
        
        $post->save();
        
        if ($request->tags) {
            $post->tags()->sync($request->tags);
        }
        
        return redirect()->route('posts.index')
            ->with('success', 'Postagem criada com sucesso!');
    }

    /**
     * Exibe uma postagem específica.
     */
    public function show(Post $post)
    {
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $post->load(['category', 'tags']);
        
        return Inertia::render('Posts/Show', [
            'post' => $post
        ]);
    }

    /**
     * Exibe o formulário de edição de postagem.
     */
    public function edit(Post $post)
    {
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $post->load(['category', 'tags']);
        $categories = Category::where('user_id', Auth::id())->get();
        $tags = Tag::where('user_id', Auth::id())->get();
        
        return Inertia::render('Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    /**
     * Atualiza uma postagem específica.
     */
    public function update(Request $request, Post $post)
    {
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:rascunho,pronto,publicado',
            'scheduled_at' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $post->title = $request->title;
        $post->content = $request->content;
        $post->category_id = $request->category_id;
        $post->status = $request->status;
        
        // Atualizar agendamento
        $oldScheduledAt = $post->scheduled_at;
        $newScheduledAt = $request->scheduled_at;
        
        if ($newScheduledAt) {
            $post->scheduled_at = $newScheduledAt;
            
            // Se a data mudou ou o status mudou para 'pronto', atualizar status da fila
            if ((!$oldScheduledAt || $oldScheduledAt->ne($newScheduledAt)) || 
                ($post->status === 'pronto' && $post->queue_status !== 'pending')) {
                $post->queue_status = 'pending';
                $post->failure_reason = null;
                $post->retry_count = 0;
            }
        } else {
            $post->scheduled_at = null;
            $post->queue_status = null;
        }
        
        $post->save();
        
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        
        return redirect()->route('posts.index')
            ->with('success', 'Postagem atualizada com sucesso!');
    }

    /**
     * Remove uma postagem específica.
     */
    public function destroy(Post $post)
    {
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $post->delete();
        
        return redirect()->route('posts.index')
            ->with('success', 'Postagem excluída com sucesso!');
    }

    /**
     * Exibe o calendário de postagens.
     */
    public function calendar()
    {
        return Inertia::render('Posts/Calendar');
    }

    /**
     * API: Retorna a lista de postagens.
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $query = Post::query()->where('user_id', $user->id);
        
        // Filtros
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('tag_id') && $request->tag_id) {
            $query->whereHas('tags', function($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Ordenação
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Carregar relacionamentos
        $query->with(['category', 'tags']);
        
        // Paginação
        $perPage = $request->per_page ?? 10;
        $posts = $query->paginate($perPage);
        
        return response()->json($posts);
    }

    /**
     * API: Retorna uma postagem específica.
     */
    public function apiShow($id)
    {
        $post = Post::with(['category', 'tags'])->findOrFail($id);
        
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Acesso não autorizado'
            ], 403);
        }
        
        return response()->json($post);
    }

    /**
     * API: Armazena uma nova postagem.
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:rascunho,pronto,publicado',
            'scheduled_at' => 'nullable|date|after_or_equal:now'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $post = new Post();
        $post->title = $request->title;
        $post->content = $request->content;
        $post->category_id = $request->category_id;
        $post->status = $request->status;
        $post->user_id = Auth::id();
        
        if ($request->scheduled_at) {
            $post->scheduled_at = $request->scheduled_at;
            $post->queue_status = 'pending';
        }
        
        $post->save();
        
        if ($request->tags) {
            $post->tags()->sync($request->tags);
        }
        
        // Carregar relacionamentos
        $post->load(['category', 'tags']);
        
        return response()->json([
            'message' => 'Postagem criada com sucesso',
            'post' => $post
        ], 201);
    }

    /**
     * API: Atualiza uma postagem específica.
     */
    public function apiUpdate(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Acesso não autorizado'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'required|in:rascunho,pronto,publicado',
            'scheduled_at' => 'nullable|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $post->title = $request->title;
        $post->content = $request->content;
        $post->category_id = $request->category_id;
        $post->status = $request->status;
        
        // Atualizar agendamento
        $oldScheduledAt = $post->scheduled_at;
        $newScheduledAt = $request->scheduled_at;
        
        if ($newScheduledAt) {
            $post->scheduled_at = $newScheduledAt;
            
            // Se a data mudou ou o status mudou para 'pronto', atualizar status da fila
            if ((!$oldScheduledAt || $oldScheduledAt->ne($newScheduledAt)) || 
                ($post->status === 'pronto' && $post->queue_status !== 'pending')) {
                $post->queue_status = 'pending';
                $post->failure_reason = null;
                $post->retry_count = 0;
            }
        } else {
            $post->scheduled_at = null;
            $post->queue_status = null;
        }
        
        $post->save();
        
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        
        // Carregar relacionamentos
        $post->load(['category', 'tags']);
        
        return response()->json([
            'message' => 'Postagem atualizada com sucesso',
            'post' => $post
        ]);
    }

    /**
     * API: Remove uma postagem específica.
     */
    public function apiDestroy($id)
    {
        $post = Post::findOrFail($id);
        
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Acesso não autorizado'
            ], 403);
        }
        
        $post->delete();
        
        return response()->json([
            'message' => 'Postagem excluída com sucesso'
        ]);
    }

    /**
     * API: Retorna eventos do calendário.
     */
    public function apiCalendarEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        
        $query = Post::where('user_id', Auth::id())
            ->whereNotNull('scheduled_at');
        
        if ($start) {
            $query->where('scheduled_at', '>=', $start);
        }
        
        if ($end) {
            $query->where('scheduled_at', '<=', $end);
        }
        
        $posts = $query->with(['category'])->get();
        
        $events = $posts->map(function($post) {
            $color = '#3788d8'; // Azul padrão
            
            // Definir cor com base no status da fila
            switch ($post->queue_status) {
                case 'published':
                    $color = '#28a745'; // Verde
                    break;
                case 'failed':
                    $color = '#dc3545'; // Vermelho
                    break;
                case 'processing':
                    $color = '#17a2b8'; // Ciano
                    break;
                case 'cancelled':
                    $color = '#6c757d'; // Cinza
                    break;
            }
            
            return [
                'id' => $post->id,
                'title' => $post->title,
                'start' => $post->scheduled_at->toIso8601String(),
                'end' => $post->scheduled_at->addHour()->toIso8601String(),
                'color' => $color,
                'extendedProps' => [
                    'status' => $post->status,
                    'queue_status' => $post->queue_status,
                    'category' => $post->category ? $post->category->name : null
                ]
            ];
        });
        
        return response()->json($events);
    }

    /**
     * API: Gera conteúdo com IA.
     */
    public function generateWithAI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'tone' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $generatedContent = $this->openAIService->generateContent(
                $request->prompt,
                $request->tone ?? 'profissional'
            );
            
            return response()->json([
                'generatedContent' => $generatedContent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao gerar conteúdo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Sugere horários ideais para postagem.
     */
    public function suggestTiming(Request $request)
    {
        try {
            $suggestedTimings = $this->openAIService->suggestPostingTimes();
            
            return response()->json([
                'suggestedTimings' => $suggestedTimings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao sugerir horários: ' . $e->getMessage()
            ], 500);
        }
    }
}
