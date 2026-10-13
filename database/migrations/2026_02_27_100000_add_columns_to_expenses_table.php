<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('purpose')->after('user_id');
            $table->date('date')->after('purpose');
            $table->decimal('amount', 12, 2)->after('date');
            $table->tinyInteger('status')->default(2)->after('amount'); // 1=Approved, 2=Pending, 3=Rejected
            $table->string('category')->nullable()->after('status');
            $table->text('note')->nullable()->after('category');
            $table->string('attachment')->nullable()->after('note');
            $table->string('attachment_title')->nullable()->after('attachment');

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'purpose', 'date', 'amount', 'status',
                'category', 'note', 'attachment', 'attachment_title',
            ]);
        });
    }
};
