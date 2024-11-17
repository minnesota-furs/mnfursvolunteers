<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigratePrimaryDeptToDepartmentUser extends Migration
{
    public function up()
    {
        // Ensure department_user table exists
        if (Schema::hasTable('department_user')) {
            // Fetch all users with a primary_dept_id
            $users = DB::table('users')->whereNotNull('primary_dept_id')->get();

            foreach ($users as $user) {
                // Insert the relationship into the department_user pivot table
                DB::table('department_user')->insert([
                    'user_id' => $user->id,
                    'department_id' => $user->primary_dept_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        // Reverse the migration: remove entries in department_user that match the migrated data
        DB::table('department_user')
            ->whereIn('user_id', function ($query) {
                $query->select('id')->from('users')->whereNotNull('primary_dept_id');
            })
            ->delete();
    }
}
