<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PublicImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:draft,published',
        ]);

        $query = Post::with('author')->latest('id');
        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%"));
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));

        $posts = $query->paginate(10)->withQueryString();
        if ($request->ajax()) return view('admin.blog._table', compact('posts'));

        return view('admin.blog.index', [
            'posts' => $posts,
            'stats' => [
                'total' => Post::count(),
                'published' => Post::where('status', 'published')->count(),
                'drafts' => Post::where('status', 'draft')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.blog.form', ['post' => new Post()]);
    }

    public function store(Request $request, PublicImageService $images)
    {
        $data = $this->validated($request);
        unset($data['featured_image']);
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $images->upload($request->file('featured_image'), 'blog', 1400);
        }
        $data['author_id'] = $request->user()->id;
        Post::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created.');
    }

    public function edit(Post $blog)
    {
        return view('admin.blog.form', ['post' => $blog]);
    }

    public function update(Request $request, Post $blog, PublicImageService $images)
    {
        $data = $this->validated($request, $blog);
        unset($data['featured_image']);
        if ($request->hasFile('featured_image')) {
            $old = $blog->featured_image;
            $data['featured_image'] = $images->upload($request->file('featured_image'), 'blog', 1400);
        }
        $blog->update($data);
        if (isset($old)) $images->delete($old);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated.');
    }

    public function destroy(Post $blog, PublicImageService $images)
    {
        $old = $blog->featured_image;
        $blog->delete();
        $images->delete($old);

        return back()->with('success', 'Blog post deleted.');
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:180',
                'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string|max:1000000',
            'featured_image' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:2000',
            'meta_robots' => ['required', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'canonical_url' => 'nullable|url|max:255',
        ], [
            'slug.regex' => 'Slug must use English letters/numbers separated by hyphens, e.g. how-to-start-a-business.',
        ]);

        $base = Str::slug($data['slug'] ?: $data['title']);
        if ($base === '') $base = 'blog-' . now()->format('Ymd') . '-' . Str::lower(Str::random(5));
        $data['slug'] = $this->uniqueSlug($base, $post?->id);
        $data['content'] = $this->cleanHtml($data['content']);

        if ($data['status'] === 'published') {
            $data['published_at'] = !empty($data['published_at'])
                ? $data['published_at']
                : ($post?->published_at ?? now());
        } else {
            $data['published_at'] = null;
        }

        return $data;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::limit($base, 170, '');
        $candidate = $slug;
        $suffix = 2;
        while (Post::query()->where('slug', $candidate)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $candidate = Str::limit($slug, 165, '') . '-' . $suffix++;
        }
        return $candidate;
    }

    private function cleanHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|svg|math)[^>]*>.*?</\\1>#is', '', $html) ?? '';
        $html = preg_replace('#</?(script|style|iframe|object|embed|form|input|button|svg|math)[^>]*>#i', '', $html) ?? '';
        $html = strip_tags($html, '<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><ul><ol><li><blockquote><a><hr><table><thead><tbody><tr><th><td>');
        // Remove every attribute except a safe, non-script URL on links.
        $html = preg_replace_callback('/<([a-z][a-z0-9]*)([^>]*)>/i', function (array $match): string {
            $tag = strtolower($match[1]);
            if ($tag !== 'a') return '<' . $tag . '>';
            if (!preg_match('/\bhref\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $match[2], $href)) return '<a>';
            $url = $href[1] ?? ($href[2] ?? ($href[3] ?? ''));
            if (!preg_match('~^(?:https?://|mailto:|tel:|/|#)~i', trim($url))) return '<a>';
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" rel="nofollow noopener noreferrer">';
        }, $html) ?? $html;
        return $html;
    }
}
