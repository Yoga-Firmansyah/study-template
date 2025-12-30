<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AssignmentIndicator;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentIndicatorController extends Controller
{
    protected $service;

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    public function update(Request $request, AssignmentIndicator $indicator)
    {
        // 1. Otorisasi: Pastikan indikator ini milik prodi user yang login
        if ($indicator->assignment->prodi_id !== auth()->user()->prodi_id) {
            abort(403);
        }

        // 2. Validasi: Auditee hanya boleh mengisi bukti, bukan skor/catatan
        $validated = $request->validate([
            'evidence_url' => 'nullable|url',
            'evidence_file' => 'nullable|file|mimes:pdf,jpg,png,zip|max:10240',
        ]);

        if ($request->hasFile('evidence_file')) {
            $validated['evidence_path'] = $request->file('evidence_file')->store('evidence/' . $indicator->assignment_id);
        }

        // 3. Simpan via Service (Otomatis mencatat riwayat perubahan polimorfik)
        $this->service->updateIndicator($indicator, $validated, auth()->id());

        return back()->with('success', 'Bukti berhasil diunggah dan tercatat di riwayat.');
    }

    /**
     * FITUR TAMBAHAN: Melihat riwayat perbaikan indikator
     */
    public function history(AssignmentIndicator $indicator)
    {
        if ($indicator->assignment->prodi_id !== auth()->user()->prodi_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $history = $indicator->histories()
            ->with('user:id,name,role')
            ->latest()
            ->get();

        return response()->json($history);
    }
}
