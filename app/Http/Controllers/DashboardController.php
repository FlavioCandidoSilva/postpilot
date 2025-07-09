<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Construtor do controlador.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Exibe o dashboard principal com estatísticas e resumos.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Estatísticas básicas
        $stats = [
            'total_posts' => Post::where('user_id', $user->id)->count(),
            'published_posts' => Post::where('user_id', $user->id)
                                    ->where('status', 'publicado')
                                    ->count(),
            'scheduled_posts' => Post::where('user_id', $user->id)
                                    ->where('status', 'pronto')
                                    ->whereNotNull('scheduled_at')
                                    ->where('scheduled_at', '>', now())
                                    ->count(),
            'draft_posts' => Post::where('user_id', $user->id)
                                ->where('status', 'rascunho')
                                ->count(),
            'categories' => Category::where('user_id', $user->id)->count(),
            'tags' => Tag::where('user_id', $user->id)->count(),
        ];
        
        // Próximas postagens agendadas
        $upcomingPosts = Post::where('user_id', $user->id)
                            ->where('status', 'pronto')
                            ->whereNotNull('scheduled_at')
                            ->where('scheduled_at', '>', now())
                            ->orderBy('scheduled_at', 'asc')
                            ->limit(5)
                            ->get();
        
        // Postagens recentes
        $recentPosts = Post::where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
        
        // Dados de engajamento (simulados)
        $engagementData = $this->getEngagementData($user->id);
        
        // Informações do plano
        $planInfo = [
            'name' => ucfirst($user->subscription_plan),
            'is_pro' => $user->subscription_plan === 'pro',
            'expires_at' => $user->subscription_ends_at ? $user->subscription_ends_at->format('d/m/Y') : null,
            'posts_limit' => $user->subscription_plan === 'free' ? 5 : 'Ilimitado',
            'posts_used' => $user->subscription_plan === 'free' 
                            ? Post::where('user_id', $user->id)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count()
                            : null,
        ];
        
        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'upcomingPosts' => $upcomingPosts,
            'recentPosts' => $recentPosts,
            'engagementData' => $engagementData,
            'planInfo' => $planInfo,
        ]);
    }
    
    /**
     * Exibe a página de relatórios detalhados.
     */
    public function reports()
    {
        $user = Auth::user();
        
        // Verificar se o usuário tem plano Pro
        if ($user->subscription_plan !== 'pro') {
            return redirect()->route('subscription.index')
                ->with('error', 'Relatórios detalhados estão disponíveis apenas para usuários do plano Pro.');
        }
        
        // Dados para relatórios (simulados)
        $reportData = [
            'engagement_by_day' => $this->getEngagementByDay($user->id),
            'engagement_by_time' => $this->getEngagementByTime($user->id),
            'engagement_by_topic' => $this->getEngagementByTopic($user->id),
            'performance_trends' => $this->getPerformanceTrends($user->id),
        ];
        
        return Inertia::render('Dashboard/Reports', [
            'reportData' => $reportData,
        ]);
    }
    
    /**
     * Exibe a página de configurações do usuário.
     */
    public function settings()
    {
        $user = Auth::user();
        
        return Inertia::render('Dashboard/Settings', [
            'user' => $user,
            'preferences' => $this->getUserPreferences($user->id),
        ]);
    }
    
    /**
     * Atualiza as configurações do usuário.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'notification_email' => 'boolean',
            'default_platform' => 'required|string|in:linkedin',
            'default_tone' => 'required|string|in:professional,casual,inspirational,educational',
            'timezone' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        // Em um ambiente real, salvaria as preferências no banco de dados
        // Aqui apenas simulamos o sucesso da operação
        
        return redirect()->route('dashboard.settings')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
    
    /**
     * Obtém dados de engajamento simulados.
     */
    private function getEngagementData($userId)
    {
        // Simulação de dados de engajamento
        return [
            'impressions' => [
                'total' => rand(1000, 5000),
                'change' => rand(-20, 50),
            ],
            'likes' => [
                'total' => rand(100, 500),
                'change' => rand(-20, 50),
            ],
            'comments' => [
                'total' => rand(20, 100),
                'change' => rand(-20, 50),
            ],
            'shares' => [
                'total' => rand(10, 50),
                'change' => rand(-20, 50),
            ],
        ];
    }
    
    /**
     * Obtém dados de engajamento por dia da semana.
     */
    private function getEngagementByDay($userId)
    {
        $days = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
        $data = [];
        
        foreach ($days as $day) {
            $data[] = [
                'day' => $day,
                'impressions' => rand(100, 1000),
                'likes' => rand(10, 100),
                'comments' => rand(0, 20),
                'shares' => rand(0, 10),
            ];
        }
        
        return $data;
    }
    
    /**
     * Obtém dados de engajamento por horário do dia.
     */
    private function getEngagementByTime($userId)
    {
        $data = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $timeLabel = sprintf('%02d:00', $hour);
            
            $data[] = [
                'time' => $timeLabel,
                'engagement' => $hour >= 8 && $hour <= 20 
                    ? rand(20, 100) 
                    : rand(5, 30), // Menor engajamento durante a noite
            ];
        }
        
        return $data;
    }
    
    /**
     * Obtém dados de engajamento por tópico/categoria.
     */
    private function getEngagementByTopic($userId)
    {
        $topics = ['Marketing', 'Tecnologia', 'Liderança', 'Inovação', 'Carreira', 'Produtividade'];
        $data = [];
        
        foreach ($topics as $topic) {
            $data[] = [
                'topic' => $topic,
                'engagement_rate' => rand(1, 10) / 10, // Entre 0.1 e 1.0
                'posts_count' => rand(1, 15),
            ];
        }
        
        return $data;
    }
    
    /**
     * Obtém tendências de desempenho ao longo do tempo.
     */
    private function getPerformanceTrends($userId)
    {
        $data = [];
        $startDate = Carbon::now()->subDays(30);
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'impressions' => rand(50, 500),
                'engagement' => rand(5, 100),
            ];
        }
        
        return $data;
    }
    
    /**
     * Obtém as preferências do usuário.
     */
    private function getUserPreferences($userId)
    {
        // Simulação de preferências do usuário
        return [
            'notification_email' => true,
            'default_platform' => 'linkedin',
            'default_tone' => 'professional',
            'timezone' => 'America/Sao_Paulo',
        ];
    }
}
