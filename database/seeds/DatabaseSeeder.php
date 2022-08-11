<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        //create super admin
        $admin = new User([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'is_admin' => 1,
            'name' => 'Admin'
        ]);
        $admin->save();
    }
}
