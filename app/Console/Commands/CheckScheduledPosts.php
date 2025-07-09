<?php

namespace App\Console\Commands;

use App\Jobs\ProcessScheduledPost;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-scheduled-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e processa postagens agendadas que estão prontas para publicação';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando postagens agendadas...');
        
        // Buscar postagens agendadas que estão prontas para publicação
        // (status 'pronto', data de agendamento no passado, não publicadas ainda)
        $posts = Post::where('status', 'pronto')
                    ->whereNotNull('scheduled_at')
                    ->whereNull('published_at')
                    ->where('scheduled_at', '<=', Carbon::now())
                    ->get();
        
        $count = $posts->count();
        $this->info("Encontradas {$count} postagens para processar.");
        
        // Processar cada postagem encontrada
        foreach ($posts as $post) {
            $this->info("Processando postagem #{$post->id}: {$post->title}");
            
            // Despachar job para processar a postagem
            ProcessScheduledPost::dispatch($post);
        }
        
        $this->info('Processamento de postagens agendadas concluído.');
    }
}
