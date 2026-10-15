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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_per_day');
            $table->unsignedInteger('deposit');
            $table->unsignedInteger('stock')->default(1);
            $table->json('specs')->nullable();
            $table->string('status')->default('active'); // active, maintenance, inactive
            $table->timestamps();
        });

        Schema::create('item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('blackouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'start_at', 'end_at']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->dateTime('picked_up_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedInteger('subtotal_rental');
            $table->unsignedInteger('deposit_total');
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('late_fee')->default(0);
            $table->unsignedInteger('damage_fee')->default(0);
            $table->unsignedInteger('grand_total');
            $table->json('handover_checklist')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'start_at', 'end_at']);
            $table->index(['expires_at']);
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('unit_deposit');
            $table->unsignedInteger('subtotal');
            $table->timestamps();

            $table->index(['item_id', 'booking_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('type')->default('rental_and_deposit');
            $table->string('method')->default('transfer');
            $table->unsignedInteger('amount');
            $table->string('proof_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('booking_status_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('blackouts');
        Schema::dropIfExists('item_images');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
    }
};
