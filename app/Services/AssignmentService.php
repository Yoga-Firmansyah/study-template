<?php

namespace App\Services;

use App\Models\{Assignment, AssignmentDocument, MasterIndicator, AssignmentIndicator, AuditHistory};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Storage};

class AssignmentService
{
    /**
     * Inisialisasi Penugasan & Snapshot Indikator
     */
    public function createAssignment(array $data): Assignment
    {
        return DB::transaction(function () use ($data) {
            $assignment = Assignment::create($data);

            $masterIndicators = MasterIndicator::where('master_standard_id', $data['master_standard_id'])->get();

            foreach ($masterIndicators as $master) {
                $snapshotPath = $master->template_path;

                // Jika ada template, lakukan COPY fisik agar tidak hilang
                if ($master->template_path && Storage::exists($master->template_path)) {
                    $newPath = 'assignments/' . $assignment->id . '/templates/' . basename($master->template_path);
                    Storage::copy($master->template_path, $newPath);
                    $snapshotPath = $newPath;
                }

                AssignmentIndicator::create([
                    'assignment_id' => $assignment->id,
                    'snapshot_code' => $master->code,
                    'snapshot_requirement' => $master->requirement,
                    'snapshot_template_path' => $snapshotPath,
                ]);
            }

            return $assignment;
        });
    }

    /**
     * Versi Sempurna: Satu metode untuk semua update (Data & File)
     * Mengotomatisasi Smart File Versioning & History
     */
    public function updateIndicator(AssignmentIndicator $indicator, array $newData, int $userId): bool
    {
        $assignment = $indicator->assignment;
        $oldData = $indicator->only(['score', 'auditor_note', 'evidence_path', 'evidence_url', 'recommendation']);

        // 1. Ambil history terakhir di tahap yang sama
        $lastHistoryAtThisStage = $indicator->histories()
            ->where('stage', $assignment->current_stage)
            ->exists();

        // 2. Logika History: Catat jika sudah berganti siklus tahap
        if (!$lastHistoryAtThisStage) {
            $this->recordHistory($indicator, $oldData, $newData, $assignment->current_stage, $userId);
        }

        // 3. Logika Smart File: Hapus fisik jika masih di tahap yang sama
        if (isset($newData['evidence_path']) && $indicator->evidence_path && $lastHistoryAtThisStage) {
            Storage::delete($indicator->evidence_path);
        }

        return $indicator->update($newData);
    }

    /**
     * Sinkronisasi Tahap AMI (Middleware & Scheduler)
     */
    public function syncCurrentStage(Assignment $assignment): void
    {
        $now = now()->startOfDay();
        $period = $assignment->period;
        $oldStage = $assignment->current_stage;
        $newStage = $this->calculateStage($period, $now);

        if ($newStage !== $oldStage) {
            DB::transaction(function () use ($assignment, $oldStage, $newStage) {
                $assignment->update([
                    'current_stage' => $newStage,
                    'completed_at' => ($newStage === 'finished') ? now() : $assignment->completed_at
                ]);

                $this->recordHistory($assignment, ['stage' => $oldStage], ['stage' => $newStage], $newStage, 0);
            });
        }
    }

    private function calculateStage($period, Carbon $now): string
    {
        if ($now->between($period->doc_audit_start, $period->doc_audit_end))
            return 'doc_audit';
        if ($now->between($period->field_audit_start, $period->field_audit_end))
            return 'field_audit';
        if ($now->between($period->finding_start, $period->finding_end))
            return 'finding';
        if ($now->between($period->reporting_start, $period->reporting_end))
            return 'reporting';
        if ($now->between($period->rtm_rtl_start, $period->rtm_rtl_end))
            return 'rtm_rtl';
        return $now->gt($period->rtm_rtl_end) ? 'finished' : 'doc_audit';
    }

    private function recordHistory($model, $old, $new, $stage, $userId): AuditHistory
    {
        return AuditHistory::create([
            'user_id' => $userId,
            'historable_type' => get_class($model),
            'historable_id' => $model->id,
            'stage' => $stage,
            'old_values' => $old,
            'new_values' => $new,
            'action' => 'update_audit_trail',
        ]);
    }

    public function uploadAssignmentDocument(Assignment $assignment, array $data, $file, $userId): AssignmentDocument
    {
        return DB::transaction(function () use ($assignment, $data, $file, $userId) {
            // 1. Simpan File Fisik
            $path = $file->store("documents/{$assignment->id}");

            // 2. Buat Record Dokumen
            $document = AssignmentDocument::create([
                'assignment_id' => $assignment->id,
                'type' => $data['type'], // ba_lapangan, ba_final, laporan_akhir
                'file_path' => $path,
                'uploaded_by' => $userId,
            ]);

            // 3. Catat ke AuditHistory sebagai Milestone Legal
            AuditHistory::create([
                'user_id' => $userId,
                'historable_type' => Assignment::class,
                'historable_id' => $assignment->id,
                'stage' => $assignment->current_stage,
                'action' => 'upload_document',
                'new_values' => [
                    'document_type' => $data['type'],
                    'file_path' => $path
                ],
                'reason' => "Unggah dokumen resmi: " . strtoupper(str_replace('_', ' ', $data['type']))
            ]);

            return $document;
        });
    }

    /**
     * Hapus folder fisik terkait assignment
     */
    public function deleteAssignmentFiles(Assignment $assignment): void
    {
        $path = "assignments/{$assignment->id}";
        if (Storage::exists($path)) {
            Storage::deleteDirectory($path); // Menghapus semua template & dokumen terkait
        }
    }

    public function finalizeAssignment(Assignment $assignment, array $data, int $userId): void
    {
        DB::transaction(function () use ($assignment, $data, $userId) {
            $assignment->update([
                'summary_note' => $data['summary_note'],
                'overall_rating' => $data['overall_rating'],
                'completed_at' => now(),
                'current_stage' => 'finished' // Otomatis set ke selesai
            ]);

            $this->recordHistory($assignment, [], $data, $assignment->current_stage, $userId);
        });
    }
}
