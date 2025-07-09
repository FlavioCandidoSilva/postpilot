<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AIContentController extends Controller
{
    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        // Middleware para verificar se o usuário tem plano Pro
        $this->middleware('subscription:pro')->except(['index', 'showUpgradePrompt']);
    }

    /**
     * Exibe a página principal de geração de conteúdo com IA.
     */
    public function index()
    {
        $user = Auth::user();
        $isPro = $user->subscription_plan === 'pro';
        
        return Inertia::render('AI/Index', [
            'isPro' => $isPro,
            'recentGenerations' => $this->getRecentGenerations(),
            'templates' => $this->getAvailableTemplates($isPro),
        ]);
    }
    
    /**
     * Exibe a página de geração de conteúdo.
     */
    public function create()
    {
        return Inertia::render('AI/Create', [
            'tones' => $this->getAvailableTones(),
            'lengths' => $this->getAvailableLengths(),
            'topics' => $this->getSuggestedTopics(),
        ]);
    }
    
    /**
     * Gera conteúdo com IA a partir dos parâmetros fornecidos.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'tone' => 'required|string|in:professional,casual,inspirational,educational',
            'length' => 'required|string|in:short,medium,long',
            'keywords' => 'nullable|string',
            'audience' => 'nullable|string',
        ]);
        
        // Usar o serviço OpenAI para gerar conteúdo
        $openAIService = new \App\Services\OpenAIService();
        $content = $openAIService->generateContent(
            $request->topic,
            $request->tone,
            $request->length,
            $request->keywords,
            $request->audience
        );
        
        // Salvar o histórico de geração (em um ambiente real)
        // $this->saveGenerationHistory($request->all(), $content);
        
        return response()->json([
            'title' => 'Conteúdo sobre ' . $request->topic,
            'content' => $content,
            'ai_generated' => true,
            'generation_time' => now()->format('Y-m-d H:i:s'),
            'parameters' => $request->all(),
        ]);
    }
    
    /**
     * Exibe a página de prompt para upgrade quando usuários do plano Free
     * tentam acessar funcionalidades exclusivas do plano Pro.
     */
    public function showUpgradePrompt()
    {
        return Inertia::render('AI/UpgradePrompt', [
            'currentPlan' => Auth::user()->subscription_plan,
        ]);
    }
    
    /**
     * Retorna os tons disponíveis para geração de conteúdo.
     */
    private function getAvailableTones()
    {
        return [
            [
                'id' => 'professional',
                'name' => 'Profissional',
                'description' => 'Tom formal e objetivo, ideal para conteúdo corporativo',
                'icon' => 'briefcase'
            ],
            [
                'id' => 'casual',
                'name' => 'Casual',
                'description' => 'Tom descontraído e conversacional, bom para engajamento',
                'icon' => 'chat'
            ],
            [
                'id' => 'inspirational',
                'name' => 'Inspirador',
                'description' => 'Tom motivacional e reflexivo, para conteúdo de impacto',
                'icon' => 'star'
            ],
            [
                'id' => 'educational',
                'name' => 'Educativo',
                'description' => 'Tom explicativo e informativo, para ensinar conceitos',
                'icon' => 'book'
            ]
        ];
    }
    
    /**
     * Retorna os comprimentos disponíveis para geração de conteúdo.
     */
    private function getAvailableLengths()
    {
        return [
            [
                'id' => 'short',
                'name' => 'Curto',
                'description' => 'Aproximadamente 100-150 palavras',
                'icon' => 'short-text'
            ],
            [
                'id' => 'medium',
                'name' => 'Médio',
                'description' => 'Aproximadamente 200-300 palavras',
                'icon' => 'subject'
            ],
            [
                'id' => 'long',
                'name' => 'Longo',
                'description' => 'Aproximadamente 400-600 palavras',
                'icon' => 'notes'
            ]
        ];
    }
    
    /**
     * Retorna tópicos sugeridos para inspiração.
     */
    private function getSuggestedTopics()
    {
        return [
            'Tendências de mercado em 2025',
            'Dicas para produtividade no trabalho remoto',
            'Como construir uma marca pessoal no LinkedIn',
            'Inteligência artificial no dia a dia',
            'Estratégias de networking para profissionais',
            'Habilidades essenciais para o futuro do trabalho',
            'Sustentabilidade nos negócios',
            'Liderança em tempos de transformação digital',
            'Equilíbrio entre vida pessoal e profissional',
            'Inovação e criatividade nas empresas'
        ];
    }
    
    /**
     * Retorna os templates disponíveis para o usuário.
     */
    private function getAvailableTemplates($isPro)
    {
        $templates = [
            [
                'id' => 'professional_insight',
                'name' => 'Insight Profissional',
                'description' => 'Compartilhe uma visão especializada sobre um tema da sua área',
                'icon' => 'lightbulb',
                'pro_only' => false
            ],
            [
                'id' => 'industry_news',
                'name' => 'Notícias do Setor',
                'description' => 'Comente sobre uma notícia recente relevante para sua indústria',
                'icon' => 'newspaper',
                'pro_only' => false
            ],
            [
                'id' => 'quick_tip',
                'name' => 'Dica Rápida',
                'description' => 'Ofereça uma dica prática e útil relacionada à sua expertise',
                'icon' => 'tips_and_updates',
                'pro_only' => false
            ],
            [
                'id' => 'case_study',
                'name' => 'Estudo de Caso',
                'description' => 'Apresente um caso de sucesso com aprendizados relevantes',
                'icon' => 'assignment',
                'pro_only' => true
            ],
            [
                'id' => 'thought_leadership',
                'name' => 'Liderança de Pensamento',
                'description' => 'Posicione-se como líder de pensamento em um tema específico',
                'icon' => 'psychology',
                'pro_only' => true
            ],
            [
                'id' => 'trend_analysis',
                'name' => 'Análise de Tendências',
                'description' => 'Analise tendências emergentes e seu impacto no mercado',
                'icon' => 'trending_up',
                'pro_only' => true
            ],
            [
                'id' => 'how_to_guide',
                'name' => 'Guia Passo a Passo',
                'description' => 'Crie um guia detalhado sobre como realizar uma tarefa específica',
                'icon' => 'menu_book',
                'pro_only' => true
            ]
        ];
        
        // Se não for usuário Pro, filtrar apenas templates disponíveis para plano Free
        if (!$isPro) {
            return array_filter($templates, function($template) {
                return !$template['pro_only'];
            });
        }
        
        return $templates;
    }
    
    /**
     * Retorna as gerações recentes do usuário.
     */
    private function getRecentGenerations()
    {
        // Em um ambiente real, buscaria do banco de dados
        // Aqui vamos simular alguns dados
        return [
            [
                'id' => 1,
                'title' => 'Inteligência Artificial no Trabalho',
                'snippet' => 'Hoje vamos abordar um tema crucial para profissionais: Inteligência Artificial no Trabalho...',
                'created_at' => '2025-04-22 14:30:00',
                'tone' => 'professional',
                'used' => true
            ],
            [
                'id' => 2,
                'title' => 'Dicas de Produtividade',
                'snippet' => 'E aí, pessoal! Vamos bater um papo sobre produtividade? Esse é um assunto que tem dado o que falar ultimamente...',
                'created_at' => '2025-04-23 09:15:00',
                'tone' => 'casual',
                'used' => false
            ]
        ];
    }
    
    /**
     * Simula a geração de conteúdo com OpenAI.
     */
    private function simulateOpenAIGeneration($topic, $tone, $length, $keywords = null, $audience = null)
    {
        $introductions = [
            'professional' => "Hoje vamos abordar um tema crucial para profissionais: {$topic}. Este assunto tem ganhado relevância no mercado atual e merece nossa atenção.",
            'casual' => "E aí, pessoal! Vamos bater um papo sobre {$topic}? Esse é um assunto que tem dado o que falar ultimamente.",
            'inspirational' => "Nunca subestime o poder de {$topic} para transformar sua carreira e vida. Hoje compartilho reflexões importantes sobre este tema.",
            'educational' => "Você já se perguntou como {$topic} funciona? Neste post, vou explicar os principais conceitos e aplicações práticas."
        ];
        
        $bodies = [
            'professional' => "Ao analisar {$topic} em profundidade, percebemos que existem três aspectos fundamentais a considerar. Primeiro, a implementação estratégica requer planejamento detalhado. Segundo, a execução deve ser metódica e baseada em métricas claras. Terceiro, a avaliação contínua permite ajustes necessários para otimização de resultados.",
            'casual' => "Quando falamos de {$topic}, a primeira coisa que vem à mente é como isso afeta nosso dia a dia, não é mesmo? Tenho experimentado algumas abordagens interessantes que têm funcionado super bem. A melhor parte é que você não precisa ser especialista para começar a aplicar essas ideias.",
            'inspirational' => "O caminho para dominar {$topic} não é linear. Haverá desafios, momentos de dúvida e obstáculos que parecerão intransponíveis. Mas é justamente nesses momentos que o crescimento acontece. Cada dificuldade superada é um degrau na escada do seu desenvolvimento pessoal e profissional.",
            'educational' => "Para compreender {$topic}, precisamos analisar seus componentes básicos. O conceito se baseia em princípios fundamentais que, uma vez entendidos, podem ser aplicados em diversos contextos. Vamos explorar cada um desses elementos e como eles se interconectam para formar um sistema coeso."
        ];
        
        $conclusions = [
            'professional' => "Em conclusão, implementar estratégias eficazes relacionadas a {$topic} pode significar um diferencial competitivo significativo para profissionais e empresas que buscam excelência em seus mercados.",
            'casual' => "E aí, o que você achou dessas dicas sobre {$topic}? Compartilhe nos comentários suas experiências e vamos trocar ideias!",
            'inspirational' => "Lembre-se: sua jornada com {$topic} é única. Confie no processo, mantenha-se resiliente e os resultados virão. O sucesso não é um destino, mas uma jornada contínua de aprendizado e superação.",
            'educational' => "Espero que este conteúdo sobre {$topic} tenha sido esclarecedor. Continuarei abordando temas relacionados nas próximas publicações. Se tiver dúvidas específicas, deixe nos comentários!"
        ];
        
        // Adicionar menção a palavras-chave se fornecidas
        $keywordSection = '';
        if ($keywords) {
            $keywordArray = explode(',', $keywords);
            $keywordSection = "\n\nAlguns conceitos importantes relacionados a {$topic} incluem: " . implode(', ', array_map('trim', $keywordArray)) . ". Estes termos são fundamentais para uma compreensão completa do assunto.";
        }
        
        // Adicionar menção ao público-alvo se fornecido
        $audienceSection = '';
        if ($audience) {
            $audienceSection = "\n\nEste conteúdo é especialmente relevante para {$audience}, que podem aplicar estes conceitos diretamente em seu contexto profissional.";
        }
        
        $lengthMultiplier = [
            'short' => 1,
            'medium' => 2,
            'long' => 3
        ];
        
        $content = $introductions[$tone] . "\n\n";
        
        // Repetir o corpo de acordo com o comprimento desejado
        for ($i = 0; $i < $lengthMultiplier[$length]; $i++) {
            $content .= $bodies[$tone] . "\n\n";
        }
        
        // Adicionar seções de palavras-chave e público-alvo
        $content .= $keywordSection;
        $content .= $audienceSection;
        
        // Adicionar conclusão
        $content .= "\n\n" . $conclusions[$tone];
        
        return $content;
    }
}
