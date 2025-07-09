<?php

namespace App\Services;

class OpenAIService
{
    /**
     * Simula a geração de conteúdo com OpenAI.
     *
     * @param string $topic O tópico principal do conteúdo
     * @param string $tone O tom do conteúdo (professional, casual, inspirational, educational)
     * @param string $length O comprimento do conteúdo (short, medium, long)
     * @param string|null $keywords Palavras-chave a serem incluídas no conteúdo
     * @param string|null $audience Público-alvo do conteúdo
     * @return string O conteúdo gerado
     */
    public function generateContent($topic, $tone, $length, $keywords = null, $audience = null)
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
    
    /**
     * Gera conteúdo a partir de um prompt XML estruturado.
     *
     * @param string $prompt O prompt XML processado
     * @return string O conteúdo gerado
     */
    public function generateContentFromPrompt($prompt)
    {
        // Simular diferentes respostas com base no tipo de prompt
        if (strpos($prompt, '<task>Quero que você sugira') !== false) {
            return $this->generateIdeasResponse($prompt);
        } elseif (strpos($prompt, '<task>Crie uma publicação') !== false) {
            return $this->generatePostResponse($prompt);
        } else {
            // Resposta genérica para outros tipos de prompts
            return $this->generateGenericResponse($prompt);
        }
    }
    
    /**
     * Gera ideias de temas para publicações.
     *
     * @param string $prompt O prompt XML processado
     * @return string O conteúdo gerado
     */
    private function generateIdeasResponse($prompt)
    {
        // Extrair área de foco do prompt
        preg_match('/focadas na área\s+de\s+([^\.]+)/', $prompt, $matches);
        $area = isset($matches[1]) ? trim($matches[1]) : 'gestão de produto';
        
        // Extrair conceitos de produto do prompt
        preg_match('/<conceito_produto>(.*?)<\/conceito_produto>/s', $prompt, $matches);
        $conceitoProduto = isset($matches[1]) ? trim($matches[1]) : 'gestão de produto';
        
        // Ideias geradas com base na área e conceitos
        $ideas = [
            "# 1. A Teoria dos Jogos e a Priorização de Features\n\nO que a estratégia do Dilema do Prisioneiro pode nos ensinar sobre como equilibrar demandas conflitantes de stakeholders e tomar decisões de priorização mais eficazes em produtos digitais.",
            
            "# 2. O Paradoxo de Abilene na Validação de Produtos\n\nComo este fenômeno psicológico (onde grupos tomam decisões contrárias aos desejos individuais) afeta pesquisas com usuários e pode levar a falsos positivos em validações de produto.",
            
            "# 3. Arqueologia e Discovery: Escavando Necessidades Não Articuladas\n\nO que as técnicas arqueológicas de reconstrução de civilizações a partir de fragmentos podem ensinar sobre descobrir necessidades profundas que usuários não conseguem expressar diretamente.",
            
            "# 4. A Economia da Atenção e o Minimalismo em Produtos\n\nComo a crescente escassez de atenção está redefinindo o valor da simplicidade e o que isso significa para decisões de design e escopo de MVPs em 2025.",
            
            "# 5. Biomimética e Evolução de Produtos\n\nO que os 3,8 bilhões de anos de R&D da natureza podem ensinar sobre adaptação, iteração e resiliência no ciclo de vida de produtos digitais."
        ];
        
        return implode("\n\n", $ideas);
    }
    
    /**
     * Gera uma publicação completa para LinkedIn.
     *
     * @param string $prompt O prompt XML processado
     * @return string O conteúdo gerado
     */
    private function generatePostResponse($prompt)
    {
        // Extrair tema escolhido do prompt
        preg_match('/Crie uma publicação para LinkedIn \[(.*?)\]/', $prompt, $matches);
        $tema = isset($matches[1]) ? trim($matches[1]) : 'gestão de produto';
        
        // Gerar publicação estruturada
        $post = "# O Paradoxo da Escolha: Quando Menos Opções Geram Mais Resultados\n\n";
        $post .= "Você já se sentiu paralisado diante de muitas opções? Não está sozinho. Enquanto produtos digitais continuam adicionando features, estudos mostram que 64% dos usuários abandonam apps por considerá-los \"complicados demais\".\n\n";
        $post .= "A psicologia por trás disso é fascinante: nosso cérebro consome glicose ao tomar decisões. Quanto mais escolhas, mais energia mental gastamos - até chegarmos à \"fadiga decisória\".\n\n";
        $post .= "Vi isso na prática recentemente: simplificamos nossa interface removendo 40% das opções raramente usadas. O resultado? Aumento de 27% na taxa de conversão e redução de 35% nas solicitações de suporte.\n\n";
        $post .= "A lição é clara: em produtos digitais, subtrair pode ser mais valioso que adicionar.\n\n";
        $post .= "Três aprendizados para aplicar hoje:\n\n";
        $post .= "1️⃣ Analise dados de uso para identificar recursos subutilizados\n";
        $post .= "2️⃣ Teste versões simplificadas com grupos de controle\n";
        $post .= "3️⃣ Priorize a clareza sobre a abundância de opções\n\n";
        $post .= "E você, já experimentou simplificar algum produto e obteve resultados surpreendentes? Compartilhe sua experiência! 👇";
        
        return $post;
    }
    
