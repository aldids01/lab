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
        Schema::create('test_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 20, 2)->default(0);
            $table->longText('range')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_lists');
    }
};
