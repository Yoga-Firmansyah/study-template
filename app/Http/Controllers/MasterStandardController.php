<?php

namespace App\Http\Controllers;

use App\Models\MasterStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MasterStandardController extends Controller
{
    /**
     * Menampilkan daftar semua Standar Mutu.
     * Hanya Admin yang memiliki akses penuh ke halaman ini.
     */
    public function index()
    {
        return Inertia::render('Master/Standards/Index', [
            'standards' => MasterStandard::withCount('indicators')->get()
        ]);
    }

    /**
     * Menyimpan Standar Mutu baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:master_standards,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        MasterStandard::create($validated);

        Session::flash('toastr', [
            'type' => 'gradient-primary',
            'content' => 'Standar Mutu <b>' . $request->name . '</b> berhasil dibuat.'
        ]);

        return back();
    }

    /**
     * Memperbarui data Standar Mutu.
     * Sangat penting jika ada perubahan penamaan atau kode standar tanpa menghapus data.
     */
    public function update(Request $request, MasterStandard $standard)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('master_standards')->ignore($standard->id)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $standard->update($validated);

        Session::flash('toastr', [
            'type' => 'gradient-info',
            'content' => 'Standar Mutu berhasil diperbarui.'
        ]);

        return back();
    }

    /**
     * Menghapus Standar Mutu secara lunak (Soft Delete).
     */
    public function destroy(MasterStandard $standard)
    {
        $name = $standard->name;

        // Memastikan standar tetap ada di database (SoftDelete) demi integritas riwayat audit
        $standard->delete();

        Session::flash('toastr', [
            'type' => 'gradient-red-to-pink',
            'content' => 'Standar <b>' . $name . '</b> berhasil dihapus.'
        ]);

        return back();
    }
}
