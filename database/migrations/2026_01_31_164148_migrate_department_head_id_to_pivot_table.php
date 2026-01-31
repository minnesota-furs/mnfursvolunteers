<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing department_head_id values to the department_head pivot table
        $departments = DB::table('departments')
            ->whereNotNull('department_head_id')
            ->get();

        foreach ($departments as $department) {
            // Check if the relationship doesn't already exist
            $exists = DB::table('department_head')
                ->where('department_id', $department->id)
                ->where('user_id', $department->department_head_id)
                ->exists();

            if (!$exists) {
                DB::table('department_head')->insert([
                    'department_id' => $department->id,
                    'user_id' => $department->department_head_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Optional: Drop the old department_head_id column
        // Uncomment the following lines if you want to remove the old column
        // Schema::table('departments', function (Blueprint $table) {
        //     $table->dropForeign(['department_head_id']);
        //     $table->dropColumn('department_head_id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the old column if it was dropped
        // Schema::table('departments', function (Blueprint $table) {
        //     $table->foreignId('department_head_id')->nullable()->constrained('users');
        // });

        // Move first head from pivot table back to department_head_id
        $departments = DB::table('departments')->get();

        foreach ($departments as $department) {
            $firstHead = DB::table('department_head')
                ->where('department_id', $department->id)
                ->first();

            if ($firstHead) {
                DB::table('departments')
                    ->where('id', $department->id)
                    ->update(['department_head_id' => $firstHead->user_id]);
            }
        }

        // Clear the pivot table
        DB::table('department_head')->truncate();
    }
};
