<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpisodeHostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episode_host', function (Blueprint $table) {
            $table->foreignId('episode_id')->references('id')->on('episodes');
            $table->foreignId('host_id')->references('id')->on('hosts');

            $table->unique(['episode_id', 'host_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('episode_host');
    }
}
