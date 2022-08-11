<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualReservationsDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_reservations_dates', function(Blueprint $table)
		{
			$table->foreign('manual_reservation_id', 'FK_MANRESDATE')->references('id')->on('manual_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_reservations_dates', function(Blueprint $table)
		{
			$table->dropForeign('FK_MANRESDATE');
		});
	}

}
