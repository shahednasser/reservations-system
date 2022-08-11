<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_reservations', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('full_name');
			$table->string('organization')->nullable();
			$table->string('mobile_phone')->nullable();
			$table->string('home_phone')->nullable();
			$table->string('event_name')->nullable();
			$table->string('event_type');
			$table->date('date_created');
			$table->integer('manual_place_id')->nullable()->index('FK_MANUALPLACERES_idx');
			$table->decimal('discount', 10)->default(0.00);
			$table->integer('is_approved')->default(1);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_reservations');
	}

}
