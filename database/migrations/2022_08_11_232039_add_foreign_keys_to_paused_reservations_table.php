<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPausedReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('paused_reservations', function(Blueprint $table)
		{
			$table->foreign('reservation_id', 'FK_PAUSEDRES')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('manual_reservation_id', 'FK_PAUSEMANRES')->references('id')->on('manual_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('paused_reservations', function(Blueprint $table)
		{
			$table->dropForeign('FK_PAUSEDRES');
			$table->dropForeign('FK_PAUSEMANRES');
		});
	}

}
