<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\{Assignment};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $assignments = Assignment::with(['prodi', 'period', 'standard'])
            ->where('prodi_id', auth()->user()->prodi_id) // Filter prodi milik user
            ->when($search, function ($q) use ($search) {
                // Mencari berdasarkan nama periode atau kode standar
                $q->whereHas('period', fn($p) => $p->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('standard', fn($s) => $s->where('code', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Agar pencarian tidak hilang saat pindah halaman

        return inertia('Auditee/Assignments/Index', [
            'assignments' => $assignments,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * FITUR TAMBAHAN: Menampilkan detail tugas prodi
     */
    public function show(Assignment $assignment)
    {
        // Otorisasi: Pastikan auditee hanya bisa melihat assignment milik prodinya
        if ($assignment->prodi_id !== auth()->user()->prodi_id) {
            abort(403, 'Anda tidak memiliki akses ke data prodi lain.');
        }

        $assignment->load(['period', 'standard', 'auditor', 'indicators', 'documents']);

        return inertia('Auditee/Assignments/Show', [
            'assignment' => $assignment,
            'currentStage' => $assignment->current_stage // Mengontrol visibilitas tombol upload di UI
        ]);
    }
}
