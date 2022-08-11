<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEditedReservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('edited_reservations', function(Blueprint $table)
		{
			$table->foreign('reservation_id', 'FK_RESEDITED')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('edited_reservations', function(Blueprint $table)
		{
			$table->dropForeign('FK_RESEDITED');
		});
	}

}
