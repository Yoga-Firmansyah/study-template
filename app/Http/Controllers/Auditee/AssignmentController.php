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
        // 1. Ambil input filter, sort, & per_page
        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $assignments = Assignment::with(['prodi', 'period', 'standard'])
            ->where('prodi_id', auth()->user()->prodi_id) // Filter prodi milik user
            ->search($request->search) // Menggunakan scopeSearch kustom di Model
            ->sort($request->sort_field, $request->direction) // Menggunakan scope dari Trait
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Auditee/Assignments/Index', [
            'assignments' => $assignments,
            'filters' => $filters
        ]);
    }

    public function show(Request $request, Assignment $assignment)
    {
        // Otorisasi: Pastikan auditee hanya bisa melihat assignment milik prodinya
        Gate::authorize('view', $assignment);

        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 25);

        $assignment->load(['period', 'standard', 'auditor', 'documents']);

        // Paginasi Indikator agar seragam dengan tampilan Auditor
        $indicators = $assignment->indicators()
            ->when($request->search, function ($q, $search) {
                $q->where('snapshot_code', 'like', "%{$search}%")
                    ->orWhere('snapshot_requirement', 'like', "%{$search}%");
            })
            ->sort($request->sort_field ?? 'snapshot_code', $request->direction ?? 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Auditee/Assignments/Show', [
            'assignment' => $assignment,
            'indicators' => $indicators,
            'filters' => $filters,
            'currentStage' => $assignment->current_stage
        ]);
    }
}
