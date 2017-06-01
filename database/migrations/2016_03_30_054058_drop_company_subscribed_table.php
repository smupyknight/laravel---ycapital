<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCompanySubscribedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('company_subscribed');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('company_subscribed', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }
}
