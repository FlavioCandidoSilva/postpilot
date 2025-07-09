<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    /**
     * Exibe a página de planos de assinatura.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $plans = [
            [
                'id' => 'free',
                'name' => 'Free',
                'price' => 0,
                'description' => 'Plano básico para começar',
                'features' => [
                    'Até 5 postagens agendadas por mês',
                    'Acesso a 3 templates de postagem',
                    'Calendário básico',
                    'Relatórios básicos'
                ],
                'current' => Auth::user()->subscription_plan === 'free'
            ],
            [
                'id' => 'pro',
                'name' => 'Pro',
                'price' => 29.90,
                'description' => 'Para criadores de conteúdo profissionais',
                'features' => [
                    'Postagens ilimitadas',
                    'Acesso a todos os templates',
                    'Geração de conteúdo com IA ilimitada',
                    'Sugestões de horário ideal',
                    'Relatórios avançados de engajamento',
                    'Suporte prioritário'
                ],
                'current' => Auth::user()->subscription_plan === 'pro'
            ]
        ];

        return Inertia::render('Subscription/Plans', [
            'plans' => $plans,
            'user' => Auth::user()
        ]);
    }

    /**
     * Processa a assinatura de um plano.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:free,pro'
        ]);

        $user = Auth::user();
        $plan = $request->plan;

        // Se o plano for gratuito, apenas atualize o status
        if ($plan === 'free') {
            $user->update([
                'subscription_plan' => 'free',
                'subscription_ends_at' => null
            ]);

            return redirect()->route('dashboard')->with('success', 'Você está agora no plano Free!');
        }

        // Simulação de integração com Stripe para o plano Pro
        // Em um ambiente real, aqui seria feita a integração completa com o Stripe
        $user->update([
            'subscription_plan' => 'pro',
            'subscription_ends_at' => now()->addMonth(),
            'stripe_id' => 'sim_' . uniqid(),
            'pm_type' => 'card',
            'pm_last_four' => '4242'
        ]);

        return redirect()->route('dashboard')->with('success', 'Parabéns! Você assinou o plano Pro com sucesso!');
    }

    /**
     * Cancela a assinatura atual.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        $user = Auth::user();
        
        // Em um ambiente real, aqui seria feito o cancelamento no Stripe
        $user->update([
            'subscription_plan' => 'free',
            'subscription_ends_at' => null
        ]);

        return redirect()->route('subscription.index')->with('success', 'Sua assinatura foi cancelada com sucesso.');
    }
}
