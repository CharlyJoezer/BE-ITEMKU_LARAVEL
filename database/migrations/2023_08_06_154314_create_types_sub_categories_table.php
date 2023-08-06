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
        Schema::create('types_sub_categories', function (Blueprint $table) {
            $table->id('id_type_sub_category');
            
            $table->unsignedBigInteger('sub_category_id');
            $table->foreign('sub_category_id')->references('id_sub_category')->on('sub_categories')->onDelete('cascade');
            
            $table->string('name_type');

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
        Schema::dropIfExists('types_sub_categories');
    }
};
