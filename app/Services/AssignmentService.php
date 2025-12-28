<?php
namespace App\Services;

use App\Models\Assignment;
use App\Models\MasterIndicator;
use App\Models\AssignmentIndicator;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    /**
     * Menyimpan penugasan baru dan melakukan snapshot indikator.
     */
    public function createAssignment(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Simpan data utama penugasan
            $assignment = Assignment::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'period_id' => $data['period_id'],
                'master_standard_id' => $data['master_standard_id'],
            ]);

            // 2. Sinkronisasi Auditor (Many-to-Many)
            // Mengisi tabel assignment_auditors
            $assignment->auditors()->sync($data['auditor_ids']);

            // 3. PROSES SNAPSHOT: Fotokopi Indikator Master ke Pelaksanaan
            $this->performSnapshot($assignment);

            return $assignment;
        });
    }

    /**
     * Logika menyalin data dari Master ke tabel Assignment Indicators.
     */
    protected function performSnapshot(Assignment $assignment)
    {
        // Ambil semua butir indikator dari Standar Master yang dipilih
        $masterIndicators = MasterIndicator::where('master_standard_id', $assignment->master_standard_id)->get();

        foreach ($masterIndicators as $master) {
            // Salin data ke tabel snapshot agar independen dari Master
            AssignmentIndicator::create([
                'assignment_id' => $assignment->id,
                'snapshot_code' => $master->code,
                'snapshot_requirement' => $master->requirement,
                'is_evidence_required' => $master->is_evidence_required,
                'score' => null, // Penilaian awal kosong
            ]);
        }
    }
}
