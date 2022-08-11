<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLongReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('long_reservations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->dateTime('from_date');
			$table->dateTime('to_date');
			$table->integer('reservation_id')->index('FK_RESERVATIONLONG_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('long_reservations');
	}

}
