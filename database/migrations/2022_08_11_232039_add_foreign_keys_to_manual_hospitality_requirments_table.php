<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualHospitalityRequirmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_hospitality_requirments', function(Blueprint $table)
		{
			$table->foreign('hospitality_requirment_id', 'FK_HOSPIREQ')->references('id')->on('hospitality_requirments')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('manual_reservation_id', 'FK_MANUALRESHOSPI')->references('id')->on('manual_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_hospitality_requirments', function(Blueprint $table)
		{
			$table->dropForeign('FK_HOSPIREQ');
			$table->dropForeign('FK_MANUALRESHOSPI');
		});
	}

}
