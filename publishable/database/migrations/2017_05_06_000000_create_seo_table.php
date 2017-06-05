<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSeoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing categories
        Schema::create('seo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table');
            $table->integer('item_id')->unsigned();
            $table->string('title');
            $table->text('description');
            $table->text('keywords');
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
        Schema::drop('seo');
    }
}
