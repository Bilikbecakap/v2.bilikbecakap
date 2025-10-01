<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        $allowedLocales = ['id', 'en', 'mb'];
        
        if (in_array($locale, $allowedLocales)) {
            $request->session()->put('locale', $locale);
            $request->session()->save();
            app()->setLocale($locale);
        }
        
        // Redirect back tanpa full reload
        return back();
    }
}