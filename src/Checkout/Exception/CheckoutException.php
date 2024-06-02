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

namespace Markocupic\CalendarEventBookingBundle\Checkout\Exception;

class CheckoutException extends \RuntimeException
{
    public function __construct(
        string $reason,
        private string $translatableText,
    ) {
        parent::__construct($reason);
    }

    public function getTranslatableText(): string
    {
        return $this->translatableText;
    }

    public function setTranslatableText(string $translatableText): void
    {
        $this->translatableText = $translatableText;
    }
}
