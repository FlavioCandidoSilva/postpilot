<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class QueueService
{
    /**
     * Obter todas as postagens na fila.
     *
     * @param string|null $status Filtrar por status específico
     * @param int|null $user_id Filtrar por usuário específico
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQueuedPosts($status = null, $user_id = null)
    {
        $query = Post::query()
            ->whereNotNull('scheduled_at')
            ->orderBy('queue_priority', 'desc')
            ->orderBy('scheduled_at', 'asc');
        
        if ($status) {
            $query->where('queue_status', $status);
        }
        
        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        
        return $query->get();
    }
    
    /**
     * Obter postagens prontas para processamento.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPostsReadyForProcessing()
    {
        $now = Carbon::now();
        
        return Post::where('status', 'pronto')
            ->where('queue_status', 'pending')
            ->where('scheduled_at', '<=', $now)
            ->orderBy('queue_priority', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->get();
    }
    
    /**
     * Processar uma postagem da fila.
     *
     * @param Post $post
     * @return bool
     */
    public function processPost(Post $post)
    {
        try {
            // Atualizar status da fila
            $post->queue_status = 'processing';
            $post->last_attempt_at = Carbon::now();
            $post->retry_count += 1;
            $post->save();
            
            // Simular publicação no LinkedIn
            // Em um ambiente real, aqui seria feita a integração com a API do LinkedIn
            $success = $this->simulatePublishToLinkedIn($post);
            
            if ($success) {
                // Atualizar status após publicação bem-sucedida
                $post->queue_status = 'published';
                $post->status = 'publicado';
                $post->published_at = Carbon::now();
                $post->failure_reason = null;
                $post->save();
                
                Log::info("Post #{$post->id} publicado com sucesso.");
                return true;
            } else {
                // Registrar falha
                $this->handleFailure($post, 'Falha na publicação no LinkedIn');
                return false;
            }
        } catch (\Exception $e) {
            // Registrar exceção
            $this->handleFailure($post, 'Erro: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lidar com falha na publicação.
     *
     * @param Post $post
     * @param string $reason
     * @return void
     */
    private function handleFailure(Post $post, $reason)
    {
        $post->queue_status = 'failed';
        $post->failure_reason = $reason;
        $post->save();
        
        Log::error("Falha ao publicar post #{$post->id}: {$reason}");
    }
    
    /**
     * Tentar novamente uma postagem que falhou.
     *
     * @param Post $post
     * @return bool
     */
    public function retryPost(Post $post)
    {
        // Verificar se a postagem está em estado de falha
        if ($post->queue_status !== 'failed') {
            return false;
        }
        
        // Resetar status para pendente
        $post->queue_status = 'pending';
        $post->save();
        
        // Processar imediatamente se a data agendada já passou
        if ($post->scheduled_at <= Carbon::now()) {
            return $this->processPost($post);
        }
        
        return true;
    }
    
    /**
     * Reordenar postagens na fila.
     *
     * @param array $postIds IDs das postagens na nova ordem
     * @return bool
     */
    public function reorderQueue(array $postIds)
    {
        try {
            // Atribuir prioridades baseadas na ordem fornecida
            // Quanto maior o número, maior a prioridade
            $priority = count($postIds);
            
            foreach ($postIds as $postId) {
                $post = Post::find($postId);
                if ($post) {
                    $post->queue_priority = $priority;
                    $post->save();
                    $priority--;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao reordenar fila: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter estatísticas da fila.
     *
     * @param int|null $user_id
     * @return array
     */
    public function getQueueStats($user_id = null)
    {
        $query = Post::query();
        
        if ($user_id) {
            $query->where('user_id', $user_id);
        }
        
        $totalPending = (clone $query)->where('queue_status', 'pending')->count();
        $totalProcessing = (clone $query)->where('queue_status', 'processing')->count();
        $totalPublished = (clone $query)->where('queue_status', 'published')->count();
        $totalFailed = (clone $query)->where('queue_status', 'failed')->count();
        
        $nextScheduled = (clone $query)
            ->where('queue_status', 'pending')
            ->whereNotNull('scheduled_at')
            ->orderBy('scheduled_at', 'asc')
            ->first();
        
        $recentlyPublished = (clone $query)
            ->where('queue_status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();
        
        $recentlyFailed = (clone $query)
            ->where('queue_status', 'failed')
            ->orderBy('last_attempt_at', 'desc')
            ->limit(5)
            ->get();
        
        return [
            'counts' => [
                'pending' => $totalPending,
                'processing' => $totalProcessing,
                'published' => $totalPublished,
                'failed' => $totalFailed,
                'total' => $totalPending + $totalProcessing + $totalPublished + $totalFailed
            ],
            'next_scheduled' => $nextScheduled,
            'recently_published' => $recentlyPublished,
            'recently_failed' => $recentlyFailed
        ];
    }
    
    /**
     * Simular publicação no LinkedIn.
     * Em um ambiente real, isso seria substituído pela integração real com a API do LinkedIn.
     *
     * @param Post $post
     * @return bool
     */
    private function simulatePublishToLinkedIn(Post $post)
    {
        // Simular uma taxa de sucesso de 90%
        $success = (rand(1, 100) <= 90);
        
        // Simular um atraso de processamento
        sleep(1);
        
        if ($success) {
            // Simular dados de engajamento iniciais
            $post->engagement_data = json_encode([
                'views' => 0,
                'likes' => 0,
                'comments' => 0,
                'shares' => 0,
                'linkedin_post_id' => 'linkedin_' . uniqid(),
                'published_at' => Carbon::now()->toIso8601String()
            ]);
            $post->save();
        }
        
        return $success;
    }
}
