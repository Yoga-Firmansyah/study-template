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
        $search = $request->input('search');
        $assignments = Assignment::with(['prodi', 'period'])
            ->where('auditor_id', auth()->id())
            ->when($search, function ($q) use ($search) {
                $q->whereHas('prodi', fn($p) => $p->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString(); // Tambahkan ini agar search tidak hilang saat pindah page

        return inertia('Auditor/Assignments/Index', [
            'assignments' => $assignments,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Menampilkan detail dengan data history header
     */
    public function show(Request $request, Assignment $assignment)
    {
        // Pastikan hanya auditor terkait yang bisa melihat
        Gate::authorize('view', $assignment);

        $search = $request->input('search');
        $assignment->load(['period', 'standard', 'prodi', 'auditor', 'documents', 'histories.user']);

        $indicators = $assignment->indicators()
            ->when($search, function ($q) use ($search) {
                $q->where('snapshot_code', 'like', "%{$search}%")
                    ->orWhere('snapshot_requirement', 'like', "%{$search}%");
            })
            ->get();

        return inertia('Auditor/Assignments/Show', [
            'assignment' => $assignment,
            'indicators' => $indicators,
            'currentStage' => $assignment->current_stage,
            'filters' => $request->only(['search'])
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

        // Gunakan DB Transaction untuk keamanan data
        \DB::transaction(function () use ($assignment, $validated) {
            $assignment->update([
                'summary_note' => $validated['summary_note'],
                'overall_rating' => $validated['overall_rating'],
                'completed_at' => now(),
            ]);

            $assignment->histories()->create([
                'user_id' => auth()->id(),
                'stage' => $assignment->current_stage,
                'action' => 'finalize_audit',
                'new_values' => $validated,
            ]);
        });

        Session::flash('toastr', ['type' => 'gradient-green-to-emerald', 'content' => 'Audit telah difinalisasi.']);
        return back();
    }
}
