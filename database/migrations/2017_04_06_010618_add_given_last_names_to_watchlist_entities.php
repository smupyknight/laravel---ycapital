<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGivenLastNamesToWatchlistEntities extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watchlist_entities', function($table) {
		    $table->string('party_given_names')->after('party_name');
		    $table->string('party_last_name')->after('party_given_names');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watchlist_entities', function($table) {
		    $table->dropColumn('party_given_names');
		    $table->dropColumn('party_last_name');
		});
	}

}
