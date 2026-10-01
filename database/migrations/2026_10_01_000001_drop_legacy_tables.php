<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Morph types of the removed models (slug => former class name).
     *
     * @var array
     */
    protected array $types = [
        'banquets' => 'App\Models\Banquet',
        'orders' => 'App\Models\Orders\Order',
        'banquet-orders' => 'App\Models\Orders\BanquetOrder',
        'product-order-fields' => 'App\Models\Orders\ProductOrderField',
        'service-order-fields' => 'App\Models\Orders\ServiceOrderField',
        'space-order-fields' => 'App\Models\Orders\SpaceOrderField',
        'ticket-order-fields' => 'App\Models\Orders\TicketOrderField',
        'customers' => 'App\Models\Customer',
        'family-members' => 'App\Models\FamilyMember',
        'spaces' => 'App\Models\Space',
        'tickets' => 'App\Models\Ticket',
        'services' => 'App\Models\Service',
        'waiters' => 'App\Models\Waiter',
        'tips' => 'App\Models\Morphs\Tip',
        'discounts' => 'App\Models\Morphs\Discount',
        'discountables' => 'App\Models\Morphs\Discountable',
    ];

    /**
     * Polymorphic tables that are kept (table => type column),
     * their rows that point at the removed models are deleted.
     *
     * @var array
     */
    protected array $morphs = [
        'mediables' => 'mediable_type',
        'categorizables' => 'categorizable_type',
        'taggables' => 'taggable_type',
        'periodicals' => 'periodical_type',
        'logs' => 'loggable_type',
        'alterations' => 'alterable_type',
        'comments' => 'commentable_type',
    ];

    /**
     * Tables of the removed models, dependants first.
     *
     * @var array
     */
    protected array $tables = [
        'product_order_fields',
        'service_order_fields',
        'space_order_fields',
        'ticket_order_fields',
        'orders',
        'banquets',
        'family_members',
        'customers',
        'tips',
        'waiters',
        'spaces',
        'tickets',
        'services',
        'discountables',
        'discounts',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $types = array_merge(array_keys($this->types), array_values($this->types));

        foreach ($this->morphs as $table => $column) {
            if (Schema::hasTable($table)) {
                DB::table($table)->whereIn($column, $types)->delete();
            }
        }

        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Irreversible: the dropped tables and their data can only be restored from a database backup.
    }
};
