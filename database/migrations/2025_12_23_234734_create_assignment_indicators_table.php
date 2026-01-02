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
        Schema::create('assignment_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();

            // Data Snapshot (Independen dari Master)
            $table->char('snapshot_code', 10)->index();
            $table->text('snapshot_requirement');
            $table->string('snapshot_template_path')->nullable();

            // Hasil Penilaian & Bukti
            $table->integer('score')->nullable()->index();
            $table->text('auditor_note')->nullable();
            $table->string('evidence_path')->nullable(); // File bukti
            $table->string('evidence_url')->nullable();  // Link bukti (Hybrid)
            $table->text('recommendation')->nullable();

            $table->timestamps();
            $table->index(['assignment_id', 'snapshot_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_indicators');
    }
};
