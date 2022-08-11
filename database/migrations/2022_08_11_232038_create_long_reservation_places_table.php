<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLongReservationPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('long_reservation_places', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('long_reservation_date_id')->index('FK_LONGRESDATEPLACES');
			$table->integer('floor_id')->nullable()->index('FK_FLOORLONGRESPLACE');
			$table->integer('room_id')->nullable()->index('FK_ROOMLONGRESPLACE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('long_reservation_places');
	}

}
