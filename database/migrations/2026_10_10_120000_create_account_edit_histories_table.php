<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_edit_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('old_opening_balance', 15, 2)->default(0);
            $table->decimal('new_opening_balance', 15, 2)->default(0);
            $table->decimal('resulting_current_balance', 15, 2)->default(0);
            $table->string('old_title')->nullable();
            $table->string('new_title')->nullable();
            $table->text('changes_summary')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_edit_histories');
    }
};
