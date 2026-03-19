<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('doc_type');
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('watermark_text')->nullable();
            $table->string('access_token', 128)->nullable();
            $table->timestamp('access_expires_at')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['lead_id', 'doc_type']);
            $table->index(['access_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_documents');
    }
};
