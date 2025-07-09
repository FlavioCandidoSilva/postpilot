<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessQueuedPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa postagens agendadas que estão prontas para publicação';

    /**
     * O serviço de fila.
     *
     * @var \App\Services\QueueService
     */
    protected $queueService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\QueueService $queueService
     * @return void
     */
    public function __construct(QueueService $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando processamento de postagens agendadas...');
        
        $posts = $this->queueService->getPostsReadyForProcessing();
        
        if ($posts->isEmpty()) {
            $this->info('Nenhuma postagem pronta para processamento.');
            return 0;
        }
        
        $this->info('Encontradas ' . $posts->count() . ' postagens para processar.');
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($posts as $post) {
            $this->info("Processando postagem #{$post->id}: {$post->title}");
            
            $result = $this->queueService->processPost($post);
            
            if ($result) {
                $this->info("✓ Postagem #{$post->id} publicada com sucesso!");
                $successCount++;
            } else {
                $this->error("✗ Falha ao publicar postagem #{$post->id}: {$post->failure_reason}");
                $failCount++;
            }
        }
        
        $this->info("Processamento concluído: {$successCount} sucesso(s), {$failCount} falha(s).");
        
        return 0;
    }
}
