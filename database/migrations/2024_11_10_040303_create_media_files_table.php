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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string("title");    // ユーザーが決められる任意の名前
            $table->string('base_name', 255);   // ディレクトリ名、またはファイル名とその拡張子
            $table->string('path', 255);   // ファイルまたはフォルダパス
            $table->unsignedBigInteger('data_size');    // ファイルまたはフォルダの容量
            $table->morphs('mediable');
            $table->string("preview_image_path")->nullable();
            // 2038年問題の為、datetimeを選択
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
