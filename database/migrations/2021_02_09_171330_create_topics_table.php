<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_episode_id')->references('id')->on('podcast_episodes');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('name');
            $table->timestamp('start');
            $table->timestamp('end');
            $table->boolean('ad');
            $table->boolean('community_contribution');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topics');
    }
}
