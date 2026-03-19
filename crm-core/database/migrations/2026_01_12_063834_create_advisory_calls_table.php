<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('advisory_calls', function (Blueprint $table) {
            $table->id();
            $table->string('segment'); // Stock Cash, Stock Future, Nifty Option, etc.
            $table->text('call_text'); // The actual trading tip/advisory
            $table->date('call_date');
            $table->time('call_time');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who created the call
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advisory_calls');
    }
};
