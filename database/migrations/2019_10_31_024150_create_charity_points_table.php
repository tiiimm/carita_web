<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharityPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charity_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('charity_id');
            $table->string('points');
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
        Schema::dropIfExists('charity_points');
    }
}
