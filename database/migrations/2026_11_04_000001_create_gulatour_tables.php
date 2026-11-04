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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 200);
            $table->string('tagline', 255)->nullable();
            $table->unsignedSmallInteger('established_year')->default(1911);
            $table->text('history');
            $table->text('vision');
            $table->json('mission');
            $table->string('address', 255);
            $table->string('phone', 40);
            $table->string('whatsapp', 40);
            $table->string('email', 150);
            $table->string('operating_hours', 160)->nullable();
            $table->text('map_embed_url')->nullable();
            $table->string('hero_image', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('caption')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->string('category', 80)->default('Arsip Sejarah');
            $table->string('year_label', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title', 200);
            $table->text('description');
            $table->json('highlights')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(120);
            $table->string('meeting_point', 200)->default('Gerbang Utama PT Pabrik Gula Candi Baru, Sidoarjo');
            $table->string('cover_image', 255)->nullable();
            $table->unsignedInteger('ticket_price')->default(0);
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->date('tour_date');
            $table->string('session_code', 20);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('quota_total')->default(0);
            $table->unsignedInteger('quota_booked')->default(0);
            $table->enum('status', ['open', 'closed', 'full'])->default('open');
            $table->timestamps();

            $table->unique(['event_id', 'tour_date', 'session_code']);
            $table->index(['tour_date', 'status']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 32)->unique();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->string('access_token', 64);
            $table->foreignId('schedule_id')->constrained('schedules')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('buyer_name', 150);
            $table->string('buyer_email', 150)->index();
            $table->string('buyer_phone', 20);
            $table->unsignedSmallInteger('ticket_qty');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('total_amount');
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->string('midtrans_order_id', 64)->nullable()->unique();
            $table->string('midtrans_txn_id', 64)->nullable();
            $table->string('snap_token', 120)->nullable();
            $table->string('snap_redirect_url', 255)->nullable();
            $table->string('payment_channel', 40)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('ticket_code', 40)->unique();
            $table->string('qr_payload', 255);
            $table->enum('status', ['valid', 'used', 'void'])->default('valid');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('title', 180);
            $table->text('message');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('events');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('company_profiles');
    }
};

