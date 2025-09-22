<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserTokenController extends Controller
{
    public function mint(Request $request)
    {
        $user = $request->user();

        // rotate this named token so we don't accumulate too many
        $user->tokens()->where('name', 'customer-page')->delete();

        $plain = $user->createToken('customer-page', [
            'cart:read','cart:write',
            'wishlist:read','wishlist:write',
            'reviews:read','reviews:write',
        ])->plainTextToken;

        return response()->json(['token' => $plain]);
    }
}
