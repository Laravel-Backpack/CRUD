<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Translatable fields stored as JSON
            // Each locale is a key: {"en": "value", "fr": "value"}
            $table->json('name');
            $table->json('description')->nullable();

            // Regular fields
            $table->string('slug')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for better search performance
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
