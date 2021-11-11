<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('idExam');
            $table->json('fileUrl')->nullable();
            $table->string('email');
            $table->boolean('submitted');
            $table->integer('point')->nullable();
            $table->longText('description')->nullable();
            $table->timestamp('thoigiannop')->nullable();
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
        Schema::dropIfExists('list_exercises');
    }
}
