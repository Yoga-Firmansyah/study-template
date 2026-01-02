<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\AssignmentService; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Session, Gate};
use Inertia\Inertia;

class AssignmentController extends Controller
{
    protected $assignmentService;

    // Tambahkan constructor untuk service
    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        // 1. Ambil filter, sort, dan per_page
        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $assignments = Assignment::with(['prodi', 'period'])
            ->where('auditor_id', auth()->id()) // Proteksi: Hanya milik auditor login
            ->search($request->search) // Gunakan scopeSearch yang sudah di-override di Model
            ->sort($request->sort_field, $request->direction) // Trait Filterable
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Auditor/Assignments/Index', [
            'assignments' => $assignments,
            'filters' => $filters
        ]);
    }

    public function show(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $assignment->load(['period', 'standard', 'prodi', 'auditor', 'documents', 'histories.user']);

        $indicators = $assignment->indicators()
            ->when($request->search, function ($q, $search) {
                $q->where('snapshot_code', 'like', "%{$search}%")
                    ->orWhere('snapshot_requirement', 'like', "%{$search}%");
            })
            // Gunakan sort dari Trait
            ->sort($request->sort_field ?? 'snapshot_code', $request->direction ?? 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return inertia('Auditor/Assignments/Show', [
            'assignment' => $assignment,
            'indicators' => $indicators,
            'filters' => $filters,
            'currentStage' => $assignment->current_stage
        ]);
    }

    /**
     * FITUR BARU: Unggah BA dan Laporan Akhir oleh Auditor
     */
    public function uploadDocument(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $request->validate([
            'type' => 'required|in:ba_lapangan,ba_final,laporan_akhir',
            'file' => 'required|file|mimes:pdf|max:5120',
        ]);

        $stage = $assignment->current_stage;

        // Validasi Tahap (Stage-Gate) agar auditor tidak salah unggah
        if ($request->type === 'ba_lapangan' && !in_array($stage, ['field_audit', 'finding'])) {
            return back()->withErrors(['message' => 'BA Lapangan hanya diunggah pada tahap Lapangan/Temuan.']);
        }

        $this->assignmentService->uploadAssignmentDocument(
            $assignment,
            $request->only('type'),
            $request->file('file'),
            auth()->id()
        );

        return back()->with('success', 'Dokumen resmi berhasil diunggah.');
    }

    public function finalize(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'summary_note' => 'required|string|min:10',
            'overall_rating' => 'required|integer|min:1|max:4',
        ]);

        // Panggil service
        $this->assignmentService->finalizeAssignment($assignment, $validated, auth()->id());

        Session::flash('toastr', ['type' => 'gradient-green-to-emerald', 'content' => 'Audit telah difinalisasi.']);
        return back();
    }
}
