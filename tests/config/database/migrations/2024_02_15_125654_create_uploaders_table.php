<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaders', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('upload')->nullable();
            $table->string('image')->nullable();
            $table->json('upload_multiple')->nullable();
            $table->json('dropzone')->nullable();
            $table->json('easymde')->nullable();
            $table->json('summernote')->nullable();
            $table->json('repeatable')->nullable();
            $table->json('extras')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaders');
    }
};
