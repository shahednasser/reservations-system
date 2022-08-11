<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemporaryReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('temporary_reservations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->text('equipment_needed_1', 65535)->nullable();
			$table->integer('reservation_id')->index('FK_RESERVATIONTEMP_idx');
			$table->text('equipment_needed_2', 65535)->nullable();
			$table->text('equipment_needed_3', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('temporary_reservations');
	}

}
