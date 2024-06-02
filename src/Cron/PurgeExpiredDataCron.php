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

namespace Markocupic\CalendarEventBookingBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Markocupic\CalendarEventBookingBundle\EventBooking\Booking\BookingType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PurgeExpiredDataCron
{
    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%markocupic_calendar_event_booking.checkout_temp_locking_time%')]
        private readonly int $checkoutTempLockingTime,
    ) {
    }

    #[AsCronJob('minutely')]
    public function deleteExpiredData(): void
    {
        // Delete registrations with an uncompleted checkout
        $this->connection->executeStatement(
            'DELETE FROM tl_cebb_registration WHERE (bookingType = :bookingTypeMember OR bookingType = :bookingTypeGuest) AND checkoutCompleted = :checkoutCompleted AND tstamp < :limit',
            [
                'bookingTypeMember' => BookingType::TYPE_MEMBER,
                'bookingTypeGuest' => BookingType::TYPE_GUEST,
                'checkoutCompleted' => false,
                'limit' => time() - $this->checkoutTempLockingTime,
            ],
            [
                'bookingTypeMember' => Types::STRING,
                'bookingTypeGuest' => Types::STRING,
                'checkoutCompleted' => Types::BOOLEAN,
                'limit' => Types::INTEGER,
            ],
        );

        // Delete orphaned cart records
        $this->connection->executeStatement(
            'DELETE FROM tl_cebb_cart WHERE tstamp < :limit AND uuid NOT IN (SELECT cartUuid FROM tl_cebb_registration WHERE tl_cebb_registration.cartUuid = tl_cebb_cart.uuid)',
            [
                'limit' => time() - $this->checkoutTempLockingTime,
            ],
            [
                'limit' => Types::INTEGER,
            ],
        );

        // Delete orphaned order records
        $this->connection->executeStatement(
            'DELETE FROM tl_cebb_order WHERE tstamp < :limit AND tl_cebb_order.uuid NOT IN (SELECT orderUuid FROM tl_cebb_registration WHERE tl_cebb_registration.orderUuid = tl_cebb_order.uuid)',
            [
                'limit' => time() - $this->checkoutTempLockingTime,
            ],
            [
                'limit' => Types::INTEGER,
            ],
        );

        // Delete orphaned payment records
        $this->connection->executeStatement(
            'DELETE FROM tl_cebb_payment WHERE tstamp < :limit AND tl_cebb_payment.uuid NOT IN (SELECT paymentUuid FROM tl_cebb_order WHERE tl_cebb_order.paymentUuid = tl_cebb_payment.uuid)',
            [
                'limit' => time() - $this->checkoutTempLockingTime,
            ],
            [
                'limit' => Types::INTEGER,
            ],
        );
    }
}
