<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('account', ['bkash', 'bank', 'cash'])->index();
            $table->enum('type', ['credit', 'debit'])->index();
            $table->decimal('amount', 14, 2);
            $table->string('purpose')->nullable();
            $table->string('reference')->nullable();   // order_number, expense ref, etc.
            $table->text('note')->nullable();
            $table->date('date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
