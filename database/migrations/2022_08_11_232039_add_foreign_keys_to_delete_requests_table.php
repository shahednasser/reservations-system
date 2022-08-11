<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToDeleteRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('delete_requests', function(Blueprint $table)
		{
			$table->foreign('reservation_id', 'FK_RESDELETEREQUESTS')->references('id')->on('reservations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('delete_requests', function(Blueprint $table)
		{
			$table->dropForeign('FK_RESDELETEREQUESTS');
		});
	}

}
