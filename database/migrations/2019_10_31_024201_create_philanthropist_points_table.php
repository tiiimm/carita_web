<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhilanthropistPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('philanthropist_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('philanthropist_id');
            $table->string('points');
            $table->timestamps();

            $table->foreign('philanthropist_id')
            ->references('id')
            ->on('philanthropists')
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
        Schema::dropIfExists('philanthropist_points');
    }
}
