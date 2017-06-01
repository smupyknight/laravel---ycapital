<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexes extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('filters', function(Blueprint $table) {
			$table->integer('user_id')->unsigned()->change();
			$table->index('user_id');
		});

		Schema::table('settings', function(Blueprint $table) {
			$table->index('field');
		});

		Schema::table('states_subscribed', function(Blueprint $table) {
			$table->integer('user_id')->unsigned()->change();
			$table->index('user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('filters', function(Blueprint $table) {
			$table->integer('user_id')->change();
			$table->dropIndex('filters_user_id_index');
		});

		Schema::table('settings', function(Blueprint $table) {
			$table->dropIndex('settings_field_index');
		});

		Schema::table('states_subscribed', function(Blueprint $table) {
			$table->integer('user_id')->change();
			$table->dropIndex('states_subscribed_user_id_index');
		});
	}
}
