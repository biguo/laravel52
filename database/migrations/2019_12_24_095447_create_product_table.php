<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country_id')->comment('村id');
            $table->string('title')->comment('名称');
            $table->decimal('price')->comment('价格');
            $table->string('pic')->comment('图片');
            $table->tinyInteger('status')->comment('0 未上线  1已上线');
            $table->text('content')->comment('内容');
            $table->tinyInteger('sort')->comment('排序');
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
        Schema::dropIfExists('product');
    }
}
