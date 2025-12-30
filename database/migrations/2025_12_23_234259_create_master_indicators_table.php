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
        Schema::create('master_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_standard_id')->constrained()->cascadeOnDelete();
            $table->char('code', 10);
            $table->text('requirement')->comment('Deskripsi persyaratan');
            $table->string('template_path')->nullable()->comment('Template acuan dari Admin');
            $table->boolean('is_evidence_required')->default(true)->comment('Apakah bukti diperlukan?');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['master_standard_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_indicators');
    }
};
