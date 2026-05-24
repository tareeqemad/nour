<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * توليد نسخ احتياطية لقاعدة البيانات (mysqldump) وإدارتها على القرص.
 * يعتمد على وجود الأمر mysqldump في PATH (أو في الـ paths الشائعة على Windows).
 */
class BackupService
{
    private string $directory;

    public function __construct()
    {
        $this->directory = storage_path('app/backups');
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function directory(): string
    {
        return $this->directory;
    }

    /**
     * توليد نسخة احتياطية جديدة وحفظها في storage/app/backups.
     * يُعيد المسار الكامل للملف.
     */
    public function create(): string
    {
        $binary = $this->findMysqldump();
        if (! $binary) {
            throw new RuntimeException('لم يتم العثور على الأمر mysqldump. تأكد من تثبيته في السيرفر وأن مساره موجود في PATH.');
        }

        $config   = config('database.connections.' . config('database.default'));
        $database = $config['database'] ?? null;
        $host     = $config['host']     ?? '127.0.0.1';
        $port     = (string) ($config['port'] ?? '3306');
        $user     = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        if (empty($database)) {
            throw new RuntimeException('لم يتم تحديد قاعدة بيانات في الإعدادات.');
        }

        $filename = sprintf('backup_%s.sql', Carbon::now()->format('Y-m-d_His'));
        $path     = $this->directory . DIRECTORY_SEPARATOR . $filename;

        $cmd = [
            $binary,
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
            $database,
        ];

        $process = new Process($cmd);
        $process->setTimeout(900); // 15 دقيقة كحد أقصى
        // تمرير كلمة المرور عبر متغير بيئي بدلاً من command-line (لا تظهر في ps)
        $env = $process->getEnv();
        if ($password !== '' && $password !== null) {
            $env['MYSQL_PWD'] = $password;
        }
        $process->setEnv($env);

        $fp = fopen($path, 'wb');
        if (! $fp) {
            throw new RuntimeException('تعذّر إنشاء ملف النسخة الاحتياطية على القرص.');
        }

        try {
            $process->run(function (string $type, string $buffer) use ($fp) {
                if ($type === Process::OUT) {
                    fwrite($fp, $buffer);
                }
            });
        } finally {
            fclose($fp);
        }

        if (! $process->isSuccessful()) {
            @unlink($path); // لا نُبقي ملف ناقص
            throw new RuntimeException('فشل توليد النسخة الاحتياطية: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        if (! is_file($path) || filesize($path) === 0) {
            @unlink($path);
            throw new RuntimeException('تم إنشاء ملف نسخة احتياطية فارغ — تحقق من إعدادات قاعدة البيانات.');
        }

        return $path;
    }

    /**
     * قائمة بالنسخ الاحتياطية الحالية مرتبة من الأحدث للأقدم.
     *
     * @return array<int, array{name: string, path: string, size: int, created_at: \Illuminate\Support\Carbon}>
     */
    public function list(): array
    {
        $files = glob($this->directory . DIRECTORY_SEPARATOR . 'backup_*.sql') ?: [];

        $rows = [];
        foreach ($files as $file) {
            $rows[] = [
                'name'       => basename($file),
                'path'       => $file,
                'size'       => filesize($file) ?: 0,
                'created_at' => Carbon::createFromTimestamp(filemtime($file) ?: time()),
            ];
        }

        usort($rows, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $rows;
    }

    /**
     * مسار آمن لملف نسخة احتياطية بناءً على اسم الملف فقط (يمنع directory traversal).
     */
    public function resolvePath(string $filename): ?string
    {
        // فقط أسماء الملفات بنمط backup_*.sql مسموحة
        if (! preg_match('/^backup_[\d\-_]+\.sql$/', $filename)) {
            return null;
        }

        $path = $this->directory . DIRECTORY_SEPARATOR . $filename;
        return is_file($path) ? $path : null;
    }

    /**
     * حذف نسخة احتياطية. يُعيد true عند النجاح.
     */
    public function delete(string $filename): bool
    {
        $path = $this->resolvePath($filename);
        if (! $path) {
            return false;
        }
        return @unlink($path);
    }

    /**
     * البحث عن mysqldump في PATH أو في المسارات الشائعة على Windows.
     */
    private function findMysqldump(): ?string
    {
        $finder = new ExecutableFinder();
        $found  = $finder->find('mysqldump');
        if ($found) {
            return $found;
        }

        // Windows: مسارات شائعة لـ XAMPP / MySQL / MariaDB
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.6\\bin\\mysqldump.exe',
        ];
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
