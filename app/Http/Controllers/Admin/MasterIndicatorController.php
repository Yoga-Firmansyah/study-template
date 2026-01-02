<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{MasterStandard, MasterIndicator, AuditHistory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Session, DB};
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MasterIndicatorController extends Controller
{
    public function index(Request $request, MasterStandard $standard)
    {
        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $indicators = $standard->indicators()
            ->search($request->search, ['requirement', 'code'])
            ->sort($request->sort_field ?? 'code', $request->direction ?? 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Master/Indicators/Index', [
            'standard' => $standard,
            'indicators' => $indicators,
            'filters' => $filters
        ]);
    }

    public function store(Request $request, MasterStandard $standard)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('master_indicators')->where('master_standard_id', $standard->id)
            ],
            'requirement' => 'required|string',
            'is_evidence_required' => 'required|boolean',
            'template' => 'nullable|file|mimes:pdf,docx,xlsx|max:5120',
        ]);

        if ($request->hasFile('template')) {
            $validated['template_path'] = $request->file('template')->store('templates');
        }

        DB::transaction(function () use ($request, $standard, $validated) {
            $indicator = $standard->indicators()->create($validated);

            // Catat History Create
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => MasterIndicator::class,
                'historable_id' => $indicator->id,
                'stage' => 'master_setup',
                'action' => 'create_master_indicator',
                'new_values' => $indicator->toArray(),
            ]);
        });

        Session::flash('toastr', ['type' => 'gradient-primary', 'content' => 'Indikator berhasil ditambahkan.']);
        return back();
    }

    /**
     * Update dengan Pencatatan History Otomatis
     */
    public function update(Request $request, MasterIndicator $indicator)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('master_indicators')
                    ->where('master_standard_id', $indicator->master_standard_id)
                    ->ignore($indicator->id)
            ],
            'requirement' => 'required|string',
            'is_evidence_required' => 'required|boolean',
            'template' => 'nullable|file|mimes:pdf,docx,xlsx|max:5120',
        ]);

        // 1. Ambil data lama sebelum diupdate untuk history
        $oldValues = $indicator->only(['code', 'requirement', 'is_evidence_required', 'template_path']);

        DB::transaction(function () use ($request, $indicator, $validated, $oldValues) {
            // 2. Kelola file template
            if ($request->hasFile('template')) {
                if ($indicator->template_path) {
                    Storage::delete($indicator->template_path);
                }
                $validated['template_path'] = $request->file('template')->store('templates');
            }

            // 3. Update data indikator
            $indicator->update($validated);

            // 4. Catat perubahan ke AuditHistory
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => MasterIndicator::class,
                'historable_id' => $indicator->id,
                'stage' => 'master_setup', // Label khusus untuk perubahan data master
                'old_values' => $oldValues,
                'new_values' => $indicator->only(['code', 'requirement', 'is_evidence_required', 'template_path']),
                'action' => 'update_master_indicator',
                'reason' => 'Perubahan pada instrumen master oleh Admin'
            ]);
        });

        Session::flash('toastr', ['type' => 'gradient-info', 'content' => 'Indikator dan riwayat perubahan berhasil diperbarui.']);
        return back();
    }

    public function destroy(MasterIndicator $indicator)
    {
        DB::transaction(function () use ($indicator) {
            // Catat History sebelum hapus
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => MasterIndicator::class,
                'historable_id' => $indicator->id,
                'stage' => 'master_setup',
                'action' => 'delete_master_indicator',
                'old_values' => $indicator->toArray(),
            ]);
            $indicator->delete();
        });

        Session::flash('toastr', ['type' => 'gradient-red-to-pink', 'content' => 'Indikator berhasil dihapus.']);
        return back();
    }

    /**
     * Endpoint History Master Indicator (AJAX)
     */
    public function history(MasterIndicator $indicator)
    {
        return response()->json($indicator->histories()->with('user:id,name')->latest()->get());
    }
}
