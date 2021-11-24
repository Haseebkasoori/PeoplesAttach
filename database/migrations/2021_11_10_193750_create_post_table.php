<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('post', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->unique();
            $table->bigInteger('user_id')->unsigned();
            $table->string('text')->nullable();
            $table->string('attachment')->nullable();
            $table->enum('visibility', ['public', 'private']);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('post', function($table) {
           $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post');
    }
}
