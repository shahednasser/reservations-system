<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTemporaryReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('temporary_reservations', function(Blueprint $table)
		{
			$table->foreign('reservation_id', 'FK_RESERVATIONTEMP')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('temporary_reservations', function(Blueprint $table)
		{
			$table->dropForeign('FK_RESERVATIONTEMP');
		});
	}

}
