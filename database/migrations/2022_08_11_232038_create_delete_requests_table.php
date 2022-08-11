<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeleteRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('delete_requests', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('reservation_id')->index('FK_RESDELETEREQUESTS_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('delete_requests');
	}

}
