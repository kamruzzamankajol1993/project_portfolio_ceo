<?php
namespace App\Services;
use App\Models\User;
use App\Models\Post;
class StorageInspector
{
    private const FOLDERS = ['users','settings','blog'];
    public function media(): array
    {
        $known = array_fill_keys(User::query()->whereNotNull('image')->pluck('image')->all(), true);
        $setting = app('site.settings');
        foreach (['logo','favicon','seo_og_image'] as $key) if ($setting->{$key}) $known[$setting->{$key}] = true;
        foreach (Post::query()->whereNotNull('featured_image')->pluck('featured_image') as $image) $known[$image] = true;
        foreach (\App\Models\SeoPage::query()->whereNotNull('og_image')->pluck('og_image') as $image) $known[$image] = true;
        $items=[];
        foreach (self::FOLDERS as $folder) {
            $root=public_path('uploads/'.$folder);
            if (!is_dir($root)) continue;
            foreach (new \DirectoryIterator($root) as $file) {
                if (!$file->isFile() || $file->isLink() || !preg_match('/^[a-f0-9-]{36}\\.webp$/D', $file->getFilename())) continue;
                $relative='uploads/'.$folder.'/'.$file->getFilename();
                $items[]=['path'=>$relative,'size'=>$file->getSize(),'modified'=>$file->getMTime(),'orphan'=>!isset($known[$relative])];
            }
        }
        return $items;
    }
    public function folderSize(string $folder): array
    {
        if (!is_dir($folder)) return ['bytes'=>0,'count'=>0];
        $bytes=0;$count=0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && !$file->isLink()) { $bytes+=$file->getSize(); $count++; }
        }
        return ['bytes'=>$bytes,'count'=>$count];
    }
    public function removeOrphans(int $minAgeDays=1): int
    {
        $count=0;
        foreach ($this->media() as $file) {
            if (!$file['orphan'] || $file['modified'] > time()-$minAgeDays*86400) continue;
            $path=public_path($file['path']);
            if (is_file($path) && !is_link($path) && unlink($path)) $count++;
        }
        return $count;
    }
    public function clearOldLogFiles(int $days=30): int
    {
        $root=storage_path('logs');$count=0;
        if (!is_dir($root)) return 0;
        foreach (new \DirectoryIterator($root) as $file) {
            if (!$file->isFile() || $file->isLink() || !preg_match('/^laravel-\\d{4}-\\d{2}-\\d{2}\\.log$/D',$file->getFilename())) continue;
            if ($file->getMTime() < time()-$days*86400 && unlink($file->getPathname())) $count++;
        }
        return $count;
    }
}
