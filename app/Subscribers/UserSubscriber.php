<?php

namespace App\Subscribers;

use App\Enums\UserRole;
use App\Events\User\Registered;
use App\Models\Customer;
use App\Repositories\CustomerRepository;

/**
 * Class UserSubscriber.
 */
class UserSubscriber extends BaseSubscriber
{
    protected function map(): void
    {
        $this->map = [
            Registered::class => 'registered',
        ];
    }

    /**
     * Handle user registered event.
     *
     * @param Registered $event
     *
     * @return void
     */
    public function registered(Registered $event): void
    {
        $user = $event->getUser();
        if ($user->hasRole(UserRole::Customer) === false) {
            return;
        }

        /** @var Customer|null $customer */
        $customer = Customer::query()
            ->where('email', $user->email)
            ->first();

        if (!$customer) {
            /** @var CustomerRepository $repository */
            $repository = app(CustomerRepository::class);
            $repository->createFromUser($user, $event->getPhone());
            return;
        }

        $customer->user_id = $user->id;
        $customer->phone = $customer->phone ?? $event->getPhone();
        $customer->save();
    }
}
