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
        Schema::create('assessment_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_indicator_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['file', 'link'])->default('file'); // Tambahkan kolom ini
            $table->string('title')->nullable(); // Opsional: Judul bukti agar rapi di UI
            $table->text('content'); // Berisi Path File (storage/app/private/...) atau URL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_evidence');
    }
};
