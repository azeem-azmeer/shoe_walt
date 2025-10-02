<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Show Blade view with all admins
    public function index()
    {
        if (auth()->guest() || auth()->user()->role !== 'admin') {
            return redirect()->route('login');
        }

    }
}
    
