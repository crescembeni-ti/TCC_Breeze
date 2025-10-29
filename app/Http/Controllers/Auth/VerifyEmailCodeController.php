// app/Http/Controllers/Auth/VerifyEmailCodeController.php
<?php


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;

class VerifyEmailCodeController extends Controller
{
    /**
     * Valida o código de verificação de e-mail.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:6'],
        ]);

        $user = $request->user();

        // 1. O código está incorreto?
        if ($request->code !== $user->email_verification_code) {
            return back()->withErrors(['code' => 'O código de verificação está incorreto.']);
        }

        // 2. O código expirou?
        if (now()->gt($user->email_verification_code_expires_at)) {
            // (Opcional) Reenvia automaticamente um novo código
            // $user->sendEmailVerificationNotification();
            return back()->withErrors(['code' => 'O código de verificação expirou. Por favor, solicite um novo.']);
        }

        // 3. O usuário já está verificado?
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // 4. Marcar como verificado
        if ($user->markEmailAsVerified()) {
            // event(new Verified($user)); // markEmailAsVerified() já dispara isso

            // Limpa o código do banco de dados
            $user->email_verification_code = null;
            $user->email_verification_code_expires_at = null;
            $user->save();
        }

        // 5. Redirecionar para o dashboard
        return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
    }
}