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

use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventConfig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class SetBookingAvailabilityEvent extends Event
{
    public function __construct(
        private readonly Request $request,
        private readonly EventConfig $eventConfig,
        private string $bookingAvailability,
        private string $bookingAvailabilityExplain,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getEventConfig(): EventConfig
    {
        return $this->eventConfig;
    }

    public function stop(string $bookingAvailability, string $bookingAvailabilityExplain): void
    {
        $this->bookingAvailability = $bookingAvailability;
        $this->bookingAvailabilityExplain = $bookingAvailabilityExplain;
        $this->stopPropagation();
    }

    public function isStopped(): bool
    {
        return $this->isPropagationStopped();
    }

    public function getBookingAvailability(): string
    {
        return $this->bookingAvailability;
    }

    public function getBookingAvailabilityExplain(): string
    {
        return $this->bookingAvailabilityExplain;
    }

    public function setBookingAvailability(string $bookingAvailability): void
    {
        $this->bookingAvailability = $bookingAvailability;
    }

    public function setBookingAvailabilityExplain(string $bookingAvailabilityExplain): void
    {
        $this->bookingAvailabilityExplain = $bookingAvailabilityExplain;
    }
}
