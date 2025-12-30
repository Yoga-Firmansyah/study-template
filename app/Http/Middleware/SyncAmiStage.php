<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Assignment;
use App\Services\AssignmentService;
use Illuminate\Http\Request;

class SyncAmiStage
{
    public function handle(Request $request, Closure $next)
    {
        // Cari assignment dari parameter route
        $assignment = $request->route('assignment');

        if ($assignment instanceof Assignment && !$assignment->completed_at) {
            // Jalankan sinkronisasi instan saat user online
            app(AssignmentService::class)->syncCurrentStage($assignment);
        }

        return $next($request);
    }
}
