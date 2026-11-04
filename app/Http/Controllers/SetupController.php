<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SetupController extends Controller
{
    public function show()
    {
        $setup = DB::table('admin_setup')->where('id', 1)->first();
        abort_if(! $setup || $setup->completed_at || User::where('is_admin', true)->exists(), 404);
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $setup = DB::table('admin_setup')->where('id', 1)->first();
        abort_if(! $setup || $setup->completed_at || User::where('is_admin', true)->exists(), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:254', 'lowercase', 'unique:users,email'],
            'password' => ['required', 'string', 'max:128', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'setup_token' => ['required', 'string', 'size:64', 'regex:/\A[a-f0-9]{64}\z/'],
        ]);
        $password = Hash::make($data['password']);
        $user = DB::transaction(function () use ($data, $password) {
            // Serialize all setup requests on an existing singleton row (InnoDB).
            $setup = DB::table('admin_setup')->where('id', 1)->lockForUpdate()->first();
            abort_if(! $setup || $setup->completed_at || User::where('is_admin', true)->exists(), 404);
            if (! $setup->token_hash || ! $setup->expires_at || now()->gte($setup->expires_at)
                || ! hash_equals($setup->token_hash, hash('sha256', $data['setup_token']))) {
                throw ValidationException::withMessages(['setup_token' => 'The setup token is invalid or has expired.']);
            }
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = $password;
            $user->is_admin = true;
            $user->save();
            DB::table('admin_setup')->where('id', 1)->update([
                'token_hash' => null, 'expires_at' => null, 'completed_at' => now(), 'updated_at' => now(),
            ]);
            return $user;
        }, 3);
        // The delivery file has no further purpose once its token is consumed.
        $deliveryFile = storage_path('app/private/first-admin-setup.txt');
        if (is_file($deliveryFile)) {
            @unlink($deliveryFile);
        }
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('admin');
    }
}
