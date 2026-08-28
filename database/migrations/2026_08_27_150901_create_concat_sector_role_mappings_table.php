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
        Schema::create('concat_sector_role_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sector_id')->unique()->constrained()->onDelete('cascade');
            $table->string('concat_role_id');
            $table->string('concat_role_name');
            $table->string('concat_scope')->default('convention');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concat_sector_role_mappings');
    }
};
