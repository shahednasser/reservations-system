<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManualPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('manual_places', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('floor_id')->index('FK_FLOORMAN_idx');
			$table->integer('room_id')->nullable()->index('FK_ROOMMAN_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('manual_places');
	}

}
