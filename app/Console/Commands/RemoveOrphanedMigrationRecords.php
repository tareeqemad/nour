<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RemoveOrphanedMigrationRecords extends Command
{
    protected $signature = 'migrations:remove-orphans
                            {--dry-run : List orphaned records without deleting}';

    protected $description = 'Remove migration table records whose files no longer exist (e.g. merged or deleted migrations)';

    public function handle(): int
    {
        $migrationsPath = database_path('migrations');
        $ran = DB::table('migrations')->orderBy('batch')->orderBy('migration')->pluck('migration');

        $orphans = [];
        foreach ($ran as $name) {
            $path = $migrationsPath . DIRECTORY_SEPARATOR . $name . '.php';
            if (!File::exists($path)) {
                $orphans[] = $name;
            }
        }

        if (empty($orphans)) {
            $this->info('No orphaned migration records found.');
            return Command::SUCCESS;
        }

        $this->warn('Orphaned migration records (file missing):');
        foreach ($orphans as $m) {
            $this->line('  - ' . $m);
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run. No changes made. Run without --dry-run to delete these records.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Delete these ' . count($orphans) . ' record(s) from the migrations table?')) {
            return Command::SUCCESS;
        }

        $deleted = DB::table('migrations')->whereIn('migration', $orphans)->delete();
        $this->info("Removed {$deleted} orphaned migration record(s).");
        return Command::SUCCESS;
    }
}
