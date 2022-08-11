<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePausedReservationPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('paused_reservation_places', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('paused_reservation_id')->index('FK_PAUSEDRESPLACES_idx');
			$table->integer('floor_id')->index('FK_FLOORPAUSEDRESPLACES_idx');
			$table->integer('room_id')->nullable()->index('FK_ROOMPAUSEDRESPLACES_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('paused_reservation_places');
	}

}
