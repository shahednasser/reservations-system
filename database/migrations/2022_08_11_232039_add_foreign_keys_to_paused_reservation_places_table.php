<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPausedReservationPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('paused_reservation_places', function(Blueprint $table)
		{
			$table->foreign('floor_id', 'FK_FLOORPAUSEDRESPLACES')->references('id')->on('floors')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('paused_reservation_id', 'FK_PAUSEDRESPLACES')->references('id')->on('paused_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('room_id', 'FK_ROOMPAUSEDRESPLACES')->references('id')->on('rooms')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('paused_reservation_places', function(Blueprint $table)
		{
			$table->dropForeign('FK_FLOORPAUSEDRESPLACES');
			$table->dropForeign('FK_PAUSEDRESPLACES');
			$table->dropForeign('FK_ROOMPAUSEDRESPLACES');
		});
	}

}
