<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TicketRepository.
 */
class TicketRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Ticket::class;
}
