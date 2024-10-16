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

use Contao\Calendar;
use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Message;
use Markocupic\CalendarEventBookingBundle\Model\CalendarEventsMemberModel;

class CalendarEvents
{
    /**
     * Adjust bookingStartDate and bookingStartDate.
     */
    #[AsCallback(table: 'tl_calendar_events', target: 'config.onsubmit')]
    public function adjustBookingDate(DataContainer $dc): void
    {
        // Return if there is no active record (override all)
        if (!$dc->activeRecord) {
            return;
        }

        $arrSet['bookingStartDate'] = $dc->activeRecord->bookingStartDate ?: null;
        $arrSet['bookingEndDate'] = $dc->activeRecord->bookingEndDate ?: null;

        // Set end date
        if (!empty((int) $dc->activeRecord->bookingEndDate)) {
            if ($dc->activeRecord->bookingEndDate < $dc->activeRecord->bookingStartDate) {
                $arrSet['bookingEndDate'] = $dc->activeRecord->bookingStartDate;
                Message::addInfo($GLOBALS['TL_LANG']['MSC']['adjustedBookingPeriodEndtime']);
            }
        }

        Database::getInstance()
            ->prepare('UPDATE tl_calendar_events %s WHERE id = ?')
            ->set($arrSet)
            ->execute($dc->id)
        ;
    }

    #[AsCallback(table: 'tl_calendar_events', target: 'list.sorting.child_record')]
    public function listEvents(array $arrRow): string
    {
        if ('1' === $arrRow['addBookingForm']) {
            $countBookings = CalendarEventsMemberModel::countBy('pid', $arrRow['id']);

            $span = Calendar::calculateSpan($arrRow['startTime'], $arrRow['endTime']);

            if ($span > 0) {
                $date = Date::parse(Config::get(($arrRow['addTime'] ? 'datimFormat' : 'dateFormat')), $arrRow['startTime']).$GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'].Date::parse(Config::get(($arrRow['addTime'] ? 'datimFormat' : 'dateFormat')), $arrRow['endTime']);
            } elseif ($arrRow['startTime'] === $arrRow['endTime']) {
                $date = Date::parse(Config::get('dateFormat'), $arrRow['startTime']).($arrRow['addTime'] ? ' '.Date::parse(Config::get('timeFormat'), $arrRow['startTime']) : '');
            } else {
                $date = Date::parse(Config::get('dateFormat'), $arrRow['startTime']).($arrRow['addTime'] ? ' '.Date::parse(Config::get('timeFormat'), $arrRow['startTime']).$GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'].Date::parse(Config::get('timeFormat'), $arrRow['endTime']) : '');
            }

            return '<div class="tl_content_left">'.$arrRow['title'].' <span style="color:#999;padding-left:3px">['.$date.']</span><span style="color:#999;padding-left:3px">['.$GLOBALS['TL_LANG']['MSC']['bookings'].': '.$countBookings.'x]</span></div>';
        }

        return (new \tl_calendar_events())->listEvents($arrRow);
    }

    public function saveUnsubscribeLimitTstamp(?int $intValue, DataContainer $dc): ?int
    {
        if (!empty($intValue)) {
            // Check whether we have an unsubscribeLimit (in days) set as well, notify the user that we cannot
            // have both
            if ($dc->activeRecord->unsubscribeLimit > 0) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['conflictingUnsubscribeLimits']);
            }

            // Check whether the timestamp entered makes sense in relation to the event start and end times
            $intMaxValue = null;

            // If the event has an end date (and optional time) that's the last sensible time unsubscription makes sense
            if ($dc->activeRecord->endDate) {
                if ($dc->activeRecord->addTime) {
                    $intMaxValue = (int) strtotime(date('Y-m-d', (int) $dc->activeRecord->endDate).' '.date('H:i:s', (int) $dc->activeRecord->endTime));
                } else {
                    $intMaxValue = (int) $dc->activeRecord->endDate;
                }
            } else {
                if ($dc->activeRecord->addTime) {
                    $intMaxValue = (int) strtotime(date('Y-m-d', (int) $dc->activeRecord->startDate).' '.date('H:i:s', (int) $dc->activeRecord->startTime));
                } else {
                    $intMaxValue = (int) $dc->activeRecord->startDate;
                }
            }

            if ($intValue > $intMaxValue) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['invalidUnsubscriptionLimit']);
            }
        }

        return $intValue;
    }
}
