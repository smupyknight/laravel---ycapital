<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchlistEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist_entities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('watchlist_id')->unsigned();
            $table->enum('type',['party_name','abn_acn']);
            $table->text('data');
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
        Schema::drop('watchlist_entities');
    }
}
