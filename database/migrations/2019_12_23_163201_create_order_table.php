<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tradeno')->comment('订单号');
            $table->integer('mid')->comment('用户id');
            $table->integer('product_id')->comment('产品id');
            $table->string('title')->comment('成交时的产品名称');
            $table->string('image')->comment('成交时的产品图片');
            $table->decimal('price')->comment('成交价');
            $table->integer('country_id')->comment('项目id');
            $table->tinyInteger('status')->default('1')->comment('订单状态:1：待支付；2：已支付;  4 已取消 ');
            $table->string('paytradeno')->nullable();
            $table->string('responsestr')->nullable();
            $table->dateTime('paytime')->nullable();
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
        Schema::dropIfExists('order');
    }
}
