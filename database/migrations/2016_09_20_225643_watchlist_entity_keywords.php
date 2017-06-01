<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WatchlistEntityKeywords extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watchlist_entities', function(Blueprint $table) {
			$table->text('keywords')->after('data');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watchlist_entities', function(Blueprint $table) {
			$table->dropColumn('keywords');
		});
	}
}
