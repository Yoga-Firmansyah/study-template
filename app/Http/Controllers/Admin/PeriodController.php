<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{AuditHistory, Period};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Session};
use Inertia\Inertia;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort_field', 'direction', 'per_page']);
        $perPage = $request->input('per_page', 10);

        $periods = Period::search($request->search, ['name'])
            ->sort($request->sort_field ?? 'doc_audit_start', $request->direction ?? 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Master/Periods/Index', [
            'periods' => $periods,
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'doc_audit_start' => 'required|date',
            'doc_audit_end' => 'required|date|after:doc_audit_start',
            'field_audit_start' => 'required|date|after:doc_audit_end',
            'field_audit_end' => 'required|date|after:field_audit_start',
            'finding_start' => 'required|date|after:field_audit_end',
            'finding_end' => 'required|date|after:finding_start',
            'reporting_start' => 'required|date|after:finding_end',
            'reporting_end' => 'required|date|after:reporting_start',
            'rtm_rtl_start' => 'required|date|after:reporting_end',
            'rtm_rtl_end' => 'required|date|after:rtm_rtl_start',
            'is_active' => 'required|boolean'
        ]);

        DB::transaction(function () use ($validated) {
            $period = Period::create($validated);

            // History: Create
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => Period::class,
                'historable_id' => $period->id,
                'stage' => 'master_setup',
                'action' => 'create_period',
                'new_values' => $period->toArray(),
            ]);
        });

        Session::flash('toastr', ['type' => 'gradient-primary', 'content' => 'Periode berhasil diterbitkan.']);
        return back();
    }

    public function update(Request $request, Period $period)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'doc_audit_start' => 'required|date',
            'doc_audit_end' => 'required|date|after:doc_audit_start',
            'field_audit_start' => 'required|date|after:doc_audit_end',
            'field_audit_end' => 'required|date|after:field_audit_start',
            'finding_start' => 'required|date|after:field_audit_end',
            'finding_end' => 'required|date|after:finding_start',
            'reporting_start' => 'required|date|after:finding_end',
            'reporting_end' => 'required|date|after:reporting_start',
            'rtm_rtl_start' => 'required|date|after:reporting_end',
            'rtm_rtl_end' => 'required|date|after:rtm_rtl_start',
            'is_active' => 'required|boolean'
        ]);

        $oldValues = $period->toArray();

        DB::transaction(function () use ($period, $validated, $oldValues) {
            $period->update($validated);

            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => Period::class,
                'historable_id' => $period->id,
                'stage' => 'master_setup',
                'action' => 'update_period',
                'old_values' => $oldValues,
                'new_values' => $period->getChanges(),
            ]);
        });

        return back()->with('toastr', ['type' => 'gradient-info', 'content' => 'Jadwal periode diperbarui.']);
    }

    public function destroy(Period $period)
    {
        DB::transaction(function () use ($period) {
            AuditHistory::create([
                'user_id' => auth()->id(),
                'historable_type' => Period::class,
                'historable_id' => $period->id,
                'stage' => 'master_setup',
                'action' => 'delete_period',
                'old_values' => $period->toArray(),
            ]);
            $period->delete();
        });

        return back()->with('toastr', ['type' => 'gradient-red-to-pink', 'content' => 'Periode dihapus.']);
    }
}
