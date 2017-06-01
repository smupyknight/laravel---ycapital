<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Watchlists extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watchlist_queue', function(Blueprint $table) {
			$table->integer('id')->unsigned()->primary();
			$table->text('keywords');
			$table->datetime('created_at');
			$table->datetime('updated_at');
		});

		DB::statement("ALTER TABLE watchlist_queue ADD FULLTEXT INDEX keywords (keywords)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watchlist_queue');
	}
}
