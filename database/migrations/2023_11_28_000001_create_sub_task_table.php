<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_task', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->string('sub_task_name', 150);
            $table->string('description')->nullable();
            $table->date('deadline_start_date')->nullable();
            $table->date('deadline_start_end')->nullable();
            $table->string('status', 1)->deafault("1")->nullable();
            $table->integer('progress_completed')->nullable();
            $table->integer('progress_target')->nullable();
            $table->integer('progress_target')->nullable();
            $table->string('progress_type', 50)->nullable();
            $table->string('priority_level', 1)->deafault("2")->nullable();
            $table->unsignedBigInteger('leader_user_id');
            $table->timestamps();

            $table->foreign('leader_user_id')->references('id')->on('users');
            $table->foreign('task_id')->references('id')->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_task');
    }
}
