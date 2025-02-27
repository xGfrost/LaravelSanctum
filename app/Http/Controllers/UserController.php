<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public static function middleware()
    {
        return [new Middleware('auth:sanctum', except: ['index', 'show'])];
    }

    public function me(Request $request)
    {
    $currentUser = $request->user();

    $posts = $currentUser->posts;

    return response()->json([
        'user' => $currentUser,
        'posts' => $posts,
    ]);
    }
}
