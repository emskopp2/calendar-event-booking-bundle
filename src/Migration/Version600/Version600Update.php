<?php

declare(strict_types=1);

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

namespace Markocupic\CalendarEventBookingBundle\Migration\Version600;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class Version600Update extends AbstractMigration
{
    private const ALTERATION_TYPE_RENAME_COLUMN = 'alteration_type_rename_column';

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Calendar Event Booking Bundle version 6.0.0 update';
    }

    public function shouldRun(): bool
    {
        $doMigration = false;
        $schemaManager = $this->connection->getSchemaManager();
        $arrAlterations = $this->getAlterationData();

        foreach ($arrAlterations as $arrAlteration) {
            $type = $arrAlteration['type'];

            // Version 2 migration: "Rename columns"
            if (self::ALTERATION_TYPE_RENAME_COLUMN === $type) {
                $strTable = $arrAlteration['table'];
                // If the database table itself does not exist we should do nothing
                if ($schemaManager->tablesExist([$strTable])) {
                    $columns = $schemaManager->listTableColumns($strTable);

                    if (isset($columns[strtolower($arrAlteration['old'])]) && !isset($columns[strtolower($arrAlteration['new'])])) {
                        $doMigration = true;
                    }
                }
            }
        }

        return $doMigration;
    }

    /**
     * @throws Exception
     */
    public function run(): MigrationResult
    {
        $resultMessages = [];

        $schemaManager = $this->connection->getSchemaManager();
        $arrAlterations = $this->getAlterationData();

        foreach ($arrAlterations as $arrAlteration) {
            $type = $arrAlteration['type'];

            // Version 2 migration: "Rename columns"
            if (self::ALTERATION_TYPE_RENAME_COLUMN === $type) {
                $strTable = $arrAlteration['table'];

                if ($schemaManager->tablesExist([$strTable])) {
                    $columns = $schemaManager->listTableColumns($strTable);

                    if (isset($columns[strtolower($arrAlteration['old'])]) && !isset($columns[strtolower($arrAlteration['new'])])) {
                        $strQuery = 'ALTER TABLE `'.$strTable.'` CHANGE `'.$arrAlteration['old'].'` `'.$arrAlteration['new'].'` '.$arrAlteration['sql'];

                        $this->connection->executeQuery($strQuery);

                        $resultMessages[] = sprintf(
                            'Rename column %s.%s to %s.%s. ',
                            $strTable,
                            $arrAlteration['old'],
                            $strTable,
                            $arrAlteration['new'],
                        );
                    }
                }
            }
        }

        return $this->createResult(true, $resultMessages ? implode("\n", $resultMessages) : null);
    }

    private function getAlterationData(): array
    {
        return [
            // tl_calendar_events
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events',
                'old' => 'enableNotificationCenter',
                'new' => 'activateBookingNotification',
                'sql' => 'char(1)',
            ],
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events',
                'old' => 'addBookingForm',
                'new' => 'activateBookingForm',
                'sql' => 'char(1)',
            ],
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events',
                'old' => 'enableDeregistration',
                'new' => 'activateDeregistration',
                'sql' => 'char(1)',
            ],
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events',
                'old' => 'eventBookingNotificationCenterIds',
                'new' => 'eventBookingNotification',
                'sql' => 'blob',
            ],
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events',
                'old' => 'includeEscortsWhenCalculatingRegCount',
                'new' => 'addEscortsToTotal',
                'sql' => 'char(1)',
            ],
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events',
                'old' => 'enableMultiBookingWithSameAddress',
                'new' => 'allowDuplicateEmail',
                'sql' => 'char(1)',
            ],
            // tl_calendar_events_member
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_calendar_events_member',
                'old' => 'addedOn',
                'new' => 'dateAdded',
                'sql' => 'int(10)',
            ],
            // tl_module
            [
                'type' => self::ALTERATION_TYPE_RENAME_COLUMN,
                'table' => 'tl_module',
                'old' => 'calendarEventBookingMemberListPartialTemplate',
                'new' => 'cebb_memberListPartialTemplate',
                'sql' => 'varchar(128)',
            ],
        ];
    }
}
