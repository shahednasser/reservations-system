<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualReservationEquipmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_reservation_equipments', function(Blueprint $table)
		{
			$table->foreign('equipment_id', 'FK_EQUIPMANURES')->references('id')->on('equipments')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('manual_reservation_id', 'FK_MANUALRESEQ')->references('id')->on('manual_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_reservation_equipments', function(Blueprint $table)
		{
			$table->dropForeign('FK_EQUIPMANURES');
			$table->dropForeign('FK_MANUALRESEQ');
		});
	}

}
