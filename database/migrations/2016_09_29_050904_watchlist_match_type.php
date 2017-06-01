<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WatchlistMatchType extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watchlist_notifications', function(Blueprint $table) {
			$table->enum('match_type', ['exact','contains','similar'])->after('case_id');
			$table->decimal('relevancy', 6, 4)->unsigned()->after('match_type');
			$table->dropColumn('name_relevancy');
			$table->dropColumn('abn_relevancy');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watchlist_notifications', function(Blueprint $table) {
			$table->dropColumn('match_type');
			$table->dropColumn('relevancy');
			$table->decimal('name_relevancy', 6, 4)->unsigned()->after('case_id');
			$table->decimal('abn_relevancy', 6, 4)->unsigned()->after('name_relevancy');
		});
	}
}
