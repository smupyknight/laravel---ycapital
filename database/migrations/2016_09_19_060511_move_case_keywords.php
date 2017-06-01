<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveCaseKeywords extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watchlist_queue', function(Blueprint $table) {
			$table->dropColumn('keywords');
		});

		Schema::table('cases', function(Blueprint $table) {
			$table->text('keywords')->after('url');
		});

		DB::statement("ALTER TABLE cases ADD FULLTEXT INDEX keywords (keywords)");

		Schema::table('watchlist_notifications', function(Blueprint $table) {
			$table->renameColumn('watchlist_id', 'watchlist_entity_id');
			$table->decimal('relevancy', 6, 4)->unsigned()->after('case_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watchlist_queue', function(Blueprint $table) {
			$table->text('keywords')->after('id');
		});

		DB::statement("ALTER TABLE watchlist_queue ADD FULLTEXT INDEX keywords (keywords)");

		Schema::table('cases', function(Blueprint $table) {
			$table->dropColumn('keywords');
		});

		Schema::table('watchlist_notifications', function(Blueprint $table) {
			$table->renameColumn('watchlist_entity_id', 'watchlist_id');
			$table->dropColumn('relevancy');
		});
	}
}
