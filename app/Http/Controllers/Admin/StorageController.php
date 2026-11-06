<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AdminBackupService;
use App\Services\StorageInspector;
use Illuminate\Http\Request;
class StorageController extends Controller
{
    public function index(StorageInspector $inspector, AdminBackupService $backup)
    {
        $media=$inspector->media();
        $orphaned=array_values(array_filter($media,fn($item)=>$item['orphan']));
        $safeOrphans=array_values(array_filter($orphaned,fn($item)=>$item['modified']<=time()-86400));
        $usage=[
            'uploads'=>$inspector->folderSize(public_path('uploads')),
            'logs'=>$inspector->folderSize(storage_path('logs')),
            'backups'=>$inspector->folderSize(storage_path('app/private/admin-backups')),
        ];
        return view('admin.storage.index',[
            'mediaCount'=>count($media),'orphanCount'=>count($orphaned),
            'safeOrphans'=>$safeOrphans,'usage'=>$usage,
        ]);
    }
    public function cleanup(Request $request, StorageInspector $inspector)
    {
        $request->validate(['target'=>'required|in:orphans,old_logs']);
        if ($request->input('target')==='orphans') {
            $deleted=$inspector->removeOrphans();
            return back()->with('success',"Removed $deleted unreferenced image(s) older than 24 hours.");
        }
        $deleted=$inspector->clearOldLogFiles(30);
        return back()->with('success',"Removed $deleted old dated Laravel log file(s). Current log was preserved.");
    }
}
