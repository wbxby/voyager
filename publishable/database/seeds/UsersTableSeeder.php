<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() == 0) {
            $role = Role::where('name', 'admin')->firstOrFail();

            User::create([
                'name'           => 'ZmitroC',
                'email'          => 'i@zmitroc.by',
                'password'       => '$2a$06$/QoImFpjezQxG3w8jWeN/e.Y6jmqso9g76P5BRX77a1d/0RUtKrbG',
                'remember_token' => str_random(60),
                'role_id'        => 1,
            ]);

            User::create([
                'name'           => 'Admin',
                'email'          => 'admin@admin.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $role->id,
            ]);
        }
    }
}
