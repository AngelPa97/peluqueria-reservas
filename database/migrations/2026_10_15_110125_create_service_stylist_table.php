<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_stylist', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stylist_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'stylist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_stylist');
    }
};
