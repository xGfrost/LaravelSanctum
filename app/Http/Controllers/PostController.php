<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [new Middleware('auth:sanctum', except: ['index', 'show'])];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $query = Post::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->get('search');
            $query->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('body', 'like', '%' . $searchTerm . '%');
        }
        
        $posts = $query->with('user')->get();

        $posts->each(function ($post) {
            $post->photo_url = asset('storage/' . $post->photo);
            $post->username = $post->user->username;
        });
    
        return response()->json($posts);
    }

    

    public function store(Request $request)
    {
        $fields = $request->validate([
            'title'=>'required|max:255',
            'body'=>'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $fields['photo'] = $photoPath;
        }

       $post = $request->user()->posts()->create($fields);

        return $post;
    }

    public function show($id)
    {
        $post = Post::with('user')->find($id);
        $post->photo_url = asset('storage/' . $post->photo);
        $post->username = $post->user->username;

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json($post);
    }

    public function update(Request $request, Post $post)
    {
        Gate::authorize('modify', $post);
        $fields = $request->validate([
            'title'=>'required|max:255',
            'body'=>'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $fields['photo'] = $photoPath;
        }

       $post->update($fields);

        return $post;
    }

    public function destroy(Post $post)
    {
        Gate::authorize('modify', $post);
        $post->delete();

        return ['message' => 'The post was deleted'];
    }
}
