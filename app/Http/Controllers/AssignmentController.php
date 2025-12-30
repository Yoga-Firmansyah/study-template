<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentIndicator;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Menampilkan detail penugasan AMI
     */
    public function show(Assignment $assignment)
    {
        $assignment->load(['period', 'standard', 'prodi', 'auditor', 'indicators', 'histories', 'documents']);

        return Inertia::render('Assignments/Show', [
            'assignment' => $assignment,
            'currentStage' => $assignment->current_stage, // Untuk kontrol UI di Vue
            'auditHistories' => $assignment->auditor->auditHistories()->latest()->take(10)->get()
        ]);
    }

    /**
     * Membuat penugasan baru (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'master_standard_id' => 'required|exists:master_standards,id',
            'prodi_id' => 'required|exists:prodis,id',
            'auditor_id' => 'required|exists:users,id',
        ]);

        // Snapshot otomatis via Service
        $assignment = $this->assignmentService->createAssignment($validated);

        return redirect()->route('assignments.show', $assignment->id)
            ->with('success', 'Penugasan AMI berhasil dibuat dan instrumen telah disiapkan.');
    }

    /**
     * Update skor, catatan, atau file bukti
     */
    public function updateIndicator(Request $request, AssignmentIndicator $indicator)
    {
        $validated = $request->validate([
            'score' => 'nullable|integer|min:1|max:4',
            'auditor_note' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'evidence_url' => 'nullable|url',
            'evidence_file' => 'nullable|file|mimes:pdf,jpg,png,zip|max:10240',
        ]);

        if ($request->hasFile('evidence_file')) {
            $path = $request->file('evidence_file')->store('evidence/' . $indicator->assignment_id);
            $validated['evidence_path'] = $path;
        }

        // Logic History & File Management dipusatkan di Service
        $this->assignmentService->updateIndicator($indicator, $validated, auth()->id());

        return back()->with('success', 'Data indikator berhasil diperbarui.');
    }

    /**
     * Mengunggah Berita Acara atau Laporan Akhir
     */
    public function uploadDocument(Request $request, Assignment $assignment)
    {
        $request->validate([
            'type' => 'required|in:ba_lapangan,ba_final,laporan_akhir',
            'file' => 'required|file|mimes:pdf|max:5120',
        ]);

        $stage = $assignment->current_stage;

        // Validasi Tahap (Stage-Gate Validation)
        if ($request->type === 'ba_lapangan' && !in_array($stage, ['field_audit', 'finding'])) {
            return back()->withErrors(['message' => 'BA Lapangan hanya dapat diunggah pada tahap Audit Lapangan atau Temuan.']);
        }

        if ($request->type === 'ba_final' && $stage !== 'reporting') {
            return back()->withErrors(['message' => 'BA Final hanya dapat diunggah pada tahap Pelaporan.']);
        }

        // Tambahan Validasi Laporan Akhir agar konsisten
        if ($request->type === 'laporan_akhir' && !in_array($stage, ['reporting', 'rtm_rtl'])) {
            return back()->withErrors(['message' => 'Laporan Akhir hanya dapat diunggah pada tahap Pelaporan atau RTM/RTL.']);
        }

        // Perbaikan: Gunakan variabel $this->assignmentService yang benar
        $this->assignmentService->uploadAssignmentDocument(
            $assignment,
            $request->only('type'),
            $request->file('file'),
            auth()->id()
        );

        return back()->with('success', 'Dokumen resmi berhasil diunggah dan tercatat dalam histori.');
    }

    /**
     * Mengambil history khusus untuk satu indikator (AJAX)
     */
    public function indicatorHistory(AssignmentIndicator $indicator)
    {
        // Memastikan user memiliki akses melihat history ini
        $history = $indicator->histories()
            ->with('user:id,name,role')
            ->latest()
            ->get();

        return response()->json($history);
    }
}
