<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Services\Conditions\Condition;
use App\Services\Conditions\ConditionFactory;
use App\Services\Query;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
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


    /**
     * @param string $conditionType
     * @param string|null $operator
     *
     * @testWith ["equal", "="]
     * ["not equal", "!="]
     * ["or equal", "="]
     * ["or not equal", "!="]
     * ["in", "in"]
     * ["or in", "in"]
     * ["not in", "not in"]
     * ["or not in", "or not in"]
     * ["on", "="]
     * ["or on", "="]
     */
    public function testConditionFactory(string $conditionType, ?string $operator)
    {
        if (!isset($operator)) {
            $this->expectException(\Exception::class);
        }

        $name = 'name';
        $value = 'value';
        $condition = ConditionFactory::make($conditionType, $name, $value);
        $this->assertTrue($condition instanceof Condition);
        $this->assertSame($name, $condition->getName());
        $this->assertSame($operator, $condition->getOperator());
        $this->assertSame(Arr::wrap($value), Arr::wrap($condition->getValue()));
    }
}
