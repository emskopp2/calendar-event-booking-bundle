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

namespace Markocupic\CalendarEventBookingBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

class Module
{
    public const TABLE = 'tl_module';

    public function __construct(
        private readonly array $checkout,
    ) {
    }

    #[AsCallback(table: self::TABLE, target: 'fields.cebb_checkoutType.options')]
    public function getCheckoutTypes(): array
    {
        return array_keys($this->checkout);
    }
}
