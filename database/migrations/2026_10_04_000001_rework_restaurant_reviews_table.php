<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reviews of restaurants, which guests leave on the public site: a rating from 1 to 5, an optional
 * name and text, in the language they were written in. New ones wait for the restaurant to approve
 * them. Neither IPs nor the guests' device tokens are stored as they are, only their hashes.
 * Reviews left before keep their texts (a title goes above the text), and the ones without a rating
 * from 1 to 5 are rejected.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('restaurant_reviews', function (Blueprint $table) {
            $table->renameColumn('reviewer', 'name');
            $table->renameColumn('score', 'rating');
            $table->renameColumn('description', 'text');
        });

        Schema::table('restaurant_reviews', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->text('text')->nullable()->change();
            $table->string('locale', 8)->nullable()->after('text');
            $table->string('status', 16)->default('pending')->after('locale');
            $table->timestamp('moderated_at')->nullable()->after('status');
            $table->foreignId('moderated_by')->nullable()->after('moderated_at')
                ->constrained('users')->nullOnDelete();
            $table->char('ip_hash', 64)->nullable()->after('moderated_by');
            $table->char('client_hash', 64)->nullable()->after('ip_hash');
        });

        DB::table('restaurant_reviews')->orderBy('id')->each(function (object $review) {
            $valid = is_numeric($review->rating) && $review->rating >= 1 && $review->rating <= 5;

            DB::table('restaurant_reviews')->where('id', $review->id)->update([
                'rating' => $valid ? $review->rating : 1,
                'text' => implode("\n\n", array_filter([$review->title, $review->text])) ?: null,
                'status' => match (true) {
                    !$valid, (bool) $review->is_rejected => 'rejected',
                    (bool) $review->is_approved => 'approved',
                    default => 'pending',
                },
                'ip_hash' => $review->ip ? hash_hmac('sha256', $review->ip, (string) config('app.key')) : null,
            ]);
        });

        Schema::table('restaurant_reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable(false)->change();
            $table->dropColumn(['ip', 'title', 'is_approved', 'is_rejected']);

            // approved ones of a restaurant by date, and a device's reviews of a day
            $table->index(['restaurant_id', 'status', 'created_at']);
            $table->index(['restaurant_id', 'client_hash', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // the restaurant's foreign key gets its own index back (MySQL uses one of these for it)
        if (!Schema::hasIndex('restaurant_reviews', 'restaurant_reviews_restaurant_id_foreign')) {
            Schema::table('restaurant_reviews', function (Blueprint $table) {
                $table->index('restaurant_id', 'restaurant_reviews_restaurant_id_foreign');
            });
        }

        Schema::table('restaurant_reviews', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'status', 'created_at']);
            $table->dropIndex(['restaurant_id', 'client_hash', 'created_at']);
            $table->ipAddress('ip')->nullable()->after('restaurant_id');
            $table->string('title')->nullable()->after('rating');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_rejected')->default(false);
        });

        DB::table('restaurant_reviews')->update([
            'is_approved' => DB::raw("status = 'approved'"),
            'is_rejected' => DB::raw("status = 'rejected'"),
            'name' => DB::raw("coalesce(name, 'Guest')"),
        ]);

        Schema::table('restaurant_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderated_by');
            $table->dropColumn(['locale', 'status', 'moderated_at', 'ip_hash', 'client_hash']);
            $table->smallInteger('rating')->nullable()->change();
            $table->string('name')->nullable(false)->change();
            $table->string('text', 510)->nullable()->change();
        });

        Schema::table('restaurant_reviews', function (Blueprint $table) {
            $table->renameColumn('name', 'reviewer');
            $table->renameColumn('rating', 'score');
            $table->renameColumn('text', 'description');
        });
    }
};
