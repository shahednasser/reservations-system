<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemporaryReservationPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('temporary_reservation_places', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('temporary_reservation_id')->index('FK_RESERVATIONPLACE_idx');
			$table->integer('floor_id')->nullable()->index('FK_ROOMRESERVATIONPLACE_idx');
			$table->integer('room_id')->nullable()->index('FK_ROOMRESERVATION_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('temporary_reservation_places');
	}

}
