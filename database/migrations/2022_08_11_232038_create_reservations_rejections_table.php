<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReservationsRejectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reservations_rejections', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('reservation_id')->index('FK_RESERVATIONREJ_idx');
			$table->string('message')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('reservations_rejections');
	}

}
