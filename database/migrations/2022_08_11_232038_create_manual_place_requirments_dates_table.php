<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualPlaceRequirmentsDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_place_requirments_dates', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('manual_reservations_date_id')->index('FK_MANUALREQDATE_idx');
			$table->integer('manual_place_requirment_id')->index('FK_PLACEREQDATES_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_place_requirments_dates');
	}

}
