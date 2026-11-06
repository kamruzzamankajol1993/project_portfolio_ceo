<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('permissions')->where('name','!=','Super Admin');
        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where('name', 'like', '%' . $term . '%');
        }
        $roles = $query->orderByDesc('id')->paginate(10)->withQueryString();
        if ($request->ajax()) return view('admin.roles._table',compact('roles'));
        return view('admin.roles.index',compact('roles'));
    }
    public function create() { return view('admin.roles.form',['role'=>new Role(),'groups'=>Permission::orderBy('group')->orderBy('name')->get()->groupBy('group')]); }
    public function store(Request $request)
    {
        $data=$this->validated($request);
        $role=Role::create(['name'=>$data['name'],'guard_name'=>'web']);
        $role->syncPermissions(Permission::whereIn('id',$data['permissions']??[])->get());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('admin.roles.index')->with('success','Role created.');
    }
    public function edit(Role $role)
    {
        $this->protect($role);
        $role->load('permissions');
        return view('admin.roles.form',['role'=>$role,'groups'=>Permission::orderBy('group')->orderBy('name')->get()->groupBy('group')]);
    }
    public function update(Request $request, Role $role)
    {
        $this->protect($role);
        $data=$this->validated($request,$role);
        $role->update(['name'=>$data['name']]);
        $role->syncPermissions(Permission::whereIn('id',$data['permissions']??[])->get());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('admin.roles.index')->with('success','Role updated.');
    }
    public function destroy(Role $role)
    {
        $this->protect($role);
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return back()->with('success','Role deleted.');
    }
    private function protect(Role $role): void { abort_if($role->name==='Super Admin',404); }
    private function validated(Request $request, ?Role $role=null): array
    {
        return $request->validate([
            'name'=>['required','string','max:100','not_in:Super Admin', Rule::unique('roles','name')->where('guard_name','web')->ignore($role?->id)],
            'permissions'=>'nullable|array','permissions.*'=>['integer',Rule::exists('permissions','id')->where('guard_name','web')],
        ]);
    }
}
