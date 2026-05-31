<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Перевірка ролі користувача для доступу до маршрутів
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Якщо користувач не увійшов в систему
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Перевіряємо, чи є роль користувача серед дозволених для цього маршруту
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Якщо звичайний пасажир лізе в адмінку — повертаємо його в його кабінет
        if ($user->role === 'passenger') {
            return redirect('/dashboard');
        }

        // У всіх інших випадках скидаємо на головну
        return redirect('/');
    }
}