<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $plan
     */
    public function handle(Request $request, Closure $next, string $plan = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Se nenhum plano específico for exigido, apenas verifique se o usuário está autenticado
        if (!$plan) {
            return $next($request);
        }

        // Verifica se o usuário tem o plano necessário
        if ($plan === 'pro' && $user->subscription_plan !== 'pro') {
            return redirect()->route('subscription.index')
                ->with('error', 'Esta funcionalidade requer o plano Pro. Por favor, faça upgrade da sua assinatura.');
        }

        // Verifica se a assinatura expirou (para plano Pro)
        if ($user->subscription_plan === 'pro' && $user->subscription_ends_at && $user->subscription_ends_at < now()) {
            $user->update(['subscription_plan' => 'free']);
            return redirect()->route('subscription.index')
                ->with('error', 'Sua assinatura Pro expirou. Por favor, renove sua assinatura.');
        }

        return $next($request);
    }
}
