<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToLongReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('long_reservations', function(Blueprint $table)
		{
			$table->foreign('reservation_id', 'FK_RESERVATIONLONGRES')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('long_reservations', function(Blueprint $table)
		{
			$table->dropForeign('FK_RESERVATIONLONGRES');
		});
	}

}
