<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query=Permission::orderBy('group')->orderBy('name');
        if ($request->filled('search')) {
            $term=$request->string('search')->toString();
            $query->where(fn($q)=>$q->where('group','like',"%{$term}%")->orWhere('name','like',"%{$term}%"));
        }
        $permissions=$query->paginate(12)->withQueryString();
        if ($request->ajax()) return view('admin.permissions._table',compact('permissions'));
        return view('admin.permissions.index',compact('permissions'));
    }
    public function create() { return view('admin.permissions.form',['permission'=>null]); }
    public function store(Request $request)
    {
        $data=$request->validate([
            'group'=>'required|string|max:100','names'=>'required|array|min:1|max:30',
            'names.*'=>['required','string','max:100','distinct',Rule::unique('permissions','name')->where('guard_name','web')],
        ]);
        foreach ($data['names'] as $name) {
            Permission::firstOrCreate(['name'=>trim($name),'guard_name'=>'web'],['group'=>trim($data['group'])]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('admin.permissions.index')->with('success','Permissions saved.');
    }
    public function edit(Permission $permission) { return view('admin.permissions.form',['permission'=>$permission]); }
    public function update(Request $request, Permission $permission)
    {
        $data=$request->validate([
            'group'=>'required|string|max:100',
            'name'=>['required','string','max:100',Rule::unique('permissions','name')->where('guard_name','web')->ignore($permission->id)],
        ]);
        if ($permission->name === 'access.admin' && $data['name'] !== 'access.admin') {
            abort(403, 'Core admin access permission cannot be renamed.');
        }
        $permission->update($data);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('admin.permissions.index')->with('success','Permission updated.');
    }
    public function destroy(Permission $permission)
    {
        if ($permission->name==='access.admin') abort(403,'Core admin access permission cannot be deleted.');
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return back()->with('success','Permission deleted.');
    }
}
