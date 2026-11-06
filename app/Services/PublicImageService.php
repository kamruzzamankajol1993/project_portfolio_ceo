<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PublicImageService
{
    public function upload(UploadedFile $file, string $folder, int $width = 1200): string
    {
        $folder = trim($folder, '/');
        if (!in_array($folder, ['users','settings','blog','profiles'], true)) throw new \InvalidArgumentException('Invalid image folder');
        $directory = public_path('uploads/'.$folder);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Image folder is not writable');
        }
        $name = Str::uuid().'.webp';
        Image::read($file->getRealPath())->scaleDown(width:$width)->toWebp(80)->save($directory.'/'.$name);
        return 'uploads/'.$folder.'/'.$name;
    }

    public function delete(?string $relative): void
    {
        if (!$relative || !preg_match('#^uploads/(users|settings|blog|profiles)/[a-f0-9-]+\.webp$#', $relative)) return;
        $file = public_path($relative);
        if (is_file($file)) @unlink($file);
    }
}
