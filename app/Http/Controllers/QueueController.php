<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class QueueController extends Controller
{
    protected $queueService;

    /**
     * Construtor do controlador.
     */
    public function __construct(QueueService $queueService)
    {
        $this->middleware(['auth', 'verified']);
        $this->queueService = $queueService;
    }

    /**
     * Exibe a página de gerenciamento da fila.
     */
    public function index()
    {
        $user = Auth::user();
        $queuedPosts = $this->queueService->getQueuedPosts(null, $user->id);
        $stats = $this->queueService->getQueueStats($user->id);
        
        return Inertia::render('Queue/Index', [
            'queuedPosts' => $queuedPosts,
            'stats' => $stats
        ]);
    }

    /**
     * API: Retorna a lista de postagens na fila.
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status');
        $queuedPosts = $this->queueService->getQueuedPosts($status, $user->id);
        
        return response()->json([
            'posts' => $queuedPosts
        ]);
    }

    /**
     * API: Retorna estatísticas da fila.
     */
    public function apiStats()
    {
        $user = Auth::user();
        $stats = $this->queueService->getQueueStats($user->id);
        
        return response()->json($stats);
    }

    /**
     * API: Reordena as postagens na fila.
     */
    public function apiReorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'integer|exists:posts,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verificar se todas as postagens pertencem ao usuário atual
        $user = Auth::user();
        $postIds = $request->post_ids;
        $userPosts = Post::whereIn('id', $postIds)
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();
        
        if (count($userPosts) !== count($postIds)) {
            return response()->json([
                'message' => 'Acesso não autorizado a uma ou mais postagens'
            ], 403);
        }
        
        $success = $this->queueService->reorderQueue($postIds);
        
        if ($success) {
            return response()->json([
                'message' => 'Fila reordenada com sucesso'
            ]);
        } else {
            return response()->json([
                'message' => 'Erro ao reordenar fila'
            ], 500);
        }
    }

    /**
     * API: Tenta novamente uma postagem que falhou.
     */
    public function apiRetry(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Acesso não autorizado'
            ], 403);
        }
        
        // Verificar se a postagem está em estado de falha
        if ($post->queue_status !== 'failed') {
            return response()->json([
                'message' => 'Apenas postagens com falha podem ser tentadas novamente'
            ], 422);
        }
        
        $success = $this->queueService->retryPost($post);
        
        if ($success) {
            return response()->json([
                'message' => 'Postagem colocada novamente na fila',
                'post' => $post
            ]);
        } else {
            return response()->json([
                'message' => 'Erro ao tentar novamente a postagem'
            ], 500);
        }
    }

    /**
     * API: Cancela uma postagem agendada.
     */
    public function apiCancel(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Acesso não autorizado'
            ], 403);
        }
        
        // Verificar se a postagem está pendente ou falhou
        if (!in_array($post->queue_status, ['pending', 'failed'])) {
            return response()->json([
                'message' => 'Apenas postagens pendentes ou com falha podem ser canceladas'
            ], 422);
        }
        
        // Atualizar status
        $post->queue_status = 'cancelled';
        $post->status = 'rascunho';
        $post->scheduled_at = null;
        $post->save();
        
        return response()->json([
            'message' => 'Agendamento cancelado com sucesso',
            'post' => $post
        ]);
    }

    /**
     * API: Processa manualmente uma postagem agendada.
     */
    public function apiProcessNow(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        // Verificar se a postagem pertence ao usuário atual
        if ($post->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Acesso não autorizado'
            ], 403);
        }
        
        // Verificar se a postagem está pendente
        if ($post->queue_status !== 'pending') {
            return response()->json([
                'message' => 'Apenas postagens pendentes podem ser processadas manualmente'
            ], 422);
        }
        
        $success = $this->queueService->processPost($post);
        
        if ($success) {
            return response()->json([
                'message' => 'Postagem publicada com sucesso',
                'post' => $post
            ]);
        } else {
            return response()->json([
                'message' => 'Erro ao publicar postagem',
                'post' => $post
            ], 500);
        }
    }
}
