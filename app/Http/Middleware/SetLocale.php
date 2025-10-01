<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil locale dari session, fallback ke config
        $locale = $request->session()->get('locale', config('app.locale'));
        
        // Validasi locale yang allowed
        $allowedLocales = ['id', 'en', 'mb'];
        if (!in_array($locale, $allowedLocales)) {
            $locale = config('app.locale');
        }
        
        // Set locale untuk aplikasi
        app()->setLocale($locale);
        
        return $next($request);
    }
}