<?php

namespace App\Http\Controllers;

use App\Enums\AssignmentDocType;
use App\Models\AssignmentDocument;
use DB;
use Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class AssignmentDocumentController extends Controller
{
    /**
     * Mengunduh dokumen BA atau Laporan Akhir
     */
    public function download(AssignmentDocument $document)
    {
        // Pastikan file fisik ada
        if (!Storage::exists($document->file_path)) {
            return back()->withErrors(['message' => 'File tidak ditemukan di server.']);
        }

        return Storage::download(
            $document->file_path,
            $document->type . '-' . $document->assignment_id . '.pdf'
        );
    }

    /**
     * Menghapus dokumen (Hanya jika tahap audit belum berpindah)
     */
    public function destroy(AssignmentDocument $document)
    {
        $assignment = $document->assignment;
        Gate::authorize('deleteDocument', $assignment);
        if ($document->isLockedForReportChange()) {
            return back()->withErrors([
                'message' => 'Dokumen sudah dikunci dan tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($document) {
            Storage::delete($document->file_path);
            $document->delete();
        });

        Session::flash('toastr', [
            'type' => 'gradient-red-to-pink',
            'content' => 'Dokumen berhasil dihapus.',
        ]);

        return back();
    }

}
