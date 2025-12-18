<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_code')->unique();
            $table->string('position');
            $table->date('join_date');
            $table->text('skills')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('workers');
    }
};
