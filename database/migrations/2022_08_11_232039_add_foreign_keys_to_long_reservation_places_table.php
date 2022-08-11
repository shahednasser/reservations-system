<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLongReservationPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('long_reservation_places', function(Blueprint $table)
		{
			$table->foreign('floor_id', 'FK_FLOORLONGRESPLACE')->references('id')->on('floors')->onUpdate('SET NULL')->onDelete('SET NULL');
			$table->foreign('long_reservation_date_id', 'FK_LONGRESDATEPLACES')->references('id')->on('long_reservation_dates')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('room_id', 'FK_ROOMLONGRESPLACE')->references('id')->on('rooms')->onUpdate('SET NULL')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('long_reservation_places', function(Blueprint $table)
		{
			$table->dropForeign('FK_FLOORLONGRESPLACE');
			$table->dropForeign('FK_LONGRESDATEPLACES');
			$table->dropForeign('FK_ROOMLONGRESPLACE');
		});
	}

}
