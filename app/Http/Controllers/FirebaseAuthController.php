<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Throwable;

class FirebaseAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'idToken' => 'required|string',
            'as'      => 'nullable|in:session,token',
        ]);

        // 0) Make sure credentials file exists
        $credsPath = config('services.firebase.credentials'); 
        if (!$credsPath || !file_exists($credsPath)) {
            Log::error('Firebase creds missing', ['path' => $credsPath]);
            return response()->json(['message' => 'Server misconfigured: firebase-admin.json not found'], 500);
        }

        // Load project_id from service account file
        $projectId = json_decode(file_get_contents($credsPath), true)['project_id'] ?? null;

        // 1) Build Admin client from that file
        $firebaseAuth = (new Factory())
            ->withServiceAccount($credsPath)
            ->createAuth();

        // 2) Verify ID token & show precise error if it fails
        try {
            $verified = $firebaseAuth->verifyIdToken($data['idToken']);
        } catch (FailedToVerifyToken $e) {
            Log::error('Failed to verify Firebase ID token', ['err' => $e->getMessage()]);
            if (!App::isProduction()) {
                return response()->json([
                    'message' => 'Invalid Firebase token',
                    'reason'  => $e->getMessage(),
                ], 401);
            }
            return response()->json(['message' => 'Invalid Firebase token'], 401);
        } catch (Throwable $e) {
            Log::error('Token verification error', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Token verification error'], 500);
        }

        // 3) Pull claims
        $claims = $verified->claims();
        $uid    = $claims->get('sub');
        $email  = $claims->get('email');
        $name   = $claims->get('name') ?? ($email ? strtok($email, '@') : 'User '.$uid);

        // 4) Make sure the token belongs to your project
        $iss = $claims->get('iss'); 
        if ($projectId && is_string($iss) && !str_contains($iss, "securetoken.google.com/{$projectId}")) {
            Log::error('Token iss not for this project', ['iss' => $iss, 'expected' => $projectId]);
            return response()->json(['message' => 'Token for wrong project (iss mismatch)'], 401);
        }

        // 5) Find or create local user
        $user = User::query()
            ->when($email, fn($q) => $q->where('email', $email))
            ->orWhere('firebase_uid', $uid)
            ->first();

        if (!$user) {
            $user = User::create([
                'name'         => $name,
                'email'        => $email,
                'password'     => bcrypt(str()->random(40)),
                'firebase_uid' => $uid,
            ]);
        } elseif (!$user->firebase_uid) {
            $user->forceFill(['firebase_uid' => $uid])->save();
        }

        // 6) Session login (for Jetstream)
        if (($data['as'] ?? 'session') === 'session') {
            Auth::login($user, true);
            return response()->noContent(); // 204 → frontend redirects
        }

        // Or: issue a token (API use)
        $token = $user->createToken('firebase-login')->plainTextToken;
        return response()->json(['token' => $token]);
    }
}
