<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AdminBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
class BackupController extends Controller
{
    public function index(AdminBackupService $backup)
    {
        return view('admin.backups.index',['backups'=>$backup->all(),'zipAvailable'=>class_exists(\ZipArchive::class)]);
    }
    public function create(AdminBackupService $backup)
    {
        try {$filename=$backup->create();}
        catch (\Throwable $e) { report($e); return back()->withErrors(['backup'=>'Backup failed. Check PHP ZIP support, database permissions and storage write access.']); }
        return back()->with('success',"Backup $filename created in private storage.");
    }
    public function download(string $filename, AdminBackupService $backup)
    {
        $path=$backup->path($filename);
        abort_unless(is_file($path),404);
        return response()->download($path,$filename,['Cache-Control'=>'no-store, private']);
    }
    public function destroy(string $filename, AdminBackupService $backup)
    {
        $path=$backup->path($filename);
        abort_unless(is_file($path),404);
        if (!unlink($path)) return back()->withErrors(['backup'=>'Unable to remove backup file.']);
        return back()->with('success','Backup deleted.');
    }
    public function maintenance(Request $request)
    {
        $request->validate(['enabled'=>'required|boolean']);
        app('site.settings')->update(['maintenance_mode'=>$request->boolean('enabled')]);
        return back()->with('success','Public homepage maintenance mode updated. Authenticated administrators can still access it.');
    }
    public function clearCache(Request $request)
    {
        $request->validate(['scope'=>'required|in:views,app']);
        $command=$request->input('scope')==='views'?'view:clear':'optimize:clear';
        try {Artisan::call($command);}
        catch (\Throwable $e) {report($e);return back()->withErrors(['cache'=>'Cache clear failed. Check storage permissions.']);}
        return back()->with('success','Cache cleared: '.$command);
    }
}
