<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WatchlistNotifications extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watchlist_notifications', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('watchlist_id')->unsigned()->index();
			$table->integer('case_id')->unsigned();
			$table->tinyInteger('is_sent');
			$table->datetime('created_at');
			$table->datetime('updated_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watchlist_notifications');
	}
}
