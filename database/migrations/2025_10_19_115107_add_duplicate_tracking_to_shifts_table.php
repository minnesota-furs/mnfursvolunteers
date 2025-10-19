<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedBigInteger('original_shift_id')->nullable()->after('id');
            $table->string('duplicate_series_id', 36)->nullable()->after('original_shift_id');
            $table->integer('duplicate_sequence')->nullable()->after('duplicate_series_id');
            
            $table->foreign('original_shift_id')
                  ->references('id')
                  ->on('shifts')
                  ->onDelete('set null');
            
            $table->index('duplicate_series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['original_shift_id']);
            $table->dropIndex(['duplicate_series_id']);
            $table->dropColumn(['original_shift_id', 'duplicate_series_id', 'duplicate_sequence']);
        });
    }
};
