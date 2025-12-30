<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProdiController extends Controller
{
    /**
     * Menampilkan daftar semua Program Studi.
     */
    public function index()
    {
        return Inertia::render('Master/Prodi/Index', [
            'prodis' => Prodi::all()
        ]);
    }

    /**
     * Menyimpan Program Studi baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:prodis,code',
        ]);

        Prodi::create($validated);

        Session::flash('toastr', [
            'type' => 'gradient-primary',
            'content' => 'Prodi <b>' . $request->name . '</b> berhasil ditambahkan.'
        ]);

        return back();
    }

    /**
     * Memperbarui data Program Studi yang sudah ada.
     */
    public function update(Request $request, Prodi $prodi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Memastikan kode unik kecuali untuk prodi itu sendiri saat ini
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('prodis')->ignore($prodi->id)
            ],
        ]);

        $prodi->update($validated);

        Session::flash('toastr', [
            'type' => 'gradient-info',
            'content' => 'Data prodi berhasil diperbarui.'
        ]);

        return back();
    }

    /**
     * Menghapus Program Studi secara lunak (Soft Delete).
     */
    public function destroy(Prodi $prodi)
    {
        // Menyimpan nama untuk pesan notifikasi sebelum dihapus
        $prodiName = $prodi->name;

        $prodi->delete(); // Memicu SoftDeletes sesuai Model

        Session::flash('toastr', [
            'type' => 'gradient-red-to-pink',
            'content' => 'Prodi <b>' . $prodiName . '</b> berhasil dihapus.'
        ]);

        return back();
    }
}
