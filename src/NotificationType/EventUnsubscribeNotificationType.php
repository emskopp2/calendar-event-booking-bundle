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

namespace Markocupic\CalendarEventBookingBundle\NotificationType;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class EventUnsubscribeNotificationType implements NotificationTypeInterface
{
    public const NAME = 'event-unsubscribe-notification';

    public function __construct(
        private readonly TokenDefinitionFactoryInterface $factory,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        $tokenDefinitions = [];

        foreach ($this->getTokenConfig()['text_token'] as $token) {
            $tokenDefinitions[] = $this->factory->create(TextTokenDefinition::class, $token, 'event_unsubscribe.'.$token);
        }

        foreach ($this->getTokenConfig()['email_token'] as $token) {
            $tokenDefinitions[] = $this->factory->create(EmailTokenDefinition::class, $token, 'event_unsubscribe.'.$token);
        }

        return $tokenDefinitions;
    }

    private function getTokenConfig(): array
    {
        return [
            'email_token' => [
                'sender_email',
                'member_email',
                'admin_email',
            ],
            'text_token' => [
                'event_*',
                'event_title',
                'event_unsubscribeLimitTstamp',
                'member_*',
                'member_dateOfBirth',
                'member_salutation',
                'member_cancelRegistrationUrl',
                'member_cancelOrderUrl',
                'order_uuid',
                'order_*',
                'sender_*',
                'sender_name',
                'sender_email',
                'admin_email',
            ],
        ];
    }
}
