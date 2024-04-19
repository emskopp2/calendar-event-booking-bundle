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

namespace Markocupic\CalendarEventBookingBundle\Migration\Version500;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\CalendarEventBookingEventBookingModuleController;
use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\CalendarEventBookingMemberListModuleController;
use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\CalendarEventBookingUnsubscribeFromEventModuleController;

class RenameFrontendModuleType extends AbstractMigration
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function shouldRun(): bool
    {
        $doMigration = false;

        $schemaManager = $this->connection->getSchemaManager();

        // If the database table itself does not exist we should do nothing
        if ($schemaManager->tablesExist(['tl_module'])) {
            $columns = $schemaManager->listTableColumns('tl_module');

            if (isset($columns['type'])) {
                // #1 Rename frontend module type
                $count = $this->connection->fetchOne(
                    'SELECT COUNT(id) FROM tl_module WHERE type = ?',
                    ['calendar_event_booking_member_list'],
                );

                if ($count > 0) {
                    $doMigration = true;
                }

                // #2 Rename frontend module type
                $count = $this->connection->fetchOne(
                    'SELECT COUNT(id) FROM tl_module WHERE type = ?',
                    ['unsubscribefromevent'],
                );

                if ($count > 0) {
                    $doMigration = true;
                }

                // #3 Rename frontend module type
                $count = $this->connection->fetchOne(
                    'SELECT COUNT(id) FROM tl_module WHERE type = ?',
                    ['eventbooking'],
                );

                if ($count > 0) {
                    $doMigration = true;
                }
            }
            // #4 Rename tl_module.calendar_event_booking_member_list_partial_template to tl_module.calendarEventBookingMemberListPartialTemplate
            if (isset($columns['calendar_event_booking_member_list_partial_template'])) {
                $doMigration = true;
            }
        }

        return $doMigration;
    }

    /**
     * @throws Exception
     */
    public function run(): MigrationResult
    {
        $arrMessage = [];

        // #1 Rename frontend module type
        $count = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_module WHERE type = ?',
            ['calendar_event_booking_member_list'],
        );

        if ($count > 0) {
            $set = [
                'type' => CalendarEventBookingMemberListModuleController::TYPE,
            ];
            $this->connection->update('tl_module', $set, ['type' => 'calendar_event_booking_member_list']);
            $arrMessage[] = 'Renamed frontend module type "calendar_event_booking_member_list" to "'.$set['type'].'". Please rename your custom templates from "mod_calendar_event_booking_member_list.html5" to "mod_calendar_event_booking_member_list_module.html5".';
        }

        // #2 Rename frontend module type
        $count = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_module WHERE type = ?',
            ['unsubscribefromevent'],
        );

        if ($count > 0) {
            $set = [
                'type' => CalendarEventBookingUnsubscribeFromEventModuleController::TYPE,
            ];
            $this->connection->update('tl_module', $set, ['type' => 'unsubscribefromevent']);
            $arrMessage[] = 'Renamed frontend module type "unsubscribefromevent" to "'.$set['type'].'". Please rename your custom templates from "mod_unsubscribefromevent.html5" to "mod_calendar_event_booking_unsubscribe_from_event_module.html5".';
        }

        // #3 Rename frontend module type
        $count = $this->connection->fetchOne(
            'SELECT COUNT(id) FROM tl_module WHERE type = ?',
            ['eventbooking'],
        );

        if ($count > 0) {
            $set = [
                'type' => CalendarEventBookingEventBookingModuleController::TYPE,
            ];
            $this->connection->update('tl_module', $set, ['type' => 'eventbooking']);
            $arrMessage[] = 'Renamed frontend module type "eventbooking" to "'.$set['type'].'". Please rename your custom templates from "mod_eventbooking.html5" to "mod_calendar_event_booking_event_booking_module.html5".';
        }

        $schemaManager = $this->connection->getSchemaManager();

        // #4 Rename tl_module.calendar_event_booking_member_list_partial_template to tl_module.calendarEventBookingMemberListPartialTemplate
        if ($schemaManager->tablesExist(['tl_module'])) {
            $columns = $schemaManager->listTableColumns('tl_module');

            if (isset($columns['calendar_event_booking_member_list_partial_template'])) {
                $this->connection->executeQuery('ALTER TABLE tl_module CHANGE calendar_event_booking_member_list_partial_template calendarEventBookingMemberListPartialTemplate varchar(128)');
                $arrMessage[] = 'Rename tl_module.calendar_event_booking_member_list_partial_template to tl_module.calendarEventBookingMemberListPartialTemplate';
            }
        }

        return new MigrationResult(
            true,
            implode(' ', $arrMessage)
        );
    }
}
