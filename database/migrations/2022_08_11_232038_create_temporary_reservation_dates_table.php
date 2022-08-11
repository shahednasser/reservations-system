<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemporaryReservationDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('temporary_reservation_dates', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->date('date');
			$table->time('from_time');
			$table->time('to_time');
			$table->integer('temporary_reservation_id')->index('FK_TEMPRESDATE_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('temporary_reservation_dates');
	}

}
