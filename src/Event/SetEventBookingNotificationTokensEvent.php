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
use Markocupic\CalendarEventBookingBundle\Model\CebbRegistrationModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class SetEventBookingNotificationTokensEvent extends Event
{
    public function __construct(
        private array $tokens,
        private readonly EventConfig $eventConfig,
        private readonly CebbRegistrationModel $registration,
        private readonly Request $request,
    ) {
    }

    public function getEventConfig(): EventConfig
    {
        return $this->eventConfig;
    }

    public function getRegistration(): CebbRegistrationModel
    {
        return $this->registration;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function hasToken(string $name): bool
    {
        return isset($this->tokens[$name]);
    }

    public function addToken(string $name, string $value): void
    {
        $this->tokens[$name] = $value;
    }

    public function unsetToken(string $name): void
    {
        if (isset($this->tokens[$name])) {
            unset($this->tokens[$name]);
        }
    }

    public function setTokens(array $tokens): void
    {
        $this->tokens = $tokens;
    }
}
