<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_reservations', function(Blueprint $table)
		{
			$table->foreign('manual_place_id', 'FK_MANUALPLACERES')->references('id')->on('manual_places')->onUpdate('CASCADE')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_reservations', function(Blueprint $table)
		{
			$table->dropForeign('FK_MANUALPLACERES');
		});
	}

}
