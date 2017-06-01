<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NormaliseParties extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('parties', function (Blueprint $table) {
			$table->string('rep_name')->after('role');
			$table->dropColumn('email');
			$table->string('acn')->change();
		});

		$this->migrateData();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('parties', function (Blueprint $table) {
			$table->dropColumn('rep_name');
			$table->string('email')->after('fax');
			$table->string('acn')->nullable()->change();
		});
	}

	/**
	 * Turns this:
	 * -----------------------------------------------
	 * | name  | role                     | rep_name |
	 * -----------------------------------------------
	 * | Fred  | Applicant                |          |
	 * | Harry | Applicant Representative |          |
	 * | Joe   | Applicant Representative |          |
	 * -----------------------------------------------
	 *
	 * Into this:
	 * -----------------------------------------------
	 * | name  | role                     | rep_name |
	 * -----------------------------------------------
	 * | Fred  | Applicant                | Harry    |
	 * | Fred  | Applicant                | Joe      |
	 * -----------------------------------------------
	 */
	private function migrateData()
	{
		$application_ids = DB::table('parties')
			->where('role', 'LIKE', '%Representative%')
			->groupBy('application_id')
			->lists('application_id');

		foreach ($application_ids as $application_id) {
			$normals = DB::table('parties')
				->where('application_id', $application_id)
				->where('role', 'NOT LIKE', '%Representative%')
				->get();

			$reps = DB::table('parties')
				->where('application_id', $application_id)
				->where('role', 'LIKE', '%Representative%')
				->get();

			foreach ($reps as $rep) {
				if (strpos($rep->role, 'Legal Representative') === 0) {
					$tmp = explode('Representative', $rep->role);
					$role = trim($tmp[1]);
				} else {
					$tmp = explode('Representative', $rep->role);
					$role = trim($tmp[0]);
				}

				foreach ($normals as $party) {
					if ($party->role != $role) {
						continue;
					}

					if (isset($party->done)) {
						DB::table('parties')->insert([
							'application_id'  => $party->application_id,
							'name'            => $party->name,
							'role'            => $party->role,
							'rep_name'        => $rep->name,
							'address'         => $rep->address,
							'phone'           => $rep->phone,
							'fax'             => $rep->fax,
							'abn'             => $party->abn,
							'acn'             => $party->acn,
							'searchable_name' => $party->searchable_name,
							'created_at'      => $party->created_at,
							'updated_at'      => $party->updated_at,
						]);
					} else {
						DB::table('parties')
							->where('id', $party->id)
							->update([
								'rep_name' => $rep->name,
								'address'  => $rep->address,
								'phone'    => $rep->phone,
								'fax'      => $rep->fax,
							]);

						$party->done = true;
					}
				}

				DB::table('parties')->where('id', $rep->id)->delete();
			}
		}
	}

}
