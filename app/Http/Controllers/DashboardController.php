<?php

namespace App\Http\Controllers;

use App\Models\{Assignment, Period, Prodi, AuditHistory, User};
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Mengembalikan data dan komponen Vue berdasarkan role
        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'auditor' => $this->auditorDashboard($user),
            'auditee' => $this->auditeeDashboard($user),
            default => abort(403),
        };
    }

    private function adminDashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_prodi' => Prodi::count(),
                'active_periods' => Period::where('is_active', true)->count(),
                'total_auditors' => User::where('role', 'auditor')->count(),
                'total_assignments' => Assignment::count(),
            ],
            // Progres AMI Nasional
            'progress' => [
                'finished' => Assignment::whereNotNull('completed_at')->count(),
                'ongoing' => Assignment::whereNull('completed_at')->count(),
            ]
        ]);
    }

    private function auditorDashboard($user)
    {
        return Inertia::render('Auditor/Dashboard', [
            'stats' => [
                'my_assignments' => Assignment::where('auditor_id', $user->id)->count(),
                'pending_reviews' => Assignment::where('auditor_id', $user->id)->whereNull('completed_at')->count(),
                'completed_reviews' => Assignment::where('auditor_id', $user->id)->whereNotNull('completed_at')->count(),
            ],
            // Daftar tugas terbaru milik auditor tersebut
            'recent_tasks' => Assignment::with('prodi', 'period')
                ->where('auditor_id', $user->id)
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    private function auditeeDashboard($user)
    {
        return Inertia::render('Auditee/Dashboard', [
            'prodi' => $user->prodi?->name,
            'stats' => [
                'total_audit' => Assignment::where('prodi_id', $user->prodi_id)->count(),
                'latest_assignment' => Assignment::with('period')
                    ->where('prodi_id', $user->prodi_id)
                    ->latest()
                    ->first()
            ],
            // Fokus pada pengisian bukti (evidence)
        ]);
    }
}
