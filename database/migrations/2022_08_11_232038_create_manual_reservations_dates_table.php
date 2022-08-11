<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualReservationsDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_reservations_dates', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('manual_reservation_id')->index('FK_MANRESDATE_idx');
			$table->date('date');
			$table->time('from_time');
			$table->time('to_time');
			$table->integer('for_women');
			$table->integer('for_men');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_reservations_dates');
	}

}
