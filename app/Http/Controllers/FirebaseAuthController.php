<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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

        // Resolve credentials path from services.php, firebase.php, or .env
        $credsPath = config('services.firebase.credentials')
            ?? config('firebase.projects.app.credentials.file')
            ?? env('FIREBASE_CREDENTIALS');

        // Normalize Windows paths (forward slashes are fine)
        if (is_string($credsPath)) {
            $credsPath = str_replace('\\', '/', $credsPath);
        }

        if (!$credsPath || !file_exists($credsPath)) {
            Log::error('Firebase creds missing', ['path' => $credsPath]);
            return response()->json([
                'message' => 'Server misconfigured: firebase-admin.json not found',
            ], 500);
        }

        // Read project_id from service account to compare with token.iss
        $sa = json_decode(file_get_contents($credsPath), true) ?: [];
        $projectId = $sa['project_id'] ?? null;
        Log::info('Firebase service account', ['project_id' => $projectId, 'path' => $credsPath]);

        // Build Admin client
        $firebaseAuth = (new Factory())
            ->withServiceAccount($credsPath)
            ->createAuth();

        // Verify the ID token
        try {
            $verified = $firebaseAuth->verifyIdToken($data['idToken']);
        } catch (FailedToVerifyToken $e) {
            Log::error('Failed to verify Firebase ID token', ['reason' => $e->getMessage()]);
            return response()->json([
                'message' => 'Invalid Firebase token',
                'reason'  => $e->getMessage(),   // keep during debugging
            ], 401);
        } catch (Throwable $e) {
            Log::error('Token verification error', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Token verification error'], 500);
        }

        // Pull claims
        $claims = $verified->claims();
        $uid    = $claims->get('sub');
        $email  = $claims->get('email');
        $name   = $claims->get('name') ?? ($email ? strtok($email, '@') : ('User '.$uid));
        $iss    = (string) $claims->get('iss', '');

        // Ensure token belongs to the same project as the service account
        if ($projectId && !str_contains($iss, "securetoken.google.com/{$projectId}")) {
            Log::error('Issuer mismatch', ['iss' => $iss, 'expected' => $projectId]);
            return response()->json(['message' => 'Token for wrong project (iss mismatch)'], 401);
        }

        // Find or create local user
        $user = User::query()
            ->when($email, fn($q) => $q->where('email', $email))
            ->orWhere('firebase_uid', $uid)
            ->first();

        if (!$user) {
            $user = User::create([
                'name'         => $name,
                'email'        => $email,                       // may be null if Google didn’t return it
                'password'     => bcrypt(str()->random(40)),    // placeholder
                'firebase_uid' => $uid,
            ]);
        } elseif (!$user->firebase_uid) {
            $user->forceFill(['firebase_uid' => $uid])->save();
        }

        // Session login (Jetstream/Sanctum)
        if (($data['as'] ?? 'session') === 'session') {
            Auth::login($user, true);
            return response()->noContent(); // 204
        }

        // Personal access token (API use)
        $token = $user->createToken('firebase-login')->plainTextToken;
        return response()->json(['token' => $token]);
    }
}
