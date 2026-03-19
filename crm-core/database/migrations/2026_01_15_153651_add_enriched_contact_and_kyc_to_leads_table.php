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
            $table->text('address')->nullable()->after('location');
            $table->string('demat_id')->nullable()->after('demat_status');
            $table->string('investment_cap')->nullable();
            $table->string('experience_level')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'demat_id',
                'investment_cap',
                'experience_level'
            ]);
        });
    }
};
