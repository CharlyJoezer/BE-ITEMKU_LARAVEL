<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id_product')->on('products');
            
            $table->unsignedBigInteger('shop_id');
            $table->foreign('shop_id')->references('id_shop')->on('shops')->onDelete('cascade');
            
            $table->unsignedBigInteger('buyer_id');
            $table->foreign('buyer_id')->references('id_user')->on('users')->onDelete('cascade');

            $table->string('order_code')->unique();
            $table->integer('amount');
            $table->enum('status_pesanan', ["success", "confirmation", "process", "canceled"])->default('process');
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
        Schema::dropIfExists('orders');
    }
};
