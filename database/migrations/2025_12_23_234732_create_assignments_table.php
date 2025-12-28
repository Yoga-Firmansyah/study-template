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

            $table->enum('current_stage', ['doc_audit', 'field_audit', 'finding', 'reporting', 'rtm_rtl'])->default('doc_audit');
            $table->text('summary_note')->nullable(); // Catatan ringkasan tahap aktif
            $table->string('overall_rating')->nullable(); // Penilaian global tahap aktif

            $table->timestamps();
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
