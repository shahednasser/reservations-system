<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEditRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('edit_requests', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('reservation_id')->index('FK_RESEDITREQ_idx');
			$table->integer('new_reservation_id')->index('FK_NEWRESEDITREQ_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('edit_requests');
	}

}
