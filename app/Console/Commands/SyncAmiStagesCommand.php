<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Services\AssignmentService;
use Illuminate\Console\Command;

class SyncAmiStagesCommand extends Command
{
    protected $signature = 'ami:sync-stages';
    protected $description = 'Sinkronisasi tahap harian untuk semua penugasan AMI';

    public function handle(AssignmentService $service)
    {
        // Ambil semua yang belum selesai
        Assignment::whereNull('completed_at')->chunk(100, function ($assignments) use ($service) {
            foreach ($assignments as $assignment) {
                $service->syncCurrentStage($assignment);
            }
        });

        $this->info('Daily stage sync completed.');
    }
}
