<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::published()->with('author')->orderByDesc('published_at')->orderByDesc('id')->paginate(9)->withQueryString();
        return view('blog.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->isPublished(), 404);
        return view('blog.show', compact('post'));
    }
}
