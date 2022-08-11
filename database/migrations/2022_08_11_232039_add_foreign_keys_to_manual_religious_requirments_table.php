<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManualReligiousRequirmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('manual_religious_requirments', function(Blueprint $table)
		{
			$table->foreign('manual_reservation_id', 'FK_MANUALRESRELIG')->references('id')->on('manual_reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('religious_requirment_id', 'FK_RELIGIOUSRES')->references('id')->on('religious_requirments')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('manual_religious_requirments', function(Blueprint $table)
		{
			$table->dropForeign('FK_MANUALRESRELIG');
			$table->dropForeign('FK_RELIGIOUSRES');
		});
	}

}
