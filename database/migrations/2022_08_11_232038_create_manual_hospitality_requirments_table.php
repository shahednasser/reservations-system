<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualHospitalityRequirmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_hospitality_requirments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('manual_reservation_id')->index('FK_MANUALRESHOSPI_idx');
			$table->integer('hospitality_requirment_id')->index('FK_HOSPIREQ_idx');
			$table->integer('nb_days')->default(0);
			$table->string('additional_name')->nullable();
			$table->float('additional_price', 10, 0)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_hospitality_requirments');
	}

}
