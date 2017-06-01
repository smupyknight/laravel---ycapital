<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixIndexes extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('hearings', function(Blueprint $table) {
			$table->integer('application_id')->unsigned()->change();
			$table->index('application_id');
		});

		Schema::table('parties', function(Blueprint $table) {
			$table->integer('application_id')->unsigned()->change();
			$table->index('application_id');
		});

		Schema::table('documents', function(Blueprint $table) {
			$table->integer('application_id')->unsigned()->change();
			$table->index('application_id');
		});

		Schema::table('materials', function(Blueprint $table) {
			$table->integer('application_id')->unsigned()->change();
			$table->index('application_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('hearings', function(Blueprint $table) {
			$table->integer('application_id')->change();
			$table->dropIndex('hearings_application_id_index');
		});

		Schema::table('parties', function(Blueprint $table) {
			$table->integer('application_id')->change();
			$table->dropIndex('parties_application_id_index');
		});

		Schema::table('documents', function(Blueprint $table) {
			$table->integer('application_id')->change();
			$table->dropIndex('documents_application_id_index');
		});

		Schema::table('materials', function(Blueprint $table) {
			$table->integer('application_id')->change();
			$table->dropIndex('materials_application_id_index');
		});
	}
}
