<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->comment("liên kết bảng customers");
            $table->string('project_name', 150);
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('task_supervisor_user_id')->comment("liên kết bảng users");
            $table->string('priority_level', 1)->comment("1: Thấp | 2: Bình thường | 3: Cao | 4: Rất cao");
            $table->string('status', 1)->comment("1: Đang chạy Ads | 2: Đang thực hiện | 3:  Chưa triển khai | 4: Đã tạm dừng");
            $table->date('contract_start_date')->comment("ngày bắt đầu hợp đồng");
            $table->date('contract_end_date')->comment("ngày kết thúc hợp đồng");
            $table->timestamps();

            $table->foreign('task_supervisor_user_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
