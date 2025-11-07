<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class LandingPageController extends Controller
{
    public function index()
    {
        return Inertia::render('Frontend/LandingPage', [
            'locale' => app()->getLocale(),
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    }
}