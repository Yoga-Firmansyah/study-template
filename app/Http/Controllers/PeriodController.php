<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class PeriodController extends Controller
{
    /**
     * Tampilan daftar periode (Hanya Admin)
     */
    public function index()
    {
        return Inertia::render('Master/Periods/Index', [
            'periods' => Period::latest()->get()
        ]);
    }

    /**
     * Simpan periode baru
     */
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
        ]);

        // Mutator di Model otomatis mengolah jam menjadi 00:00/23:59
        Period::create($validated);

        Session::flash('toastr', [
            'type' => 'gradient-primary',
            'content' => 'Jadwal periode <b>' . $request->name . '</b> berhasil diterbitkan.'
        ]);

        return back();
    }

    /**
     * Update jadwal periode
     */
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
        ]);

        $period->update($validated);

        Session::flash('toastr', [
            'type' => 'gradient-info',
            'content' => 'Perubahan jadwal berhasil disimpan.'
        ]);

        return back();
    }

    /**
     * Hapus periode (Soft Delete)
     */
    public function destroy(Period $period)
    {
        $name = $period->name;
        $period->delete(); // Mendukung SoftDeletes

        Session::flash('toastr', [
            'type' => 'gradient-red-to-pink',
            'content' => 'Periode <b>' . $name . '</b> telah dihapus.'
        ]);

        return back();
    }
}
