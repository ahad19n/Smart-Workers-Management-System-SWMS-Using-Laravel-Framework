<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->onDelete('cascade');
            $table->string('month');
            $table->integer('basic_salary');
            $table->integer('overtime_hours')->default(0);
            $table->integer('deductions')->default(0);
            $table->integer('net_salary');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('payroll');
    }
};
