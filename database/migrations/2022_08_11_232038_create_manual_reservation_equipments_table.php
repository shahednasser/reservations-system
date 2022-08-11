<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualReservationEquipmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_reservation_equipments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('manual_reservation_id')->index('FK_MANUALRESEQ_idx');
			$table->integer('equipment_id')->index('FK_EQUIPMANURES_idx');
			$table->integer('number');
			$table->integer('day_nb')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_reservation_equipments');
	}

}
