<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualPlaceRequirmentsDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_place_requirments_dates', function(Blueprint $table)
		{
			$table->foreign('manual_place_requirment_id', 'FK_MANPLACEREQDATE')->references('id')->on('manual_place_requirments')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('manual_reservations_date_id', 'FK_MANUALREQDATE')->references('id')->on('manual_reservations_dates')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_place_requirments_dates', function(Blueprint $table)
		{
			$table->dropForeign('FK_MANPLACEREQDATE');
			$table->dropForeign('FK_MANUALREQDATE');
		});
	}

}
