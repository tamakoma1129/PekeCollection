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
        Schema::table('audios', function (Blueprint $table) {
            $table->string('waveform_path')->nullable()->after('preview_audio_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audios', function (Blueprint $table) {
            $table->dropColumn('waveform_path');
        });
    }
};
