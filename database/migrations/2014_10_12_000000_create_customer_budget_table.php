<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerBudgetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_budget', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->comment("liên kết bảng customers");
            $table->bigInteger('advance_amount')->nullable()->default(0)->comment("số tiền KH đã tạm ứng");
            $table->integer('fee')->nullable()->default(0)->comment("số tiền phí dịch vụ");
            $table->date('fee_date')->nullable()->comment("ngày áp dụng phí");
            $table->integer('default_video_quantity')->nullable()->default(0)->comment("số lượng video khách hàng mặc định");
            $table->integer('additional_video_quantity')->nullable()->default(0)->comment("số lượng video khách hàng đã làm thêm");
            $table->integer('additional_service_cost')->nullable()->default(0)->comment("chi phí dịch vụ phát sinh");
            $table->string('additional_service_note')->nullable()->default(0)->comment("note dịch vụ phát sinh");
            $table->integer('facebook_service_amount')->nullable()->default(0)->comment("số tiền đã chi tiêu cho facebook");
            $table->integer('tiktok_service_amount')->nullable()->default(0)->comment("số tiền đã chi tiêu cho tiktok");
            $table->integer('google_service_amount')->nullable()->default(0)->comment("số tiền đã chi tiêu cho google");
            $table->integer('zalo_service_amount')->nullable()->default(0)->comment("số tiền đã chi tiêu cho zalo");
            $table->date('last_update_date')->nullable()->comment("UPDATE (tính đến hết ngày)");
            $table->integer('budget_per_day')->nullable()->default(0)->comment("ngân sách / ngày");
            $table->string('status', 1)->default("1")->comment("0: Tạm dừng | 1: Đang chạy");
            $table->unsignedBigInteger('ads_user_id')->nullable()->comment("liên kết bảng users");
            $table->unsignedBigInteger('sale_user_id')->nullable()->comment("liên kết bảng users");
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('ads_user_id')->references('id')->on('users');
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
        Schema::dropIfExists('customer_budget');
    }
}
