<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('update_note')->nullable();
            $table->string('file_path');
            $table->string('original_file_name');
            $table->string('mime_type', 120);
            $table->string('file_type', 20);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->date('effective_at')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['department_id', 'status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_structures');
    }
};
