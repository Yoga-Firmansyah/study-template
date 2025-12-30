<?php

namespace App\Http\Controllers;

use App\Models\{Assignment, Period, Prodi, AuditHistory};
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Menampilkan Ringkasan Eksekutif AMI
     */
    public function index()
    {
        $user = auth()->user();
        $query = Assignment::query();

        // 1. Filter Data Berdasarkan Role
        if ($user->role === 'auditor') {
            $query->where('auditor_id', $user->id);
        } elseif ($user->role === 'auditee') {
            $query->where('prodi_id', $user->prodi_id);
        }

        // 2. Statistik Utama
        $stats = [
            'total_assignments' => (clone $query)->count(),
            'completed' => (clone $query)->whereNotNull('completed_at')->count(),
            'on_progress' => (clone $query)->whereNull('completed_at')->count(),
            'total_prodi' => Prodi::count(),
        ];

        // 3. Progres per Tahap (Stage Distribution)
        $stageDistribution = (clone $query)
            ->selectRaw('current_stage, count(*) as count')
            ->groupBy('current_stage')
            ->get();

        // 4. Jadwal Aktif Saat Ini
        $activePeriod = Period::where('is_active', true)->first();

        // 5. Aktivitas Terbaru (Recent Logs)
        $recentActivities = AuditHistory::with(['user', 'historable'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'stageDistribution' => $stageDistribution,
            'activePeriod' => $activePeriod,
            'recentActivities' => $recentActivities,
            'userRole' => $user->role
        ]);
    }
}
