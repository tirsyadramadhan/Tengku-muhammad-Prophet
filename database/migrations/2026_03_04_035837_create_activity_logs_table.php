<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // create, update, delete, login, logout
            $table->string('model')->nullable(); // User, Product, Order, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected record
            $table->text('description')->nullable();
            $table->json('old_data')->nullable(); // Old values before update/delete
            $table->json('new_data')->nullable(); // New values after create/update
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable(); // GET, POST, PUT, DELETE
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('tbl_user')->onDelete('set null');
            $table->index(['user_id', 'action']);
            $table->index(['model', 'model_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
