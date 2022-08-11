<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualPlaceRequirmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_place_requirments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('nb_days');
			$table->integer('manual_reservation_id')->index('FK_MANRESREQ_idx');
			$table->integer('place_requirment_id')->index('FK_MANPLACEREQ_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_place_requirments');
	}

}
