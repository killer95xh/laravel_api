<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->comment("ID của người dùng");
            $table->string('first_name', 24);
            $table->string('last_name', 16);
            $table->string('user_name', 30)->unique()->comment("Tên đăng nhập");
            $table->string('email', 30)->nullable();
            $table->string('password');
            $table->string('position', 100)->nullable()->comment("Chức vụ");
            $table->string('employment_state', 1)->comment("0: đã nghỉ việc. 1: đang làm việc");
            $table->string('url_avatar', 255)->nullable()->comment("URL của ảnh đại diện");
            $table->string('is_admin', 1)->default("0")->comment("1: admin | 0: user");
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
