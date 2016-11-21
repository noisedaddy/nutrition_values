<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserimageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userimage', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned(); // user who created article
            $table->string('image_name');
            $table->string('image_size');
            $table->string('image_type');
            $table->string('image_path');
            $table->timestamps();

            $table->foreign('user_id')//id field on users tab;e
                ->references('id')//references id field on users table
                ->on('users')//users table
                ->onDelete('cascade');//cascade down and delete all articles


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('userimage');
    }
}
