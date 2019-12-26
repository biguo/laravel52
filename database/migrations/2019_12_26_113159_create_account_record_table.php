<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trade_no')->comment('订单号');
            $table->string('title')->comment('消费名目');
            $table->string('mid')->comment('用户id');
            $table->decimal('total_fee')->comment('金额');
            $table->string('country_id')->comment('项目id');
            $table->string('type')->default('微信充值')->comment('充值通道');
            $table->tinyInteger('change')->default('1')->comment('1-增 2-减');
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
        Schema::dropIfExists('account_record');
    }
}
