<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTracking extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('updates', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('case_id')->unsigned();
			$table->integer('entity_id')->unsigned();
			$table->enum('entity_type', ['case','application','hearing','document','material','party']);
			$table->enum('action_type', ['create','edit','delete']);
			$table->datetime('created_at');
			$table->datetime('updated_at');
		});

		Schema::create('update_fields', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('update_id')->unsigned();
			$table->string('name');
			$table->string('old_value');
			$table->string('new_value');
			$table->datetime('created_at');
			$table->datetime('updated_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('updates');
		Schema::drop('update_fields');
	}

}
