<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToReservationsRejectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('reservations_rejections', function(Blueprint $table)
		{
			$table->foreign('reservation_id', 'FK_RESERVATIONREJ')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('reservations_rejections', function(Blueprint $table)
		{
			$table->dropForeign('FK_RESERVATIONREJ');
		});
	}

}
