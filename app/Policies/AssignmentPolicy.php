<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assignment;
use App\Enums\UserRole;
use App\Enums\AuditStage;

class AssignmentPolicy
{
    /**
     * Fitur 'before' untuk memberikan akses penuh kepada Admin
     * tanpa perlu mengecek metode lain satu per satu.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Digunakan di AssignmentController@index dan @show
     * Siapa yang boleh melihat detail penugasan?
     */
    public function view(User $user, Assignment $assignment): bool
    {
        return match ($user->role) {
            UserRole::AUDITOR => $assignment->auditor_id === $user->id,
            UserRole::AUDITEE => $assignment->prodi_id === $user->prodi_id,
            default => false,
        };
    }

    /**
     * Digunakan di Auditee/AssignmentIndicatorController@update
     * Bolehkah user mengunggah bukti/evidence?
     */
    public function updateEvidence(User $user, Assignment $assignment): bool
    {
        // 1. User harus Auditee
        // 2. Berasal dari Prodi yang sama
        // 3. Periode audit harus sedang aktif
        return $user->role === UserRole::AUDITEE &&
            $assignment->prodi_id === $user->prodi_id &&
            $assignment->period->is_active;
    }

    /**
     * Digunakan di Auditee/AssignmentIndicatorController@history
     * Bolehkah user melihat riwayat?
     */
    public function auditeeAssignment(User $user, Assignment $assignment): bool
    {
        // 1. User harus Auditee
        // 2. Berasal dari Prodi yang sama
        return $user->role === UserRole::AUDITEE &&
            $assignment->prodi_id === $user->prodi_id;
    }

    /**
     * Digunakan di Auditor/AssignmentIndicatorController@update
     * Bolehkah user mengisi skor dan catatan auditor?
     */
    public function updateScore(User $user, Assignment $assignment): bool
    {
        // 1. User harus Auditor yang ditugaskan
        // 2. Tidak boleh mengisi jika status sudah 'finished'
        return $user->role === UserRole::AUDITOR &&
            $assignment->auditor_id === $user->id &&
            $assignment->current_stage !== AuditStage::FINISHED;
    }

    /**
     * Digunakan di Auditor/AssignmentController@finalize
     * Bolehkah user menutup/finalisasi audit ini?
     */
    public function finalize(User $user, Assignment $assignment): bool
    {
        // Aturan: Harus Auditor yang ditugaskan dan sudah masuk tahap pelaporan/RTM
        return $user->role === UserRole::AUDITOR &&
            $assignment->auditor_id === $user->id &&
            in_array($assignment->current_stage, [
                AuditStage::REPORTING,
                AuditStage::RTM_RTL
            ]);
    }

    /**
     * Digunakan di AssignmentDocumentController@destroy
     * Bolehkah menghapus dokumen? (Logika tambahan selain pengecekan stage)
     */
    public function deleteDocument(User $user, Assignment $assignment): bool
    {
        // Hanya yang mengunggah atau Admin (Admin sudah dihandle di 'before')
        return $assignment->auditor_id === $user->id || $assignment->prodi_id === $user->prodi_id;
    }

    /**
     * Menentukan apakah user boleh mengupdate/mengunggah dokumen penugasan.
     * Dipanggil oleh Gate::authorize('update', $assignment)
     */
    public function update(User $user, Assignment $assignment): bool
    {
        // Cukup cek identitas: Apakah dia Auditor yang ditugaskan?
        return $user->role === UserRole::AUDITOR &&
            $assignment->auditor_id === $user->id;
    }
}
