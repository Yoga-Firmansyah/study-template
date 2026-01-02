<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained();
            $table->foreignId('prodi_id')->constrained();
            $table->foreignId('auditor_id')->constrained('users'); // Single Auditor
            $table->foreignId('master_standard_id')->constrained();

            $table->string('current_stage', 25)
                ->default('doc_audit')
                ->index();
            $table->text('summary_note')->nullable(); // Catatan ringkasan tahap aktif
            $table->string('overall_rating')->nullable(); // Penilaian global tahap aktif
            $table->dateTime('completed_at')->nullable(); // Waktu selesai jika finished

            $table->timestamps();
            $table->softDeletes();
            $table->index(['period_id', 'prodi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
