<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubTaskAssigneesUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_task_assignees_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_task_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('sub_task_id')->references('id')->on('sub_task');
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
        Schema::dropIfExists('sub_task_assignees_user');
    }
}
