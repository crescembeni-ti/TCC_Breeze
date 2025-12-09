<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
    // Lista todas as OS
    public function index()
    {
        $oss = ServiceOrder::with(['contact.user', 'contact.status'])
            ->latest()
            ->get();

        return view('admin.os.index', compact('oss'));
    }

    // Mostra uma OS específica
    public function show($id)
    {
        $os = ServiceOrder::with(['contact.user', 'contact.status'])
            ->findOrFail($id);

        return view('admin.os.show', compact('os'));
    }
}
