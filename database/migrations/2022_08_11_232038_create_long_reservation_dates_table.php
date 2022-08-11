<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLongReservationDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('long_reservation_dates', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('long_reservation_id')->index('FK_LONGRESDATES_idx');
			$table->integer('day_of_week')->comment('0-sunday
1-monday
2-tuesday
3-wednesday
4-thursday
5-friday
6-saturday');
			$table->time('from_time');
			$table->time('to_time');
			$table->text('event', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('long_reservation_dates');
	}

}
