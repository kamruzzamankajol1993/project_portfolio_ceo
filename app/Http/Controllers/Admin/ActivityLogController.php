<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $input=$request->validate(['search'=>'nullable|string|max:100','module'=>'nullable|string|max:70','days'=>'nullable|integer|in:1,7,30,90']);
        $query=AdminActivityLog::query();
        if (!$request->user()->isFullSuperAdmin()) {
            // Preserve hidden Super Admin visibility for non-super administrators.
            $superIds=User::whereHas('roles', fn($q)=>$q->where('name','Super Admin'))->pluck('id');
            $query->where(function($q) use ($superIds) {
                $q->whereNull('actor_id')->orWhereNotIn('actor_id',$superIds);
            })->where(function($q) {
                $q->whereNull('actor_name')->orWhere('actor_name','!=','Super Admin');
            });
        }
        $query->latest('id');
        if (!empty($input['module'])) $query->where('module',$input['module']);
        if (!empty($input['days'])) $query->where('created_at','>=',now()->subDays((int)$input['days']));
        if (!empty($input['search'])) {
            $term=$input['search'];
            $query->where(function ($q) use ($term) {
                $q->where('actor_name','like','%'.$term.'%')->orWhere('action','like','%'.$term.'%')
                    ->orWhere('path','like','%'.$term.'%')->orWhere('ip_address','like','%'.$term.'%');
            });
        }
        $logs=$query->paginate(15)->withQueryString();
        if ($request->ajax()) return view('admin.activity._table',compact('logs'));
        $modules=(clone $query)->reorder()->select('module')->distinct()->orderBy('module')->pluck('module');
        $total=AdminActivityLog::count();
        $today=AdminActivityLog::where('created_at','>=',today())->count();
        return view('admin.activity.index',compact('logs','modules','total','today'));
    }
    public function prune(Request $request)
    {
        $data=$request->validate(['days'=>'required|integer|in:30,60,90,180']);
        $query=AdminActivityLog::where('created_at','<',now()->subDays((int)$data['days']));
        if (!$request->user()->isFullSuperAdmin()) {
            $superIds=User::whereHas('roles',fn($q)=>$q->where('name','Super Admin'))->pluck('id');
            $query->where(function($q) use ($superIds) {
                $q->whereNull('actor_id')->orWhereNotIn('actor_id',$superIds);
            })->where(function($q) {
                $q->whereNull('actor_name')->orWhere('actor_name','!=','Super Admin');
            });
        }
        $deleted=$query->delete();
        return back()->with('success',"Deleted $deleted audit entries older than {$data['days']} days.");
    }
}
