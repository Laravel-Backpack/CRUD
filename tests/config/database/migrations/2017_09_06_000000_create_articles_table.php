<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->length(10)->unsigned();
            $table->string('content');
            $table->integer('address_id')->nullable();
            $table->string('metas')->nullable();
            $table->string('tags')->nullable();
            $table->string('extras')->nullable();
            $table->string('cast_metas')->nullable();
            $table->string('cast_tags')->nullable();
            $table->string('cast_extras')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('articles');
    }
}
