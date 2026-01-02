<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentIndicator;
use App\Services\AssignmentService;
use DB;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AssignmentIndicatorController extends Controller
{
    protected $service;

    public function __construct(AssignmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Update skor, catatan, dan bukti (Smart History via Service)
     */
    public function update(Request $request, AssignmentIndicator $indicator)
    {
        Gate::authorize('updateScore', $indicator->assignment);
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

        // Service menangani pencatatan polimorfik secara otomatis
        DB::transaction(function () use ($indicator, $validated) {
            $this->service->updateIndicator($indicator, $validated, auth()->id());
        });


        Session::flash('toastr', [
            'type' => 'gradient-green-to-emerald',
            'content' => 'Data indikator diperbarui.'
        ]);
        return back();
    }

    /**
     * Mengambil riwayat khusus untuk indikator ini (On-Click History)
     */
    public function history(AssignmentIndicator $indicator)
    {
        // Polymorphic relation: hanya mengambil log milik model AssignmentIndicator ini
        $history = $indicator->histories()
            ->with('user:id,name,role')
            ->latest()
            ->get();

        return response()->json($history);
    }
}
