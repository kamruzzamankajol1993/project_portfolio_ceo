<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PublicImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->whereDoesntHave('roles', fn($q)=>$q->where('name','Super Admin'));
        if ($request->filled('search')) {
            $term = trim($request->string('search')->toString());
            $query->where(fn($q)=>$q->where('name','like',"%{$term}%")->orWhere('email','like',"%{$term}%")->orWhere('phone','like',"%{$term}%"));
        }
        $users = $query->latest('id')->paginate(10)->withQueryString();
        if ($request->ajax()) return view('admin.users._table',compact('users'));
        return view('admin.users.index', compact('users'));
    }
    public function create() { return view('admin.users.form',['user'=>new User(),'roles'=> $this->availableRoles()]); }
    public function store(Request $request, PublicImageService $images)
    {
        $data=$this->validated($request);
        $roleIds=$data['roles']??[];
        unset($data['roles'],$data['image']);
        $image = $request->hasFile('image') ? $images->upload($request->file('image'),'users',600) : null;
        $user=User::create(array_merge($data,['image'=>$image]));
        $user->syncRoles($this->availableRoles()->whereIn('id',$roleIds));
        return redirect()->route('admin.users.index')->with('success','User created.');
    }
    public function edit(User $user)
    {
        $this->protect($user);
        return view('admin.users.form',['user'=>$user, 'roles'=>$this->availableRoles()]);
    }
    public function update(Request $request, User $user, PublicImageService $images)
    {
        $this->protect($user);
        $data=$this->validated($request,$user);
        $roles=$data['roles']??[];
        unset($data['roles'],$data['image']);
        if (empty($data['password'])) unset($data['password']);
        if ($request->hasFile('image')) {
            $data['image']=$images->upload($request->file('image'),'users',600);
            $old=$user->image;
        }
        $user->update($data);
        $user->syncRoles($this->availableRoles()->whereIn('id',$roles));
        if (isset($old)) $images->delete($old);
        return redirect()->route('admin.users.index')->with('success','User updated.');
    }
    public function destroy(User $user, PublicImageService $images)
    {
        $this->protect($user);
        abort_if(auth()->id()===$user->id,403,'You cannot delete yourself.');
        $old=$user->image;
        $user->delete();
        $images->delete($old);
        return back()->with('success','User deleted.');
    }
    private function protect(User $user): void { abort_if($user->isFullSuperAdmin(),404); }
    private function availableRoles() { return Role::where('guard_name','web')->where('name','!=','Super Admin')->orderBy('name')->get(); }
    private function validated(Request $request, ?User $user=null): array
    {
        return $request->validate([
            'name'=>'required|string|max:255','phone'=>'nullable|string|max:30',
            'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($user?->id)],
            'password'=>[$user?'nullable':'required','confirmed',Password::min(10)->mixedCase()->numbers()],
            'status'=>'required|in:active,inactive','address'=>'nullable|string|max:2000',
            'bio'=>'nullable|string|max:5000','image'=>'nullable|image|mimes:jpeg,png,webp|max:3072',
            'roles'=>'nullable|array','roles.*'=>['integer',Rule::exists('roles','id')->where(fn($q)=>$q->where('guard_name','web')->where('name','!=','Super Admin'))],
        ]);
    }
}
