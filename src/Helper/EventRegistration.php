<?php

declare(strict_types=1);

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

namespace Markocupic\CalendarEventBookingBundle\Helper;

use Contao\CalendarEventsModel;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\FrontendUser;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\CalendarEventBookingEventBookingModuleController;
use Markocupic\CalendarEventBookingBundle\Model\CalendarEventsMemberModel;
use Symfony\Component\Security\Core\Security;

class EventRegistration
{
    /**
     * @var ContaoFramework
     */
    protected $framework;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Security
     */
    private $security;

    public function __construct(ContaoFramework $framework, Connection $connection, Security $security)
    {
        $this->framework = $framework;
        $this->connection = $connection;
        $this->security = $security;
    }

    public function hasLoggedInFrontendUser(): bool
    {
        $user = $this->security->getUser();

        return $user instanceof FrontendUser ? true : false;
    }

    public function getLoggedInFrontendUser(): ?FrontendUser
    {
        $user = $this->security->getUser();

        if ($user instanceof FrontendUser) {
            return $user;
        }

        return null;
    }

    public function getEventFromCurrentUrl(): ?CalendarEventsModel
    {
        $configAdapter = $this->framework->getAdapter(Config::class);
        $inputAdapter = $this->framework->getAdapter(Input::class);
        $calendarEventsModelAdapter = $this->framework->getAdapter(CalendarEventsModel::class);

        // Set the item from the auto_item parameter
        if (!isset($_GET['events']) && $configAdapter->get('useAutoItem') && isset($_GET['auto_item'])) {
            $inputAdapter->setGet('events', $inputAdapter->get('auto_item'));
        }

        // Return an empty string if "events" is not set
        if ('' !== $inputAdapter->get('events')) {
            if (null !== ($objEvent = $calendarEventsModelAdapter->findByIdOrAlias($inputAdapter->get('events')))) {
                return $objEvent;
            }
        }

        return null;
    }

    public function getRegistrationState(CalendarEventsModel $objEvent): string
    {
        if (null === $objEvent) {
            $state = CalendarEventBookingEventBookingModuleController::CASE_BOOKING_NOT_YET_POSSIBLE;
        } elseif (!$objEvent->addBookingForm) {
            $state = CalendarEventBookingEventBookingModuleController::CASE_BOOKING_FORM_DISABLED;
        } elseif ($objEvent->bookingStartDate > time()) {
            $state = CalendarEventBookingEventBookingModuleController::CASE_BOOKING_NOT_YET_POSSIBLE;
        } elseif (is_numeric($objEvent->bookingEndDate) && $objEvent->bookingEndDate < time()) {
            $state = CalendarEventBookingEventBookingModuleController::CASE_BOOKING_NO_LONGER_POSSIBLE;
        } elseif ($this->isFullyBooked($objEvent)) {
            $state = CalendarEventBookingEventBookingModuleController::CASE_EVENT_FULLY_BOOKED;
        } else {
            $state = CalendarEventBookingEventBookingModuleController::CASE_BOOKING_POSSIBLE;
        }

        return $state;
    }

    public function canRegister(CalendarEventsModel $objEvent): bool
    {
        return CalendarEventBookingEventBookingModuleController::CASE_BOOKING_POSSIBLE === $this->getRegistrationState($objEvent) ? true : false;
    }

    public function isFullyBooked(CalendarEventsModel $objEvent): bool
    {
        $bookingCount = $this->getBookingCount($objEvent);
        $bookingMax = $this->getBookingMax($objEvent);

        if ($bookingMax > 0 && $bookingCount >= $bookingMax) {
            return true;
        }

        return false;
    }

    public function getBookingCount(CalendarEventsModel $objEvent): int
    {
        $calendarEventsMemberModelAdaper = $this->framework->getAdapter(CalendarEventsMemberModel::class);
        $memberCount = (int) $calendarEventsMemberModelAdaper->countBy('pid', $objEvent->id);

        if ($objEvent->includeEscortsWhenCalculatingRegCount) {
            $query = 'SELECT SUM(escorts) FROM tl_calendar_events_member WHERE pid=?';
            $sum = $this->connection
                ->executeQuery($query, [(int) $objEvent->id])
                ->fetchColumn()
            ;

            if (false !== $sum) {
                $memberCount += $sum;
            }
        }

        return $memberCount;
    }

    public function getBookingMax(CalendarEventsModel $objEvent): int
    {
        return (int) $objEvent->maxMembers;
    }

    public function getBookingMin(CalendarEventsModel $objEvent): int
    {
        return (int) $objEvent->minMembers;
    }

    /**
     * @param CalendarEventsModel $objEvent
     * @param string $format
     * @return int|string
     */
    public function getBookingStartDate(CalendarEventsModel $objEvent, string $format= 'timestamp')
    {

        $dateAdapter = $this->framework->getAdapter(Date::class);
        $configAdapter = $this->framework->getAdapter(Config::class);

        $tstamp = empty($objEvent->bookingStartDate) ? 0 : $objEvent->bookingStartDate;

        if($format === 'timestamp'){
            $varValue = (int) $tstamp;
        }elseif($format==='date'){
            $varValue = $dateAdapter->parse($configAdapter->get('dateFormat'), $tstamp);
        }
        elseif($format==='datim'){
            $varValue = $dateAdapter->parse($configAdapter->get('datimFormat'), $tstamp);
        }else{
            $varValue = (int) $tstamp;
        }
        return $varValue;
    }

    /**
     * @param CalendarEventsModel $objEvent
     * @param string $format
     * @return int|string
     */
    public function getBookingEndDate(CalendarEventsModel $objEvent, string $format= 'timestamp')
    {
        $dateAdapter = $this->framework->getAdapter(Date::class);
        $configAdapter = $this->framework->getAdapter(Config::class);

        $tstamp = empty($objEvent->bookingEndDate) ? 0 : $objEvent->bookingEndDate;

        if($format === 'timestamp'){
            $varValue = (int) $tstamp;
        }elseif($format==='date'){
            $varValue = $dateAdapter->parse($configAdapter->get('dateFormat'), $tstamp);
        }
        elseif($format==='datim'){
            $varValue = $dateAdapter->parse($configAdapter->get('datimFormat'), $tstamp);
        }else{
            $varValue = (int) $tstamp;
        }
        return $varValue;
    }
}
