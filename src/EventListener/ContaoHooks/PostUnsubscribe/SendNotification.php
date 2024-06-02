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

namespace Markocupic\CalendarEventBookingBundle\EventListener\ContaoHooks\PostUnsubscribe;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\StringUtil;
use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventConfig;
use Markocupic\CalendarEventBookingBundle\EventBooking\EventRegistration\EventRegistration;
use Markocupic\CalendarEventBookingBundle\EventBooking\Notification\Notification;
use Markocupic\CalendarEventBookingBundle\EventListener\ContaoHooks\AbstractHook;

#[AsHook(SendNotification::HOOK, priority: 1000)]
final class SendNotification extends AbstractHook
{
    public const HOOK = AbstractHook::HOOK_UNSUBSCRIBE_FROM_EVENT;

    private Adapter $stringUtilAdapter;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Notification $notification,
    ) {
        $this->stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
    }

    /**
     * @throws \Exception
     */
    public function __invoke(EventConfig $eventConfig, EventRegistration $eventRegistration): void
    {
        if (!self::isEnabled()) {
            return;
        }

        // Multiple notifications possible
        $arrNotificationIds = $this->stringUtilAdapter->deserialize($eventConfig->get('eventUnsubscribeNotification'), true);
        $arrNotificationIds = array_map('intval', $arrNotificationIds);

        if (!empty($arrNotificationIds)) {
            // Get notification tokens
            $this->notification->setTokens($eventConfig, $eventRegistration->getModel(), (int) $eventConfig->get('eventUnsubscribeNotificationSender'));
            $this->notification->notify($arrNotificationIds);
        }
    }
}
