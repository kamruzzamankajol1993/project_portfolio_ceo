<div class="table-responsive">
<table class="table admin-table module-table blog-admin-table"><thead><tr><th>Post</th><th>Slug</th><th>Author</th><th>Status</th><th>Published</th><th class="actions-header">Actions</th></tr></thead><tbody>
@forelse($posts as $post)
<tr>
  <td><div class="blog-post-cell">@if($post->featured_image_url)<img src="{{ $post->featured_image_url }}" alt="">@else<span class="blog-thumb-placeholder"><i class="bi bi-image"></i></span>@endif<div><strong>{{ $post->title }}</strong><small>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 72) }}</small></div></div></td>
  <td><code class="blog-slug">/blog/{{ $post->slug }}</code></td>
  <td>{{ $post->author?->name ?? '—' }}</td>
  <td><span class="soft-tag {{ $post->status==='published'?'tag-active':'tag-violet' }}">{{ ucfirst($post->status) }}</span></td>
  <td>{{ $post->published_at?->format('d M Y, h:i A') ?? '—' }}</td>
  <td class="actions-cell"><div class="row-actions">
    @if($post->isPublished())<a class="row-edit action-icon" href="{{ route('blog.show',$post->slug) }}" target="_blank" rel="noopener" title="View post" aria-label="View post"><i class="bi bi-box-arrow-up-right"></i></a>@endif
    <a class="row-edit action-icon" href="{{ route('admin.blog.edit',$post) }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square"></i></a>
    <form method="post" action="{{ route('admin.blog.destroy',$post) }}" data-confirm="Delete this blog post?">@csrf @method('DELETE')<button type="submit" class="row-delete action-icon" title="Delete" aria-label="Delete"><i class="bi bi-trash3"></i></button></form>
  </div></td>
</tr>
@empty<tr><td colspan="6" class="text-center py-4">No blog posts found. Create your first post to get started.</td></tr>@endforelse
</tbody></table></div>
@include('admin.shared._pagination',['paginator'=>$posts])
