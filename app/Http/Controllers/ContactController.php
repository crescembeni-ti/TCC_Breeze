<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'cpf' => 'required|string|max:14',
            'address' => 'required|string|max:255',
            'description' => 'required|string',
            'email' => 'required|email|max:255',
        ]);

        Contact::create($validated);

        return redirect()->route('contact')->with('success', 'Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.');
    }
}
