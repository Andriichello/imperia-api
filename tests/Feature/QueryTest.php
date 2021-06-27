<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\Query;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueryTest extends TestCase
{
    public function testWhereQuery()
    {
        $query = Query::from(Customer::query())
            ->where(function ($q) {
                $q->equal('name', 'Tony')
                    ->orEqual('surname', 'Parker')
                    ->where(function ($q) {
                        $q->equal('price', 4.55)
                            ->orEqual('amount', 3);
                    });
            })
            ->finish();

        $this->assertSame(
            'select * from `customers` where (`name` = ? or `surname` = ? and (`price` = ? or `amount` = ?))',
            $query->toSql()
        );
    }

    public function testJoinQuery()
    {
        $query = Query::from(Customer::query())
            ->join('customer_family_members', function ($q) {
                $q->on('customer_family_members.customer_id', 'customers.id');
            })
            ->finish();

        $this->assertSame(
            'select * from `customers` inner join `customer_family_members` on `customer_family_members`.`customer_id` = `customers`.`id`',
            $query->toSql()
        );
    }
}
