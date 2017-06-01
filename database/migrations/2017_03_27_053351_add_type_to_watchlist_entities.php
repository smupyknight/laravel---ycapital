<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToWatchlistEntities extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watchlist_entities', function(Blueprint $table) {
			$table->enum('type', ['Company', 'Individual'])->after('watchlist_id');
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
			$table->dropColumn('type');
		});
	}

}
