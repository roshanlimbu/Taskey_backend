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
        Schema::create('task_commit_hashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->index();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->unsignedBigInteger('project_id')->index();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('commit_hash', 40);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_commit_hashes', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn(['task_id', 'project_id']);
        });
        Schema::dropIfExists('task_commit_hashes');
    }
};
