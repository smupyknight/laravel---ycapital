<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexCasesUpdatedAt extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cases', function(Blueprint $table) {
			$table->index('updated_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cases', function(Blueprint $table) {
			$table->dropIndex('cases_updated_at_index');
		});
	}
}
