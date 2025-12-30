<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Assignment, AuditHistory, Prodi, Period, MasterStandard, User};
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Menampilkan daftar penugasan berdasarkan Role
     */
    public function index(Request $request)
    {
        // 1. Ambil input filter & sort
        $filters = $request->only(['search', 'sort_field', 'direction']);
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $assignments = Assignment::with(['prodi', 'period', 'standard', 'auditor'])
            // 2. Logika Search Global
            ->when($request->input('search'), function ($q, $search) {
                $q->whereHas('prodi', fn($p) => $p->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('auditor', fn($a) => $a->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('period', fn($per) => $per->where('name', 'like', "%{$search}%"));
            })
            // 3. Logika Sorting
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString(); // Mempertahankan parameter di URL

        return Inertia::render('Assignments/Index', [
            'assignments' => $assignments,
            'filters' => $filters, // Mengirim balik ke UI untuk state input
            'prodis' => Prodi::all(['id', 'name']),
            'periods' => Period::where('is_active', true)->get(['id', 'name']),
            'standards' => MasterStandard::all(['id', 'name']),
            'auditors' => User::where('role', 'auditor')->get(['id', 'name'])
        ]);
    }

    /**
     * Membuat penugasan & Snapshot Indikator via Service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'master_standard_id' => 'required|exists:master_standards,id',
            'prodi_id' => 'required|exists:prodis,id',
            'auditor_id' => 'required|exists:users,id',
        ]);

        $assignment = $this->assignmentService->createAssignment($validated);


        Session::flash('toastr', [
            'type' => 'gradient-green-to-emerald',
            'content' => 'Penugasan AMI berhasil dibuat.'
        ]);
        return redirect()->route('assignments.show', $assignment->id);
    }

    /**
     * Menampilkan detail penugasan dengan Fitur Search Indikator
     */
    public function show(Request $request, Assignment $assignment)
    {
        $search = $request->input('search');

        $assignment->load(['period', 'standard', 'prodi', 'auditor', 'documents', 'histories.user']);

        // Memuat indikator dengan filter search jika ada
        $indicators = $assignment->indicators()
            ->when($search, function ($q) use ($search) {
                $q->where('snapshot_code', 'like', "%{$search}%")
                    ->orWhere('snapshot_requirement', 'like', "%{$search}%");
            })
            ->get();

        return Inertia::render('Assignments/Show', [
            'assignment' => $assignment,
            'indicators' => $indicators, // Mengirim indikator yang sudah difilter
            'currentStage' => $assignment->current_stage,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Mengunggah Berita Acara atau Laporan Akhir dengan validasi tahap
     */
    public function uploadDocument(Request $request, Assignment $assignment)
    {
        $request->validate([
            'type' => 'required|in:ba_lapangan,ba_final,laporan_akhir',
            'file' => 'required|file|mimes:pdf|max:5120',
        ]);

        $stage = $assignment->current_stage;

        // Stage-Gate Validation
        if ($request->type === 'ba_lapangan' && !in_array($stage, ['field_audit', 'finding'])) {
            return back()->withErrors(['message' => 'BA Lapangan hanya dapat diunggah pada tahap Audit Lapangan atau Temuan.']);
        }

        if ($request->type === 'ba_final' && $stage !== 'reporting') {
            return back()->withErrors(['message' => 'BA Final hanya dapat diunggah pada tahap Pelaporan.']);
        }

        if ($request->type === 'laporan_akhir' && !in_array($stage, ['reporting', 'rtm_rtl'])) {
            return back()->withErrors(['message' => 'Laporan Akhir hanya dapat diunggah pada tahap Pelaporan atau RTM/RTL.']);
        }

        $this->assignmentService->uploadAssignmentDocument(
            $assignment,
            $request->only('type'),
            $request->file('file'),
            auth()->id()
        );

        Session::flash('toastr', [
            'type' => 'gradient-green-to-emerald',
            'content' => 'Dokumen resmi berhasil diunggah.'
        ]);
        return back();
    }

    /**
     * Menghapus penugasan (Soft Delete)
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        Session::flash('toastr', [
            'type' => 'gradient-green-to-emerald',
            'content' => 'Penugasan berhasil dihapus.'
        ]);
        return redirect()->route('assignments.index');
    }

    /**
     * Auditor memberikan simpulan akhir dan rating (Finalisasi Audit)
     */
    public function finalize(Request $request, Assignment $assignment)
    {
        // Validasi input
        $validated = $request->validate([
            'summary_note' => 'required|string|min:10',
            'overall_rating' => 'required|integer|min:1|max:4',
        ]);

        // Hanya auditor yang ditugaskan yang bisa melakukan finalisasi
        if (auth()->id() !== $assignment->auditor_id && !auth()->user()->isAdmin()) {
            return back()->withErrors(['message' => 'Anda tidak memiliki otoritas untuk memfinalisasi audit ini.']);
        }

        // Pastikan audit belum selesai
        if ($assignment->completed_at) {
            return back()->withErrors(['message' => 'Audit ini sudah difinalisasi sebelumnya.']);
        }

        // Update data dan set waktu selesai
        $assignment->update([
            'summary_note' => $validated['summary_note'],
            'overall_rating' => $validated['overall_rating'],
            'completed_at' => now(),
        ]);

        // Catat ke History bahwa Audit telah diselesaikan
        AuditHistory::create([
            'user_id' => auth()->id(),
            'historable_type' => Assignment::class,
            'historable_id' => $assignment->id,
            'stage' => $assignment->current_stage,
            'action' => 'finalize_audit',
            'new_values' => $assignment->only(['summary_note', 'overall_rating', 'completed_at']),
        ]);

        Session::flash('toastr', [
            'type' => 'gradient-green-to-emerald',
            'content' => 'Audit berhasil difinalisasi dengan rating <b>' . $validated['overall_rating'] . '</b>.'
        ]);

        return back();
    }
}
