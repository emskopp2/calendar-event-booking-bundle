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

namespace Markocupic\CalendarEventBookingBundle\Checkout\Step;

use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventConfig;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;

#[AutoconfigureTag('markocupic_calendar_event_booking.checkout.step')]
interface CheckoutStepInterface
{
    public function initialize(EventConfig $eventConfig, Request $request): void;

    public function getIdentifier(): string;

    public function setTemplatePath(string $templatePath): void;

    public function getTemplatePath(): string;

    public function doAutoForward(EventConfig $eventConfig, Request $request): bool;

    public function commitStep(EventConfig $eventConfig, Request $request): bool;

    public function prepareStep(EventConfig $eventConfig, Request $request): array;
}
