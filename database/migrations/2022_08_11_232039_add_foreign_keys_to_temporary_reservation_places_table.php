<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTemporaryReservationPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('temporary_reservation_places', function(Blueprint $table)
		{
			$table->foreign('floor_id', 'FK_FLOORRESERVATION')->references('id')->on('floors')->onUpdate('CASCADE')->onDelete('SET NULL');
			$table->foreign('temporary_reservation_id', 'FK_RESERVATIONPLACE')->references('id')->on('temporary_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('room_id', 'FK_ROOMRESERVATIONS')->references('id')->on('rooms')->onUpdate('CASCADE')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('temporary_reservation_places', function(Blueprint $table)
		{
			$table->dropForeign('FK_FLOORRESERVATION');
			$table->dropForeign('FK_RESERVATIONPLACE');
			$table->dropForeign('FK_ROOMRESERVATIONS');
		});
	}

}
