<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add composite and lookup indexes for high-frequency queries.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->index('qr_payload', 'tickets_qr_payload_idx');
            $table->index(['status', 'checked_in_at'], 'tickets_status_checked_in_idx');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at'], 'orders_user_created_idx');
            $table->index(['schedule_id', 'status'], 'orders_schedule_status_idx');
        });

        Schema::table('gallery_items', function (Blueprint $table): void {
            $table->index(['is_active', 'sort_order'], 'gallery_items_active_sort_idx');
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->index(['is_published', 'published_at'], 'articles_published_date_idx');
        });

        Schema::table('admin_notifications', function (Blueprint $table): void {
            $table->index(['is_read', 'created_at'], 'admin_notifications_read_created_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index('created_at', 'audit_logs_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex('tickets_qr_payload_idx');
            $table->dropIndex('tickets_status_checked_in_idx');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_user_created_idx');
            $table->dropIndex('orders_schedule_status_idx');
        });

        Schema::table('gallery_items', function (Blueprint $table): void {
            $table->dropIndex('gallery_items_active_sort_idx');
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->dropIndex('articles_published_date_idx');
        });

        Schema::table('admin_notifications', function (Blueprint $table): void {
            $table->dropIndex('admin_notifications_read_created_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_created_at_idx');
        });
    }
};

