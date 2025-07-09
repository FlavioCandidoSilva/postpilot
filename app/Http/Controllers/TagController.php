<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TagController extends Controller
{
    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::where('user_id', Auth::id())
                    ->withCount('posts')
                    ->orderBy('name')
                    ->paginate(10);
        
        return Inertia::render('Tags/Index', [
            'tags' => $tags,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Tags/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,NULL,id,user_id,' . Auth::id(),
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:255',
        ]);
        
        $tag = new Tag();
        $tag->user_id = Auth::id();
        $tag->name = $request->name;
        $tag->color = $request->color ?? '#6B7280'; // Cor padrão cinza
        $tag->description = $request->description;
        $tag->save();
        
        return redirect()->route('tags.index')
            ->with('success', 'Tag criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        // Verificar se a tag pertence ao usuário atual
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }
        
        $tag->load(['posts' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        return Inertia::render('Tags/Show', [
            'tag' => $tag,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        // Verificar se a tag pertence ao usuário atual
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }
        
        return Inertia::render('Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        // Verificar se a tag pertence ao usuário atual
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id . ',id,user_id,' . Auth::id(),
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:255',
        ]);
        
        $tag->name = $request->name;
        $tag->color = $request->color ?? '#6B7280';
        $tag->description = $request->description;
        $tag->save();
        
        return redirect()->route('tags.index')
            ->with('success', 'Tag atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        // Verificar se a tag pertence ao usuário atual
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Remover a tag (as relações serão tratadas pelo modelo)
        $tag->delete();
        
        return redirect()->route('tags.index')
            ->with('success', 'Tag excluída com sucesso!');
    }
}