    /**
     * Gera uma resposta genérica para outros tipos de prompts.
     *
     * @param string $prompt O prompt XML processado
     * @return string O conteúdo gerado
     */
    private function generateGenericResponse($prompt)
    {
        // Extrair palavras-chave do prompt para personalizar a resposta
        $keywords = ['produto', 'gestão', 'desenvolvimento', 'estratégia', 'usuário', 'mercado'];
        $relevantKeywords = [];
        
        foreach ($keywords as $keyword) {
            if (strpos(strtolower($prompt), strtolower($keyword)) !== false) {
                $relevantKeywords[] = $keyword;
            }
        }
        
        // Se não encontrou palavras-chave relevantes, usar resposta padrão
        if (empty($relevantKeywords)) {
            return "Aqui está um conteúdo gerado com base no seu prompt. Para obter resultados mais específicos, considere usar um dos templates pré-definidos ou estruturar seu prompt com mais detalhes sobre o tipo de conteúdo desejado.";
        }
        
        // Gerar resposta baseada nas palavras-chave encontradas
        $response = "# Insights sobre " . implode(", ", $relevantKeywords) . "\n\n";
        $response .= "Baseado no seu prompt, aqui estão algumas reflexões importantes sobre " . implode(", ", $relevantKeywords) . ":\n\n";
        
        // Adicionar parágrafos baseados nas palavras-chave
        if (in_array('produto', $relevantKeywords)) {
            $response .= "A gestão de produto eficaz começa com uma compreensão profunda das necessidades do usuário. Antes de pensar em features, é essencial mapear a jornada completa e identificar pontos de dor reais que podem ser solucionados.\n\n";
        }
        
        if (in_array('gestão', $relevantKeywords)) {
            $response .= "A gestão moderna exige equilíbrio entre dados e intuição. Enquanto métricas fornecem direção objetiva, a experiência e sensibilidade para contexto são igualmente importantes para decisões estratégicas.\n\n";
        }
        
        if (in_array('desenvolvimento', $relevantKeywords)) {
            $response .= "O desenvolvimento ágil não significa ausência de planejamento, mas sim adaptabilidade. As equipes mais eficientes mantêm visão de longo prazo enquanto permanecem flexíveis para incorporar aprendizados ao longo do caminho.\n\n";
        }
        
        if (in_array('estratégia', $relevantKeywords)) {
            $response .= "Estratégias eficazes são simultaneamente ambiciosas e realistas. O segredo está em definir um norte claro e inspirador, mas decompô-lo em etapas alcançáveis que mantenham o momentum da equipe.\n\n";
        }
        
        if (in_array('usuário', $relevantKeywords)) {
            $response .= "Compreender usuários vai além de pesquisas e entrevistas. Requer empatia genuína e capacidade de distinguir entre o que as pessoas dizem querer e o que realmente precisam.\n\n";
        }
        
        if (in_array('mercado', $relevantKeywords)) {
            $response .= "Mercados evoluem em velocidades diferentes. A chave está em identificar não apenas tendências atuais, mas padrões subjacentes que sinalizam mudanças fundamentais no comportamento do consumidor.\n\n";
        }
        
        $response .= "Espero que estas reflexões sejam úteis para seu contexto específico. Para conteúdo mais direcionado, considere utilizar um dos templates estruturados disponíveis na plataforma.";
        
        return $response;
    }
    
