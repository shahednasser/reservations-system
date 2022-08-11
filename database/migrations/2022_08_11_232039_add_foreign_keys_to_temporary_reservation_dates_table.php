<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTemporaryReservationDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('temporary_reservation_dates', function(Blueprint $table)
		{
			$table->foreign('temporary_reservation_id', 'FK_TEMPRESDATE')->references('id')->on('temporary_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('temporary_reservation_dates', function(Blueprint $table)
		{
			$table->dropForeign('FK_TEMPRESDATE');
		});
	}

}
