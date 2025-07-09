<?php

namespace App\Http\Controllers;

use App\Models\PromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Services\OpenAIService;

class PromptTemplateController extends Controller
{
    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Exibe a lista de templates de prompt do usuário.
     */
    public function index()
    {
        $templates = PromptTemplate::where(function($query) {
            $query->where('user_id', Auth::id())
                  ->orWhere('is_default', true);
        })->orderBy('is_default', 'desc')
          ->orderBy('name')
          ->get();
        
        $categories = PromptTemplate::select('category')
            ->distinct()
            ->pluck('category');
        
        return Inertia::render('Prompts/Index', [
            'templates' => $templates,
            'categories' => $categories,
        ]);
    }

    /**
     * API: Retorna a lista de templates de prompt do usuário.
     */
    public function apiIndex()
    {
        $templates = PromptTemplate::where(function($query) {
            $query->where('user_id', Auth::id())
                  ->orWhere('is_default', true);
        })->orderBy('is_default', 'desc')
          ->orderBy('name')
          ->get();
        
        $categories = PromptTemplate::select('category')
            ->distinct()
            ->pluck('category');
        
        return response()->json([
            'templates' => $templates,
            'categories' => $categories,
        ]);
    }

    /**
     * Exibe o formulário para criar um novo template.
     */
    public function create()
    {
        $categories = PromptTemplate::select('category')
            ->distinct()
            ->pluck('category');
        
        return Inertia::render('Prompts/Create', [
            'categories' => $categories,
            'exampleTemplate' => $this->getExampleTemplate(),
        ]);
    }

    /**
     * Armazena um novo template de prompt.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Validar o XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request->content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        if ($xml === false) {
            return redirect()->back()
                ->withErrors(['content' => 'O conteúdo XML não é válido.'])
                ->withInput();
        }
        
        $template = new PromptTemplate();
        $template->user_id = Auth::id();
        $template->name = $request->name;
        $template->description = $request->description;
        $template->content = $request->content;
        $template->category = $request->category;
        $template->is_default = false;
        $template->save();
        
        return redirect()->route('prompts.index')
            ->with('success', 'Template criado com sucesso!');
    }

    /**
     * API: Armazena um novo template de prompt.
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Validar o XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request->content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        if ($xml === false) {
            return response()->json([
                'message' => 'O conteúdo XML não é válido.',
                'errors' => array_map(function($error) {
                    return [
                        'line' => $error->line,
                        'message' => $error->message
                    ];
                }, $errors)
            ], 422);
        }
        
        $template = new PromptTemplate();
        $template->user_id = Auth::id();
        $template->name = $request->name;
        $template->description = $request->description;
        $template->content = $request->content;
        $template->category = $request->category;
        $template->is_default = false;
        $template->save();
        
        return response()->json([
            'message' => 'Template criado com sucesso!',
            'template' => $template
        ]);
    }

    /**
     * Exibe um template específico.
     */
    public function show(PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual ou é um template padrão
        if (!$promptTemplate->is_default && $promptTemplate->user_id !== Auth::id()) {
            abort(403);
        }
        
        $variables = $promptTemplate->extractVariables();
        
        return Inertia::render('Prompts/Show', [
            'template' => $promptTemplate,
            'variables' => $variables,
        ]);
    }

