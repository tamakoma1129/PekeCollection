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
        Schema::create('manga_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->string('file_name');
            $table->string('path');
            $table->string('lite_path');
            $table->string('file_extension', 10);
            $table->integer("width");
            $table->integer("height");
            $table->unsignedBigInteger('file_size');
            // 2038年問題の為、datetimeを選択
            $table->dateTime("created_at")->nullable();
            $table->dateTime("updated_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manga_pages');
    }
};
