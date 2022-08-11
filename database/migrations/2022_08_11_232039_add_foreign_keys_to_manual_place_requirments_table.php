<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualPlaceRequirmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_place_requirments', function(Blueprint $table)
		{
			$table->foreign('place_requirment_id', 'FK_MANPLACEREQ')->references('id')->on('place_requirments')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('manual_reservation_id', 'FK_MANRESREQ')->references('id')->on('manual_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_place_requirments', function(Blueprint $table)
		{
			$table->dropForeign('FK_MANPLACEREQ');
			$table->dropForeign('FK_MANRESREQ');
		});
	}

}
