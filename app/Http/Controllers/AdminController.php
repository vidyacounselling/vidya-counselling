<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('admin.dashboard', [
            'administrator' => $request->user(),
            'accountCount' => User::count(),
            'adminCount' => User::where('is_admin', true)->count(),
        ]);
    }
}
