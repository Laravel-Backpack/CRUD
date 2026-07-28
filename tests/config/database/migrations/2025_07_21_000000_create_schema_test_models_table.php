<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('schema_test_models', function ($table) {
            $table->id();
            // column for type detection
            $table->string('name');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('age')->default(0);
            $table->boolean('active')->default(true);
            $table->decimal('price', 8, 2)->nullable()->default(null);
            $table->json('metadata')->nullable();
            $table->date('published_at')->nullable();
            // column with explicit non-null default string value
            $table->string('status')->default('draft');
            // nullable column with no default
            $table->string('notes')->nullable();
            // non-nullable column with no default
            $table->string('sku');
            // unique index column (for identifiable attribute tests)
            $table->string('email')->unique();
            // indexed foreign-key-like column (should be skipped by identifiable)
            $table->unsignedBigInteger('user_id')->index();
            // column to test default value edge cases
            $table->string('code')->nullable()->default('ABC');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schema_test_models');
    }
};
