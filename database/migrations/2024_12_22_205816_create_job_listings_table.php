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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade'); // Associate with departments
            $table->string('position_title');
            $table->enum('visibility', ['draft', 'public', 'internal'])->default('draft'); // Visibility options
            $table->text('description'); // Markdown content
            $table->unsignedInteger('number_of_openings')->default(1);
            $table->date('closing_date')->nullable(); // Closing date
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