    /**
     * API: Retorna um template específico.
     */
    public function apiShow(PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual ou é um template padrão
        if (!$promptTemplate->is_default && $promptTemplate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $variables = $promptTemplate->extractVariables();
        
        return response()->json([
            'template' => $promptTemplate,
            'variables' => $variables,
        ]);
    }

    /**
     * Exibe o formulário para editar um template.
     */
    public function edit(PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual
        if ($promptTemplate->is_default || $promptTemplate->user_id !== Auth::id()) {
            abort(403);
        }
        
        $categories = PromptTemplate::select('category')
            ->distinct()
            ->pluck('category');
        
        return Inertia::render('Prompts/Edit', [
            'template' => $promptTemplate,
            'categories' => $categories,
        ]);
    }

    /**
     * Atualiza um template específico.
     */
    public function update(Request $request, PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual
        if ($promptTemplate->is_default || $promptTemplate->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Validar o XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request->content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        if ($xml === false) {
            return redirect()->back()
                ->withErrors(['content' => 'O conteúdo XML não é válido.'])
                ->withInput();
        }
        
        $promptTemplate->name = $request->name;
        $promptTemplate->description = $request->description;
        $promptTemplate->content = $request->content;
        $promptTemplate->category = $request->category;
        $promptTemplate->save();
        
        return redirect()->route('prompts.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * API: Atualiza um template específico.
     */
    public function apiUpdate(Request $request, PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual
        if ($promptTemplate->is_default || $promptTemplate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'category' => 'required|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Validar o XML
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request->content);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        if ($xml === false) {
            return response()->json([
                'message' => 'O conteúdo XML não é válido.',
                'errors' => array_map(function($error) {
                    return [
                        'line' => $error->line,
                        'message' => $error->message
                    ];
                }, $errors)
            ], 422);
        }
        
        $promptTemplate->name = $request->name;
        $promptTemplate->description = $request->description;
        $promptTemplate->content = $request->content;
        $promptTemplate->category = $request->category;
        $promptTemplate->save();
        
        return response()->json([
            'message' => 'Template atualizado com sucesso!',
            'template' => $promptTemplate
        ]);
    }

    /**
     * Remove um template específico.
     */
    public function destroy(PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual e não é um template padrão
        if ($promptTemplate->is_default || $promptTemplate->user_id !== Auth::id()) {
            abort(403);
        }
        
        $promptTemplate->delete();
        
        return redirect()->route('prompts.index')
            ->with('success', 'Template excluído com sucesso!');
    }

    /**
     * API: Remove um template específico.
     */
    public function apiDestroy(PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual e não é um template padrão
        if ($promptTemplate->is_default || $promptTemplate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $promptTemplate->delete();
        
        return response()->json([
            'message' => 'Template excluído com sucesso!'
        ]);
    }

    /**
     * Gera conteúdo usando um template específico.
     */
    public function generate(Request $request, PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual ou é um template padrão
        if (!$promptTemplate->is_default && $promptTemplate->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validator = Validator::make($request->all(), [
            'variables' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Processar o template com as variáveis fornecidas
        $processedPrompt = $this->processTemplate($promptTemplate->content, $request->variables);
        
        // Usar o serviço OpenAI para gerar conteúdo
        $openAIService = new OpenAIService();
        $content = $openAIService->generateContentFromPrompt($processedPrompt);
        
        return Inertia::render('Prompts/Result', [
            'template' => $promptTemplate,
            'variables' => $request->variables,
            'processedPrompt' => $processedPrompt,
            'generatedContent' => $content,
        ]);
    }

    /**
     * API: Gera conteúdo usando um template específico.
     */
    public function apiGenerate(Request $request, PromptTemplate $promptTemplate)
    {
        // Verificar se o template pertence ao usuário atual ou é um template padrão
        if (!$promptTemplate->is_default && $promptTemplate->user_id !== Auth::id()) {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'variables' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Processar o template com as variáveis fornecidas
        $processedPrompt = $this->processTemplate($promptTemplate->content, $request->variables);
        
        // Usar o serviço OpenAI para gerar conteúdo
        $openAIService = new OpenAIService();
        $content = $openAIService->generateContentFromPrompt($processedPrompt);
        
        return response()->json([
            'template' => $promptTemplate,
            'variables' => $request->variables,
            'processedPrompt' => $processedPrompt,
            'generatedContent' => $content,
        ]);
    }

    /**
     * Processa um template XML substituindo as variáveis.
     */
    private function processTemplate($templateContent, $variables)
    {
        // Carregar o XML
        $xml = simplexml_load_string($templateContent);
        
        // Processar o XML substituindo as variáveis
        $this->replaceVariablesInXml($xml, $variables);
        
        // Converter o XML processado para string
        $processedXml = $xml->asXML();
        
        // Remover a declaração XML se existir
        $processedXml = preg_replace('/<\?xml.*?\?>/', '', $processedXml);
        
        return trim($processedXml);
    }
    
    /**
     * Substitui recursivamente as variáveis no XML.
     */
    private function replaceVariablesInXml(&$element, $variables)
    {
        foreach ($element->children() as $child) {
            $name = $child->getName();
            
            // Se o nome da tag corresponde a uma variável, substituir o conteúdo
            if (isset($variables[$name])) {
                $child[0] = $variables[$name];
            }
            
            // Recursivamente substituir variáveis em elementos filhos
            $this->replaceVariablesInXml($child, $variables);
        }
    }
    
    /**
     * Retorna um exemplo de template para referência.
     */
    private function getExampleTemplate()
    {
        return <<<XML
<task>
 Quero que você sugira 5 ideias originais de temas para publicações no LinkedIn, focadas na área 
de gestão de produto.
</task>
<restricoes>
 Não quero temas genéricos ou clichês como "dicas de PO" ou "práticas ágeis".
 O foco deve ser criar conexões inesperadas e provocativas entre:
 <elemento_externo>
 um acontecimento, fenômeno ou referência externa (histórica, cultural, política, científica, 
econômica, tecnológica, etc.)
 </elemento_externo>
 e
 <conceito_produto>
 conceitos, dilemas ou desafios reais da gestão de produto (ex.: discovery, priorização, validação, 
MVP, trade-offs, decisões de negócio, etc.)
 </conceito_produto>
</restricoes>
<exemplos>
 Os exemplos a seguir são apenas ilustrativos para demonstrar o tipo de associação desejada, 
mas NÃO devem ser usados:
 <exemplo>Um evento histórico (ex: corrida espacial)</exemplo>
 <exemplo>Uma notícia futura ou especulativa (ex: carros voadores em 2026)</exemplo>
 <exemplo>Um movimento social, cultural ou econômico atual (ex: ascensão do TikTok)</exemplo>
 <exemplo>Algo curioso, memorável ou inusitado que sirva como gancho reflexivo</exemplo>
</exemplos>
<objetivo>
Gerar ideias que funcionem como pontos de partida para reflexões profundas e relevantes sobre 
práticas e desafios reais da gestão de produto, com potencial para se destacar e engajar no feed 
do LinkedIn.
</objetivo>
<instrucoes>
 Produza ideias novas, criativas e com alto potencial de impacto.
 Evite temas já muito explorados ou superficiais.
 Pense em analogias e conexões pouco óbvias que provoquem interesse e curiosidade.  
</instrucoes>
XML;
    }
}
