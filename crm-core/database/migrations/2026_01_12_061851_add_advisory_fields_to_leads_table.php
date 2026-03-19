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
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('is_kyc_completed')->default(false)->after('status');
            $table->boolean('is_rpm_completed')->default(false)->after('is_kyc_completed');
            $table->string('pan_number')->nullable()->after('is_rpm_completed');
            $table->string('aadhaar_number')->nullable()->after('pan_number');
            $table->json('interest_segments')->nullable()->after('aadhaar_number'); // e.g. ["Stock Cash", "Nifty Option"]
            $table->boolean('is_trading')->default(false)->after('interest_segments');
            $table->decimal('expected_payment', 10, 2)->default(0)->after('is_trading');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'is_kyc_completed',
                'is_rpm_completed',
                'pan_number',
                'aadhaar_number',
                'interest_segments',
                'is_trading',
                'expected_payment'
            ]);
        });
    }
};
