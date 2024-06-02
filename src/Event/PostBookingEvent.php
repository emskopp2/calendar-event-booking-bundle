<?php

declare(strict_types=1);

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2024 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

namespace Markocupic\CalendarEventBookingBundle\Event;

use Contao\Model\Collection;
use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventConfig;
use Markocupic\CalendarEventBookingBundle\Model\CebbCartModel;
use Markocupic\CalendarEventBookingBundle\Model\CebbOrderModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class PostBookingEvent extends Event
{
    public function __construct(
        private readonly EventConfig $eventConfig,
        private readonly CebbOrderModel $order,
        private readonly CebbCartModel $cart,
        private readonly Collection $registrations,
        private readonly Request $request,
    ) {
    }

    public function getEventConfig(): EventConfig
    {
        return $this->eventConfig;
    }

    public function getEventRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function getOrder(): CebbOrderModel
    {
        return $this->order;
    }

    public function getCart(): CebbCartModel
    {
        return $this->cart;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
