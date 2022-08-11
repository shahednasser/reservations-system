<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLongReservationDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('long_reservation_dates', function(Blueprint $table)
		{
			$table->foreign('long_reservation_id', 'FK_LONGRESDATES')->references('id')->on('long_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('long_reservation_dates', function(Blueprint $table)
		{
			$table->dropForeign('FK_LONGRESDATES');
		});
	}

}
