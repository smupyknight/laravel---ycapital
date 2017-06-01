<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WatchlistAbns extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watchlist_entities', function(Blueprint $table) {
			$table->string('party_name', 255)->after('watchlist_id');
			$table->string('abn', 11)->nullable()->after('party_name');
			$table->dropColumn('type');
			$table->dropColumn('data');
		});

		Schema::table('watchlist_notifications', function(Blueprint $table) {
			$table->decimal('name_relevancy', 6, 4)->unsigned()->after('case_id');
			$table->decimal('abn_relevancy', 6, 4)->unsigned()->after('name_relevancy');
			$table->dropColumn('relevancy');
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
			$table->enum('type', ['party_name','abn_acn'])->after('watchlist_id');
			$table->text('data')->after('type');
			$table->dropColumn('party_name');
			$table->dropColumn('abn');
		});

		Schema::table('watchlist_notifications', function(Blueprint $table) {
			$table->dropColumn('name_relevancy');
			$table->dropColumn('abn_relevancy');
			$table->decimal('relevancy', 6, 4)->unsigned()->after('case_id');
		});
	}
}
