<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEditRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('edit_requests', function(Blueprint $table)
		{
			$table->foreign('new_reservation_id', 'FK_NEWRESEDITREQ')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('reservation_id', 'FK_RESEDITREQ')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('edit_requests', function(Blueprint $table)
		{
			$table->dropForeign('FK_NEWRESEDITREQ');
			$table->dropForeign('FK_RESEDITREQ');
		});
	}

}
