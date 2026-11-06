<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class AdminBackupService
{
    private function dir(): string
    {
        $dir=storage_path('app/private/admin-backups');
        if (!is_dir($dir) && !mkdir($dir,0700,true) && !is_dir($dir)) throw new RuntimeException('Private backup directory cannot be created.');
        return $dir;
    }
    public function validName(string $name): bool
    {
        return (bool) preg_match('/^backup-\\d{8}-\\d{6}-[a-f0-9]{8}\\.(zip|sql)$/D', $name);
    }
    public function path(string $name): string
    {
        abort_unless($this->validName($name),404);
        return $this->dir().DIRECTORY_SEPARATOR.$name;
    }
    public function all(): array
    {
        $files=[];
        foreach (new \DirectoryIterator($this->dir()) as $file) {
            if (!$file->isFile() || $file->isLink() || !$this->validName($file->getFilename())) continue;
            $files[]=['name'=>$file->getFilename(),'size'=>$file->getSize(),'created'=>$file->getMTime()];
        }
        usort($files,fn($a,$b)=>$b['created']<=>$a['created']);
        return $files;
    }
    public function create(): string
    {
        $name='backup-'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
        $sql=$this->dir().'/'.$name.'.working';
        $out=fopen($sql,'wb');
        if (!$out) throw new RuntimeException('Unable to write database backup.');
        try {
            $this->writeSql($out);
        } catch (\Throwable $e) {
            fclose($out); @unlink($sql); throw $e;
        }
        fclose($out);
        if (!class_exists(\ZipArchive::class)) {
            $destination=$this->dir().'/'.$name.'.sql';
            rename($sql,$destination);
            return basename($destination);
        }
        $dest=$this->dir().'/'.$name.'.zip';
        $zip=new \ZipArchive();
        if ($zip->open($dest,\ZipArchive::CREATE|\ZipArchive::EXCL)!==true) { @unlink($sql); throw new RuntimeException('Unable to create ZIP backup.'); }
        try {
            if (!$zip->addFile($sql,'database.sql')) throw new RuntimeException('Unable to add database to ZIP.');
            foreach (['users','settings','blog'] as $folder) {
                $source=public_path('uploads/'.$folder);
                if (!is_dir($source)) continue;
                foreach (new \DirectoryIterator($source) as $file) {
                    if (!$file->isFile() || $file->isLink() || !preg_match('/^[a-f0-9-]{36}\\.webp$/D',$file->getFilename())) continue;
                    $zip->addFile($file->getPathname(),'uploads/'.$folder.'/'.$file->getFilename());
                }
            }
            $zip->addFromString('README.txt',"Private site backup. database.sql contains the database. Copy uploads/ into public/uploads/ when restoring. Protect this archive: it contains personal data.\n");
            if (!$zip->close()) throw new RuntimeException('Unable to finalize backup ZIP.');
        } catch (\Throwable $e) {
            $zip->close(); @unlink($dest); throw $e;
        } finally { @unlink($sql); }
        return basename($dest);
    }
    private function writeSql($out): void
    {
        $connection=DB::connection();$driver=$connection->getDriverName();
        if (!in_array($driver,['mysql','sqlite'],true)) throw new RuntimeException('Backup supports MySQL and SQLite only.');
        $pdo=$connection->getPdo();
        fwrite($out,"-- Site database export; restore manually with the corresponding database engine.\n");
        fwrite($out,$driver==='mysql' ? "SET FOREIGN_KEY_CHECKS=0;\n" : "PRAGMA foreign_keys=OFF;\n");
        if ($driver==='mysql') {
            $tables=[];
            foreach ($pdo->query('SHOW FULL TABLES')->fetchAll(\PDO::FETCH_NUM) as $row) if (($row[1]??'')==='BASE TABLE') $tables[]=$row[0];
        } else {
            $tables=$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
        }
        foreach ($tables as $table) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/D',$table)) continue;
            $quoted=$driver==='mysql'?'`'.$table.'`':'"'.$table.'"';
            $create=$driver==='mysql'
                ? $pdo->query('SHOW CREATE TABLE '.$quoted)->fetch(\PDO::FETCH_NUM)[1]
                : $pdo->query('SELECT sql FROM sqlite_master WHERE type=\'table\' AND name='.$pdo->quote($table))->fetchColumn();
            if (!$create) continue;
            fwrite($out,"\nDROP TABLE IF EXISTS $quoted;\n".$create.";\n");
            $query=$pdo->query('SELECT * FROM '.$quoted);
            while ($row=$query->fetch(\PDO::FETCH_ASSOC)) {
                $cols=[];$values=[];
                foreach ($row as $key=>$value) {
                    $cols[]=$driver==='mysql'?'`'.$key.'`':'"'.$key.'"';
                    $values[]=$value===null?'NULL':$pdo->quote((string)$value);
                }
                fwrite($out,'INSERT INTO '.$quoted.' ('.implode(',',$cols).') VALUES ('.implode(',',$values).");\n");
            }
        }
        fwrite($out,$driver==='mysql' ? "SET FOREIGN_KEY_CHECKS=1;\n" : "PRAGMA foreign_keys=ON;\n");
    }
}
