<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reservations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id')->nullable()->index('FK_USERRESERVATIONS_idx');
			$table->string('committee');
			$table->string('event_name');
			$table->text('notes', 65535)->nullable();
			$table->text('supervisors', 65535)->nullable();
			$table->timestamp('date_created')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->integer('is_approved')->default(0)->comment('0: not approved, 1: approved, -1: declined, -2: revoked');
			$table->text('message', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('reservations');
	}

}
