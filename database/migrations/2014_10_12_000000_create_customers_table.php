<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_user_id')->comment("liên kết bảng users");
            $table->string('customer_name', 150);
            $table->string('phone_number', 20);
            $table->string('company_name', 150)->nullable();
            $table->string('customer_source', 1)->comment("1: Cá nhân | 2: Công ty");
            $table->string('email', 30)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('deal_status', 1)->deafault("0")->comment("0: Chưa chốt | 1: Đã chốt | 2: Đã hủy");
            $table->text('care_progress')->comment("tiến độ chăm sóc khách hàng");
            $table->date('callback_due_date')->comment("ngày gọi lại cho khách hàng");
            $table->timestamps();

            $table->foreign('sale_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