    /**
     * Simula a geração de ideias para tópicos com base em um tema.
     *
     * @param string $theme O tema principal para gerar ideias
     * @param int $count Número de ideias a serem geradas
     * @return array Lista de ideias geradas
     */
    public function generateTopicIdeas($theme, $count = 5)
    {
        $ideas = [
            'marketing' => [
                'Estratégias de marketing digital para pequenas empresas',
                'Como criar uma campanha de email marketing eficaz',
                'O papel das redes sociais no marketing moderno',
                'Técnicas de SEO para aumentar o tráfego orgânico',
                'Marketing de conteúdo: criando valor para sua audiência',
                'Análise de métricas para otimizar campanhas de marketing',
                'Storytelling como ferramenta de marketing',
                'Personalização no marketing: conectando-se com seu público',
                'Marketing de influência: escolhendo os parceiros certos',
                'Tendências de marketing para o próximo ano'
            ],
            'tecnologia' => [
                'Como a inteligência artificial está transformando os negócios',
                'Blockchain além das criptomoedas: aplicações práticas',
                'O futuro do trabalho com automação e robótica',
                'Cibersegurança: protegendo sua empresa de ameaças digitais',
                'Cloud computing: vantagens para empresas de todos os tamanhos',
                'Internet das Coisas (IoT) no ambiente corporativo',
                'Realidade virtual e aumentada: aplicações comerciais',
                'Big Data: transformando dados em insights de negócios',
                'Tecnologias emergentes que todo profissional deve conhecer',
                '5G e suas implicações para a transformação digital'
            ],
            'liderança' => [
                'Desenvolvendo habilidades de liderança em tempos de crise',
                'Liderança inclusiva: criando equipes diversas e produtivas',
                'Comunicação eficaz: a base de uma liderança de sucesso',
                'Gestão de equipes remotas: desafios e melhores práticas',
                'Inteligência emocional para líderes',
                'Tomada de decisão: equilibrando dados e intuição',
                'Mentoria e coaching: desenvolvendo talentos na sua equipe',
                'Liderança situacional: adaptando seu estilo às circunstâncias',
                'Construindo uma cultura organizacional positiva',
                'Gestão de conflitos: transformando desafios em oportunidades'
            ],
            'produtividade' => [
                'Técnicas de gestão de tempo para profissionais ocupados',
                'Metodologias ágeis aplicadas ao trabalho individual',
                'Ferramentas digitais para aumentar sua produtividade',
                'Combatendo a procrastinação: estratégias práticas',
                'Mindfulness e foco no ambiente de trabalho',
                'Equilibrando vida pessoal e profissional',
                'Organização do espaço de trabalho para maior eficiência',
                'Reuniões produtivas: maximizando resultados e minimizando tempo',
                'Técnica Pomodoro e outras abordagens para manter o foco',
                'Automação de tarefas repetitivas para ganhar tempo'
            ],
            'carreira' => [
                'Construindo uma marca pessoal forte no LinkedIn',
                'Habilidades essenciais para o mercado de trabalho do futuro',
                'Networking estratégico: construindo relacionamentos profissionais',
                'Transição de carreira: planejando e executando com sucesso',
                'Negociação salarial: preparação e estratégias',
                'Aprendizado contínuo: mantendo-se relevante em sua área',
                'Feedback: como dar e receber para crescimento profissional',
                'Equilíbrio entre especialização e versatilidade na carreira',
                'Superando o síndrome do impostor no ambiente profissional',
                'Planejamento de carreira a longo prazo: definindo objetivos claros'
            ]
        ];
        
        // Se o tema não existir, usar um tema genérico
        if (!isset($ideas[$theme])) {
            $allIdeas = array_merge(...array_values($ideas));
            shuffle($allIdeas);
            return array_slice($allIdeas, 0, $count);
        }
        
        // Embaralhar as ideias do tema escolhido
        shuffle($ideas[$theme]);
        
        // Retornar o número solicitado de ideias
        return array_slice($ideas[$theme], 0, $count);
    }
    
    /**
     * Simula a análise de um texto para sugerir melhorias.
     *
     * @param string $text O texto a ser analisado
     * @return array Sugestões de melhorias
     */
    public function analyzeContent($text)
    {
        // Simulação de análise de conteúdo
        $wordCount = str_word_count($text);
        $sentenceCount = preg_match_all('/[.!?]+/', $text, $matches);
        
        $suggestions = [];
        
        // Sugestões baseadas no comprimento
        if ($wordCount < 100) {
            $suggestions[] = [
                'type' => 'length',
                'message' => 'O texto é relativamente curto. Considere expandir com mais detalhes ou exemplos.'
            ];
        } elseif ($wordCount > 500) {
            $suggestions[] = [
                'type' => 'length',
                'message' => 'O texto é bastante longo. Considere dividir em seções com subtítulos para melhor legibilidade.'
            ];
        }
        
        // Sugestões baseadas na estrutura de frases
        if ($sentenceCount > 0 && $wordCount / $sentenceCount > 25) {
            $suggestions[] = [
                'type' => 'readability',
                'message' => 'Algumas frases parecem muito longas. Considere dividir em frases mais curtas para melhorar a legibilidade.'
            ];
        }
        
        // Adicionar algumas sugestões genéricas
        $genericSuggestions = [
            [
                'type' => 'engagement',
                'message' => 'Considere adicionar uma pergunta para engajar seus leitores e estimular comentários.'
            ],
            [
                'type' => 'structure',
                'message' => 'Uma conclusão forte que reforce sua mensagem principal pode aumentar o impacto do seu conteúdo.'
            ],
            [
                'type' => 'visual',
                'message' => 'Considere adicionar elementos visuais como emojis ou quebras de linha para tornar o texto mais atraente visualmente.'
            ]
        ];
        
        // Adicionar 1-2 sugestões genéricas aleatórias
        shuffle($genericSuggestions);
        $suggestions = array_merge($suggestions, array_slice($genericSuggestions, 0, rand(1, 2)));
        
        return [
            'statistics' => [
                'word_count' => $wordCount,
                'sentence_count' => $sentenceCount,
                'estimated_reading_time' => ceil($wordCount / 200) . ' minutos'
            ],
            'suggestions' => $suggestions
        ];
    }
}
