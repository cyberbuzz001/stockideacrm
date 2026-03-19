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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_mode'); // UPI, NEFT, Cash
            $table->string('transaction_id')->nullable();
            $table->string('subscription_plan')->nullable();

            $table->string('status')->default('Pending'); // Pending, Verified
            $table->foreignId('verified_by')->nullable()->constrained('users');

            // Commission Split
            $table->decimal('commission_ba', 10, 2)->default(0);
            $table->decimal('commission_sba', 10, 2)->default(0);

            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
