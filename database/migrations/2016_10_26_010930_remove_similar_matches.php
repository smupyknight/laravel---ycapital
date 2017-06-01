<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSimilarMatches extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("DELETE FROM watchlist_notifications WHERE match_type = 'similar'");
		DB::statement("ALTER TABLE watchlist_notifications MODIFY match_type ENUM('exact','contains'), DROP relevancy");

		Schema::table('watchlist_entities', function(Blueprint $table) {
			$table->dropColumn('keywords');
		});

		Schema::table('cases', function(Blueprint $table) {
			$table->dropColumn('keywords');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE watchlist_notifications MODIFY match_type ENUM('exact','contains','similar'), ADD relevancy DECIMAL(6,4) UNSIGNED NOT NULL AFTER match_type");

		Schema::table('watchlist_entities', function(Blueprint $table) {
			$table->text('keywords')->after('abn');
		});

		DB::statement("ALTER TABLE watchlist_entities ADD FULLTEXT INDEX keywords (keywords)");

		Schema::table('cases', function(Blueprint $table) {
			$table->text('keywords')->after('url');
		});

		DB::statement("ALTER TABLE cases ADD FULLTEXT INDEX keywords (keywords)");
	}
}
