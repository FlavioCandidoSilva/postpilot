#!/usr/bin/env php
<?php

namespace Tests;

use App\Models\Post;
use App\Models\PromptTemplate;
use App\Services\QueueService;
use App\Services\OpenAIService;
use Carbon\Carbon;

class SystemTest
{
    protected $queueService;
    protected $openAIService;
    
    public function __construct()
    {
        $this->queueService = new QueueService();
        $this->openAIService = new OpenAIService();
    }
    
    public function runAllTests()
    {
        echo "Iniciando testes do sistema PostPilot...\n\n";
        
        $this->testPromptTemplateSystem();
        $this->testQueueSystem();
        $this->testIntegration();
        
        echo "\nTodos os testes concluídos!\n";
    }
    
    protected function testPromptTemplateSystem()
    {
        echo "=== Testando Sistema de Templates de Prompts ===\n";
        
        // Testar criação de template
        echo "- Testando criação de template... ";
        try {
            $template = new PromptTemplate();
            $template->name = "Template de Teste";
            $template->description = "Template para testes automatizados";
            $template->category = "Teste";
            $template->content = "<prompt>\n  <context>Você é um especialista em {{topic}}</context>\n  <instruction>Escreva um post sobre {{topic}} com tom {{tone}}</instruction>\n  <format>Markdown</format>\n</prompt>";
            $template->is_default = false;
            $template->user_id = 1;
            $template->save();
            
            echo "OK\n";
            
            // Testar extração de variáveis
            echo "- Testando extração de variáveis... ";
            $variables = $this->openAIService->extractVariablesFromTemplate($template->content);
            
            if (in_array('topic', $variables) && in_array('tone', $variables)) {
                echo "OK\n";
            } else {
                echo "FALHA (Variáveis não extraídas corretamente)\n";
            }
            
            // Testar geração de conteúdo
            echo "- Testando geração de conteúdo... ";
            $variables = [
                'topic' => 'marketing digital',
                'tone' => 'profissional'
            ];
            
            $result = $this->openAIService->generateFromTemplate($template->content, $variables);
            
            if (!empty($result)) {
                echo "OK\n";
            } else {
                echo "FALHA (Conteúdo não gerado)\n";
            }
            
            // Limpar
            $template->delete();
            
        } catch (\Exception $e) {
            echo "FALHA (" . $e->getMessage() . ")\n";
        }
    }
    
    protected function testQueueSystem()
    {
        echo "\n=== Testando Sistema de Fila de Postagens ===\n";
        
        // Testar criação de postagem agendada
        echo "- Testando criação de postagem agendada... ";
        try {
            $post = new Post();
            $post->title = "Postagem de Teste";
            $post->content = "Conteúdo de teste para a fila de postagens";
            $post->status = "pronto";
            $post->user_id = 1;
            $post->scheduled_at = Carbon::now()->addMinutes(5);
            $post->queue_status = "pending";
            $post->save();
            
            echo "OK\n";
            
            // Testar obtenção de postagens na fila
            echo "- Testando obtenção de postagens na fila... ";
            $queuedPosts = $this->queueService->getQueuedPosts();
            
            if ($queuedPosts->contains('id', $post->id)) {
                echo "OK\n";
            } else {
                echo "FALHA (Postagem não encontrada na fila)\n";
            }
            
            // Testar reordenação da fila
            echo "- Testando reordenação da fila... ";
            $result = $this->queueService->reorderQueue([$post->id]);
            
            if ($result) {
                echo "OK\n";
            } else {
                echo "FALHA (Não foi possível reordenar a fila)\n";
            }
            
            // Testar processamento de postagem
            echo "- Testando processamento de postagem... ";
            $result = $this->queueService->processPost($post);
            
            // Recarregar postagem
            $post = Post::find($post->id);
            
            if ($post->queue_status === 'published' || $post->queue_status === 'failed') {
                echo "OK\n";
            } else {
                echo "FALHA (Status da fila não atualizado corretamente)\n";
            }
            
            // Testar estatísticas da fila
            echo "- Testando estatísticas da fila... ";
            $stats = $this->queueService->getQueueStats();
            
            if (isset($stats['counts']) && is_array($stats['counts'])) {
                echo "OK\n";
            } else {
                echo "FALHA (Estatísticas não geradas corretamente)\n";
            }
            
            // Limpar
            $post->delete();
            
        } catch (\Exception $e) {
            echo "FALHA (" . $e->getMessage() . ")\n";
        }
    }
    
    protected function testIntegration()
    {
        echo "\n=== Testando Integração entre Sistemas ===\n";
        
        // Testar fluxo completo: criar template, gerar conteúdo, agendar postagem
        echo "- Testando fluxo completo de template para postagem... ";
        try {
            // 1. Criar template
            $template = new PromptTemplate();
            $template->name = "Template de Integração";
            $template->description = "Template para teste de integração";
            $template->category = "Integração";
            $template->content = "<prompt>\n  <context>Você é um especialista em {{topic}}</context>\n  <instruction>Escreva um post curto sobre {{topic}}</instruction>\n  <format>Markdown</format>\n</prompt>";
            $template->is_default = false;
            $template->user_id = 1;
            $template->save();
            
            // 2. Gerar conteúdo
            $variables = [
                'topic' => 'automação de marketing'
            ];
            
            $generatedContent = $this->openAIService->generateFromTemplate($template->content, $variables);
            
            // 3. Criar postagem com o conteúdo gerado
            $post = new Post();
            $post->title = "Post sobre " . $variables['topic'];
            $post->content = $generatedContent;
            $post->status = "pronto";
            $post->user_id = 1;
            $post->scheduled_at = Carbon::now()->addMinutes(10);
            $post->queue_status = "pending";
            $post->save();
            
            // 4. Verificar se está na fila
            $queuedPosts = $this->queueService->getQueuedPosts();
            $isInQueue = $queuedPosts->contains('id', $post->id);
            
            // 5. Processar a postagem
            $this->queueService->processPost($post);
            
            // 6. Verificar resultado
            $post = Post::find($post->id);
            
            if ($isInQueue && ($post->queue_status === 'published' || $post->queue_status === 'failed')) {
                echo "OK\n";
            } else {
                echo "FALHA (Fluxo de integração não completado corretamente)\n";
            }
            
            // Limpar
            $template->delete();
            $post->delete();
            
        } catch (\Exception $e) {
            echo "FALHA (" . $e->getMessage() . ")\n";
        }
    }
}

// Executar testes
$tester = new SystemTest();
$tester->runAllTests();
