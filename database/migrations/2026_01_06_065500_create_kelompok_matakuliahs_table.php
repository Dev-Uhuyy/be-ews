<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelompok_matakuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matakuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->string('nama');
            $table->foreignId('dosenpengampu_id')->constrained('dosen')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_matakuliahs');
    }
};
