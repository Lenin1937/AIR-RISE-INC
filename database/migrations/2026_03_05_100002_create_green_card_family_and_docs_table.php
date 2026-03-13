<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('green_card_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('green_card_applications')->cascadeOnDelete();
            $table->enum('type', ['spouse', 'child']);
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('country_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('green_card_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('green_card_applications')->cascadeOnDelete();
            $table->string('document_type'); // passport, education_certificate, marriage_certificate, birth_certificate
            $table->string('label');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('green_card_documents');
        Schema::dropIfExists('green_card_family_members');
    }
};
