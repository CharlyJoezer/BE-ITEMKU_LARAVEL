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
        Schema::disableForeignKeyConstraints();
        Schema::create('products', function (Blueprint $table) {
            $table->id('id_product');

            $table->unsignedBigInteger('sub_category_id');
            $table->foreign('sub_category_id')->default(0)->references('id_sub_category')->on('sub_categories')->onDelete('restrict')->onUpdate('restrict');

            $table->unsignedBigInteger('shop_id');
            $table->foreign('shop_id')->references('id_shop')->on('shops')->onDelete('cascade')->onUpdate('restrict');

            $table->unsignedBigInteger('type_sub_category_id');
            $table->foreign('type_sub_category_id')->references('id_type_sub_category')->on('types_sub_categories')->onDelete('restrict')->onUpdate('restrict');

            $table->string('name_product');
            $table->string('desc_product');
            $table->integer('price_product');
            $table->string('slug_product');
            $table->integer('stock_product')->default(0);
            $table->string('path_image_product');
            $table->integer('min_buy');
            $table->integer('success_transaction')->default(0);
            $table->string('duration_transaction')->default(null);

            $table->timestamps();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
