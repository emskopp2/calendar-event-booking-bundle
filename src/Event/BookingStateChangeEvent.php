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

use Markocupic\CalendarEventBookingBundle\Model\CebbRegistrationModel;
use Symfony\Contracts\EventDispatcher\Event;

class BookingStateChangeEvent extends Event
{
    public function __construct(
        private readonly CebbRegistrationModel $registration,
        private readonly string $bookingStateOld,
        private readonly string $bookingStateNew,
    ) {
    }

    public function getRegistration(): CebbRegistrationModel
    {
        return $this->registration;
    }

    public function getBookingStateOld(): string
    {
        return $this->bookingStateOld;
    }

    public function getBookingStateNew(): string
    {
        return $this->bookingStateNew;
    }
}
