<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePausedReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('paused_reservations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('reservation_id')->nullable()->index('FK_PAUSEDRES_idx');
			$table->integer('manual_reservation_id')->nullable()->index('FK_PAUSEMANRES_idx');
			$table->date('from_date');
			$table->date('to_date');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('paused_reservations');
	}

}
