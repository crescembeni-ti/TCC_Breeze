<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Models\Tree;
use App\Models\ServiceActivity;

class DashboardController extends Controller
{
    public function index()
    {
        // Detecta automaticamente qual guard está logado
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('analyst')->check()) {
            $user = Auth::guard('analyst')->user();
        } elseif (Auth::guard('service')->check()) {
            $user = Auth::guard('service')->user();
        } else {
            $user = Auth::user(); // guard web
        }

        return view('dashboard', compact('user'));
    }
}