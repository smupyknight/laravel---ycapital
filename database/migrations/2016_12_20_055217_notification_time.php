<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotificationTime extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cases', function (Blueprint $table) {
			$table->datetime('notification_time')->after('url');
		});

		DB::statement('
			UPDATE cases c
			INNER JOIN (
				SELECT case_id, MIN(date_filed) as date
				FROM applications
				GROUP BY case_id
			) AS a ON a.case_id = c.id
			SET c.notification_time = COALESCE(a.date, c.created_at)
		');

		Schema::table('cases', function (Blueprint $table) {
			$table->index('notification_time');
			$table->dropIndex('cases_updated_at_index');
		});

		DB::statement('ALTER TABLE cases CHANGE created_at created_at DATETIME NOT NULL');
		DB::statement('ALTER TABLE cases CHANGE updated_at updated_at DATETIME NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cases', function (Blueprint $table) {
			$table->dropColumn('notification_time');
		});

		DB::statement('ALTER TABLE cases CHANGE created_at created_at TIMESTAMP NOT NULL');
		DB::statement('ALTER TABLE cases CHANGE updated_at updated_at TIMESTAMP NOT NULL');

		Schema::table('cases', function (Blueprint $table) {
			$table->index('updated_at');
		});
	}

}
