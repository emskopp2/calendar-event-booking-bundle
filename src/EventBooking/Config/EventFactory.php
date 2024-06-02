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

namespace Markocupic\CalendarEventBookingBundle\EventBooking\Config;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Markocupic\CalendarEventBookingBundle\EventBooking\Validator\BookingValidator;

class EventFactory
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly BookingValidator $bookingValidator,
    ) {
    }

    public function create(CalendarEventsModel $event): EventConfig
    {
        return new EventConfig($this->bookingValidator, $event, $this->connection, $this->framework);
    }
}
