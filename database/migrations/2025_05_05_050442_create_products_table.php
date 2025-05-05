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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->string('UNIQUE_KEY')->unique();
            $table->string('PRODUCT_TITLE');
            $table->text('PRODUCT_DESCRIPTION')->nullable();
            $table->string('STYLE#')->nullable();
            $table->string('SANMAR_MAINFRAME_COLOR')->nullable();
            $table->string('SIZE')->nullable();
            $table->string('COLOR_NAME')->nullable();
            $table->decimal('PIECE_PRICE', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['UNIQUE_KEY', 'PRODUCT_TITLE', 'STYLE#', 'SIZE', 'COLOR_NAME'], 'product_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
