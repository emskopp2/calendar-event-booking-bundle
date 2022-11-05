<?php

declare(strict_types=1);

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

namespace Markocupic\CalendarEventBookingBundle\DataContainer;

use Contao\Calendar;
use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Date;
use Contao\Message;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Markocupic\CalendarEventBookingBundle\EventBooking\Booking\BookingState;
use Markocupic\CalendarEventBookingBundle\Model\CalendarEventsMemberModel;
use Symfony\Contracts\Translation\TranslatorInterface;

class CalendarEvents
{
    public const TABLE = 'tl_calendar_events';

    private ContaoFramework $framework;
    private Connection $connection;
    private TranslatorInterface $translator;

    // Adapters
    private Adapter $calendar;
    private Adapter $calendarEventsMemberModel;
    private Adapter $config;
    private Adapter $date;
    private Adapter $message;

    public function __construct(ContaoFramework $framework, Connection $connection, TranslatorInterface $translator)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->translator = $translator;

        // Adapters
        $this->calendar = $this->framework->getAdapter(Calendar::class);
        $this->calendarEventsMemberModel = $this->framework->getAdapter(CalendarEventsMemberModel::class);
        $this->config = $this->framework->getAdapter(Config::class);
        $this->date = $this->framework->getAdapter(Date::class);
        $this->message = $this->framework->getAdapter(Message::class);
    }

    /**
     * Adjust bookingStartDate and bookingStartDate.
     *
     * @Callback(table=CalendarEvents::TABLE, target="config.onsubmit")
     *
     * @throws Exception
     */
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
                $this->message->addInfo($GLOBALS['TL_LANG']['MSC']['adjusted_booking_period_end_time']);
            }
        }

        $this->connection->update(self::TABLE, $arrSet, ['id' => $dc->id]);
    }

    /**
     * Override child record callback.
     *
     * @Callback(table=CalendarEvents::TABLE, target="list.sorting.child_record")
     */
    public function listEvents(array $arrRow): string
    {
        if ('1' === $arrRow['activateBookingForm']) {
            $countBookings = $this->calendarEventsMemberModel->countBy('pid', $arrRow['id']);

            $span = $this->calendar->calculateSpan($arrRow['startTime'], $arrRow['endTime']);

            if ($span > 0) {
                $date = $this->date->parse($this->config->get(($arrRow['addTime'] ? 'datimFormat' : 'dateFormat')), $arrRow['startTime']).$GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'].$this->date->parse($this->config->get(($arrRow['addTime'] ? 'datimFormat' : 'dateFormat')), $arrRow['endTime']);
            } elseif ($arrRow['startTime'] === $arrRow['endTime']) {
                $date = $this->date->parse($this->config->get('dateFormat'), $arrRow['startTime']).($arrRow['addTime'] ? ' '.$this->date->parse($this->config->get('timeFormat'), $arrRow['startTime']) : '');
            } else {
                $date = $this->date->parse($this->config->get('dateFormat'), $arrRow['startTime']).($arrRow['addTime'] ? ' '.$this->date->parse($this->config->get('timeFormat'), $arrRow['startTime']).$GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'].$this->date->parse($this->config->get('timeFormat'), $arrRow['endTime']) : '');
            }

            return '<div class="tl_content_left">'.$arrRow['title'].' <span style="color:#999;padding-left:3px">['.$date.']</span><span style="color:#999;padding-left:3px">['.$GLOBALS['TL_LANG']['MSC']['bookings'].': '.$countBookings.'x]</span></div>';
        }

        return (new \tl_calendar_events())->listEvents($arrRow);
    }

    /**
     * @Callback(table=CalendarEvents::TABLE, target="fields.text.save")
     */
    public function saveUnsubscribeLimitTstamp(?int $intValue, DataContainer $dc): ?int
    {
        if (!empty($intValue)) {
            // Check whether we have an unsubscribeLimit (in days) set as well, notify the user that we cannot
            // have both
            if ($dc->activeRecord->unsubscribeLimit > 0) {
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['conflicting_unsubscribe_limits']);
            }

            // Check whether the timestamp entered makes sense in relation to the event start and end times
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
                throw new \InvalidArgumentException($GLOBALS['TL_LANG']['ERR']['invalid_unsubscription_limit']);
            }
        }

        return $intValue;
    }

    /**
     * @Callback(table="tl_calendar_events", target="list.sorting.child_record", priority=100)
     */
    public function childRecordCallback(array $arrRow): string
    {
        $origClass = new \tl_calendar_events();

        $strRegistrationsBadges = $this->getBookingStateBadgesString($arrRow);

        if ($strRegistrationsBadges) {
            $arrRow['title'] .= $strRegistrationsBadges;
        }

        return $origClass->listEvents($arrRow);
    }

    private function getBookingStateBadgesString(array $arrRow): string
    {
        $strRegistrationsBadges = '';

        $intNotConfirmed = 0;
        $intConfirmed = 0;
        $intRejected = 0;
        $intWaitingList = 0;
        $intUnsubscribed = 0;

        $eventsMemberModel = $this->calendarEventsMemberModel->findByPid($arrRow['id']);

        if (null !== $eventsMemberModel) {
            while ($eventsMemberModel->next()) {
                if (BookingState::STATE_NOT_CONFIRMED === $eventsMemberModel->bookingState) {
                    ++$intNotConfirmed;
                }

                if (BookingState::STATE_CONFIRMED === $eventsMemberModel->bookingState) {
                    ++$intConfirmed;
                }

                if (BookingState::STATE_REJECTED === $eventsMemberModel->bookingState) {
                    ++$intRejected;
                }

                if (BookingState::STATE_WAITING_LIST === $eventsMemberModel->bookingState) {
                    ++$intWaitingList;
                }

                if (BookingState::STATE_UNSUBSCRIBED === $eventsMemberModel->bookingState) {
                    ++$intUnsubscribed;
                }
            }

            if ($intNotConfirmed > 0) {
                $strRegistrationsBadges .= sprintf('<span class="subscription-badge not-confirmed blink" title="%dx %s">%sx</span>', $intNotConfirmed, $this->translator->trans('MSC.not_confirmed', [], 'contao_default'), $intNotConfirmed);
            }

            if ($intConfirmed > 0) {
                $strRegistrationsBadges .= sprintf('<span class="subscription-badge confirmed" title="%dx %s">%dx</span>', $intConfirmed, $this->translator->trans('MSC.confirmed', [], 'contao_default'), $intConfirmed);
            }

            if ($intRejected > 0) {
                $strRegistrationsBadges .= sprintf('<span class="subscription-badge rejected" title="%dx %s">%dx</span>', $intRejected, $this->translator->trans('MSC.rejected', [], 'contao_default'), $intRejected);
            }

            if ($intWaitingList > 0) {
                $strRegistrationsBadges .= sprintf('<span class="subscription-badge waiting-list" title="%dx %s">%dx</span>', $intWaitingList, $this->translator->trans('MSC.waiting_list', [], 'contao_default'), $intWaitingList);
            }

            if ($intUnsubscribed > 0) {
                $strRegistrationsBadges .= sprintf('<span class="subscription-badge unsubscribed" title="%dx %s">%dx</span>', $intUnsubscribed, $this->translator->trans('MSC.unsubscribed', [], 'contao_default'), $intUnsubscribed);
            }
        }

        return $strRegistrationsBadges;
    }
}
