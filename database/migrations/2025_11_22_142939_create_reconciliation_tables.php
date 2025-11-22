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
        // Транзакции из QuickBooks
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->nullable();
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->string('vendor', 100)->nullable();
            $table->string('vendor_raw', 255)->nullable();
            $table->text('memo')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('source', 50)->default('quickbooks');
            $table->string('card_last4', 4)->nullable();
            
            $table->enum('match_status', ['pending', 'matched', 'partial', 'unmatched'])->default('pending');
            $table->decimal('match_confidence', 3, 2)->nullable();
            
            $table->timestamps();
            
            $table->index('transaction_date');
            $table->index('amount');
            $table->index('vendor');
            $table->index('match_status');
        });

        // Заказы из различных платформ
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 100);
            $table->string('platform', 50);
            $table->string('store', 100)->nullable();
            $table->date('order_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('original_amount', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('client_id', 100)->nullable();
            $table->string('client_email', 255)->nullable();
            
            $table->enum('match_status', ['pending', 'matched', 'partial', 'unmatched'])->default('pending');
            
            $table->timestamps();
            
            $table->unique(['order_id', 'platform']);
            $table->index('order_date');
            $table->index('amount');
            $table->index('platform');
            $table->index('match_status');
        });

        // Товары в заказах
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->text('item_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Результаты сопоставления
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->enum('match_type', ['direct', 'split', 'discount', 'fuzzy', 'manual', 'ai_suggested']);
            $table->decimal('confidence', 3, 2)->default(1.00);
            
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            
            $table->json('transaction_ids')->nullable(); // Для split
            
            $table->decimal('amount_difference', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->string('discount_type', 50)->nullable();
            
            $table->boolean('ai_suggested')->default(false);
            $table->text('ai_explanation')->nullable();
            
            $table->boolean('verified')->default(false);
            $table->string('verified_by', 100)->nullable();
            $table->timestamp('verified_at')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        // Динамические правила matching
        Schema::create('matching_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            
            $table->string('vendor_pattern', 255)->nullable();
            $table->string('platform', 50)->nullable();
            $table->decimal('amount_min', 12, 2)->nullable();
            $table->decimal('amount_max', 12, 2)->nullable();
            
            $table->decimal('amount_tolerance', 12, 2)->default(0.10);
            $table->integer('date_tolerance_days')->default(3);
            $table->decimal('discount_tolerance', 12, 2)->default(0);
            $table->boolean('allow_splits')->default(true);
            $table->integer('max_split_parts')->default(5);
            
            $table->integer('priority')->default(100);
            $table->boolean('is_active')->default(true);
            
            $table->enum('created_by', ['manual', 'ai_suggested'])->default('manual');
            $table->decimal('ai_confidence', 3, 2)->nullable();
            $table->integer('times_used')->default(0);
            $table->decimal('success_rate', 5, 2)->nullable();
            
            $table->timestamps();
        });

        // AI предложения
        Schema::create('ai_suggestions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['match', 'rule', 'pattern', 'anomaly']);
            
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            
            $table->json('suggestion');
            $table->text('explanation');
            $table->decimal('confidence', 3, 2);
            
            $table->enum('status', ['pending', 'accepted', 'rejected', 'modified'])->default('pending');
            $table->string('reviewed_by', 100)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            $table->boolean('feedback_applied')->default(false);
            
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_suggestions');
        Schema::dropIfExists('matching_rules');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('transactions');
    }
};
