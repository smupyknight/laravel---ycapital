<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchlistSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('watchlist_id')->unsigned();
            $table->text('name');
            $table->text('email');
            $table->integer('created_by');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->foreign('watchlist_id')->references('id')->on('watchlists');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('watchlist_subscribers');
    }
}
