<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
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
        $categories = Category::where('user_id', Auth::id())
                    ->withCount('posts')
                    ->orderBy('name')
                    ->paginate(10);
        
        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,NULL,id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:255',
        ]);
        
        $category = new Category();
        $category->user_id = Auth::id();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();
        
        return redirect()->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        // Verificar se a categoria pertence ao usuário atual
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
        
        $category->load(['posts' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        return Inertia::render('Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Verificar se a categoria pertence ao usuário atual
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
        
        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Verificar se a categoria pertence ao usuário atual
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id . ',id,user_id,' . Auth::id(),
            'description' => 'nullable|string|max:255',
        ]);
        
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();
        
        return redirect()->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Verificar se a categoria pertence ao usuário atual
        if ($category->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Verificar se existem posts associados a esta categoria
        if ($category->posts()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Não é possível excluir uma categoria que possui postagens associadas.');
        }
        
        // Remover a categoria
        $category->delete();
        
        return redirect()->route('categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }
}
