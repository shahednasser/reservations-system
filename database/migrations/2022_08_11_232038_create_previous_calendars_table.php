<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePreviousCalendarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('previous_calendars', function(Blueprint $table)
		{
			$table->integer('calendar_id', true);
			$table->date('date');
			$table->text('data', 65535);
			$table->integer('is_weekly')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('previous_calendars');
	}

}
