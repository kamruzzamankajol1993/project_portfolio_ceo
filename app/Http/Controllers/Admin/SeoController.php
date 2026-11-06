<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SeoPage;
use App\Services\PublicImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class SeoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['search'=>'nullable|string|max:100','edit'=>'nullable|integer|min:1','tab'=>'nullable|in:global,pages,preview']);
        $query=SeoPage::query()->orderBy('path');
        if ($request->filled('search')) $query->where('path','like','%'.$request->input('search').'%');
        $pages=$query->paginate(10)->withQueryString();
        if ($request->ajax()) return view('admin.seo._table',compact('pages'));
        $editing=$request->filled('edit') ? SeoPage::findOrFail($request->integer('edit')) : new SeoPage();
        return view('admin.seo.index',['pages'=>$pages,'editing'=>$editing]);
    }
    public function globalUpdate(Request $request,PublicImageService $images)
    {
        $data=$request->validate([
            'seo_title'=>'nullable|string|max:255','seo_description'=>'nullable|string|max:500',
            'seo_keywords'=>'nullable|string|max:1000',
            'seo_robots'=>['required',Rule::in(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'])],
            'seo_canonical'=>'nullable|url|max:255','seo_og_type'=>'required|in:website,article,profile',
            'seo_twitter_card'=>'required|in:summary,summary_large_image',
            'google_analytics_id'=>'nullable|string|max:100','google_site_verification'=>'nullable|string|max:255',
            'seo_og_image'=>'nullable|image|mimes:jpeg,png,webp|max:3072',
        ]);
        $setting=app('site.settings');$old=$setting->seo_og_image;
        unset($data['seo_og_image']);
        if ($request->hasFile('seo_og_image')) $data['seo_og_image']=$images->upload($request->file('seo_og_image'),'settings',1200);
        $setting->update($data);
        if (isset($data['seo_og_image'])) $images->delete($old);
        return redirect()->route('admin.seo.index',['tab'=>'global'])->with('success','Global SEO updated.');
    }
    private function validated(Request $request,?SeoPage $page=null): array
    {
        $data=$request->validate([
            'path'=>['required','string','max:240','regex:~^/(?!admin(?:/|$))(?:[A-Za-z0-9_-][A-Za-z0-9_.-]*(?:/[A-Za-z0-9_-][A-Za-z0-9_.-]*)*)?$~',Rule::unique('seo_pages','path')->ignore($page?->id)],
            'title'=>'required|string|max:255','description'=>'nullable|string|max:500','keywords'=>'nullable|string|max:1000',
            'robots'=>['required',Rule::in(['index,follow','noindex,follow','index,nofollow','noindex,nofollow'])],
            'canonical'=>'nullable|url|max:255','og_title'=>'nullable|string|max:255','og_description'=>'nullable|string|max:500',
            'og_image'=>'nullable|image|mimes:jpeg,png,webp|max:3072',
            'enabled'=>'nullable|boolean',
        ],['path.regex'=>'Use a public relative URL such as / or /about (not /admin).']);
        $data['enabled']=$request->boolean('enabled');
        $data['path']='/'.ltrim($data['path'],'/');
        return $data;
    }
    public function store(Request $request,PublicImageService $images)
    {
        $data=$this->validated($request);unset($data['og_image']);
        if ($request->hasFile('og_image')) $data['og_image']=$images->upload($request->file('og_image'),'settings',1200);
        SeoPage::create($data);
        return redirect()->route('admin.seo.index',['tab'=>'pages'])->with('success','Page metadata saved.');
    }
    public function update(Request $request,SeoPage $page,PublicImageService $images)
    {
        $data=$this->validated($request,$page);unset($data['og_image']);$old=$page->og_image;
        if ($request->hasFile('og_image')) $data['og_image']=$images->upload($request->file('og_image'),'settings',1200);
        $page->update($data);
        if (isset($data['og_image'])) $images->delete($old);
        return redirect()->route('admin.seo.index',['tab'=>'pages'])->with('success','Page metadata updated.');
    }
    public function destroy(SeoPage $page,PublicImageService $images)
    {
        $old=$page->og_image;$page->delete();$images->delete($old);
        return back()->with('success','Page SEO override removed.');
    }
}
