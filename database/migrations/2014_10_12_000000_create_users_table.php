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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullName');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string("mobile")->nullable();
            $table->string("address")->nullable();
            $table->string("userType");
            $table->string("image")->nullable();
            $table->string("otp");
            $table->boolean("verify_email");
            $table->tinyInteger('user_status')->default(0);
            $table->rememberToken()->nullable();
            $table->string('google_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
