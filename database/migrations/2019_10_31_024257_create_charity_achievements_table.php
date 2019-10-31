<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharityAchievementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charity_achievements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('charity_id');
            $table->string('title');
            $table->string('description');
            $table->string('photo');
            $table->date('held_on');
            $table->timestamps();

            $table->foreign('charity_id')
            ->references('id')
            ->on('charities')
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
        Schema::dropIfExists('charity_achievements');
    }
}
