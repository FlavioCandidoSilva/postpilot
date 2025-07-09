<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * O post a ser processado.
     *
     * @var \App\Models\Post
     */
    protected $post;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Verificar se o post ainda existe e está agendado
        if (!$this->post || !$this->post->scheduled_at) {
            return;
        }

        // Verificar se já passou da data de agendamento
        if ($this->post->scheduled_at->isPast()) {
            // Atualizar o status do post para 'publicado'
            $this->post->status = 'publicado';
            $this->post->published_at = now();
            $this->post->save();

            // Simular a publicação no LinkedIn
            $this->simulateLinkedInPosting();

            Log::info('Post agendado publicado com sucesso', [
                'post_id' => $this->post->id,
                'title' => $this->post->title,
                'platform' => $this->post->platform
            ]);
        }
    }

    /**
     * Simula a publicação no LinkedIn.
     * Em um ambiente real, aqui seria feita a integração com a API do LinkedIn.
     */
    private function simulateLinkedInPosting(): void
    {
        // Simular dados de engajamento iniciais
        $engagementData = [
            'impressions' => rand(50, 200),
            'likes' => rand(5, 30),
            'comments' => rand(0, 10),
            'shares' => rand(0, 5),
            'clicks' => rand(10, 50),
            'posted_at' => now()->toIso8601String()
        ];

        // Atualizar os dados de engajamento do post
        $this->post->engagement_data = $engagementData;
        $this->post->save();
    }
}
