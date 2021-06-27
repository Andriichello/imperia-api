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
                        $q->equal('phone', '111 111 222 222')
                            ->orEqual('email', 'tony.parker@mail.com');
                    });
            })
            ->finish();

        $this->assertSame(
            'select * from `customers` where (`name` = ? or `surname` = ? and (`phone` = ? or `email` = ?))',
            $query->toSql()
        );
    }

    public function testInQuery()
    {
        $query = Query::from(Customer::query())
            ->where(function ($q) {
                $q->in('id', [1, 2, 3])
                    ->notIn('name', 'John')
                    ->orIn('name', ['Austin', 'James']);
            })
            ->finish();

        $this->assertSame(
            'select * from `customers` where (`id` in (?, ?, ?) and `name` not in (?) or `name` in (?, ?))',
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
