<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualReligiousRequirmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_religious_requirments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('manual_reservation_id')->index('FK_MANUALRESRELIG_idx');
			$table->integer('religious_requirment_id')->index('FK_RELIGIOUSRES_idx');
			$table->integer('nb_days')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_religious_requirments');
	}

}
