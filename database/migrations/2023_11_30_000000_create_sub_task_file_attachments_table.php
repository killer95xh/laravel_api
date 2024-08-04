<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubTaskFileAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_task_file_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_task_id');
            $table->string('path', 255);
            $table->timestamps();

            $table->foreign('sub_task_id')->references('id')->on('sub_task');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_task_file_attachments');
    }
}
