<?php

namespace App\Console\Commands;

use App\Models\AuthorizedPhone;
use Illuminate\Console\Command;

class ClearAuthorizedPhones extends Command
{
    protected $signature = 'authorized-phones:clear';
    protected $description = 'حذف جميع الأرقام المصرح بها';

    public function handle(): int
    {
        $count = AuthorizedPhone::count();
        
        if ($this->confirm("هل أنت متأكد من حذف جميع الأرقام المصرح بها ({$count} رقم)؟", false)) {
            AuthorizedPhone::truncate();
            $this->info("تم حذف جميع الأرقام المصرح بها بنجاح!");
            return Command::SUCCESS;
        }
        
        $this->info("تم إلغاء العملية");
        return Command::SUCCESS;
    }
}
