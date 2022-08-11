<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualPlacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_places', function(Blueprint $table)
		{
			$table->foreign('floor_id', 'FK_FLOORMAN')->references('id')->on('floors')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('room_id', 'FK_ROOMMAN')->references('id')->on('rooms')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_places', function(Blueprint $table)
		{
			$table->dropForeign('FK_FLOORMAN');
			$table->dropForeign('FK_ROOMMAN');
		});
	}

}
