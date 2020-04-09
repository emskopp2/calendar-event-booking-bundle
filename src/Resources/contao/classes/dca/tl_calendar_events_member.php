<?php

/**
 * Calendar Event Booking Bundle Extension for Contao CMS
 * Copyright (c) 2008-2020 Marko Cupic
 * @package Markocupic\CalendarEventBookingBundle
 * @author Marko Cupic m.cupic@gmx.ch, 2020
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

use Markocupic\ExportTable\ExportTable;

/**
 * Class tl_calendar_events_member
 */
class tl_calendar_events_member extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     *
     * OnLoad Callback
     */
    public function onloadCallback()
    {
        // Download the registration list as a csv spreadsheet
        if (Input::get('act') == 'downloadRegistrationList')
        {
            $opt = array();

            // Add fields
            $arrSkip = array('bookingToken');
            $opt['arrSelectedFields'] = array();
            foreach ($GLOBALS['TL_DCA']['tl_calendar_events_member']['fields'] as $k => $v)
            {
                if (!\in_array($k, $arrSkip))
                {
                    $opt['arrSelectedFields'][] = $k;
                }
            }

            $opt['exportType'] = 'csv';
            $opt['arrFilter'][] = array('pid=?', Input::get('id'));
            $opt['strDestinationCharset'] = 'windows-1252';
            $GLOBALS['TL_HOOKS']['exportTable'][] = array('Markocupic\CalendarEventBookingBundle\ExportTableHook', 'exportBookingListHook');
            ExportTable::exportTable('tl_calendar_events_member', $opt);
            exit;
        }
    }
}
