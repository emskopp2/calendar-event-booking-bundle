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

namespace Markocupic\CalendarEventBookingBundle\Registry;

class NonExistingServiceException extends \InvalidArgumentException
{
    public function __construct($context, $type, array $existingServices)
    {
        parent::__construct(
            sprintf(
                '%s service "%s" does not exist, available %s services: "%s"',
                ucfirst($context),
                $type,
                $context,
                implode('", "', $existingServices),
            ),
        );
    }
}
