<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthTokenController extends Controller
{
    public function mint(Request $request)
    {
        $user = $request->user();

        // Rotate the same named token to avoid piling up
        $user->tokens()->where('name', 'admin-page')->delete();

        // Give only the abilities your admin API needs
        $plain = $user->createToken('admin-page', [
            'products:read',
            'products:crud',
        ])->plainTextToken;

        return response()->json(['token' => $plain]);
    }
}
