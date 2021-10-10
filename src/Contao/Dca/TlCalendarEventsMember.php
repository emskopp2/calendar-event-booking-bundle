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

namespace Markocupic\CalendarEventBookingBundle\Contao\Dca;

use Contao\Input;
use Markocupic\ExportTable\Config\Config;
use Markocupic\ExportTable\Export\ExportTable;

class TlCalendarEventsMember
{
    /**
     * @var ExportTable
     */
    private $exportTable;

    public function __construct(ExportTable $exportTable)
    {
        $this->exportTable = $exportTable;
    }

    /**
     * @throws \Exception
     */
    public function downloadRegistrationList(): void
    {
        // Download the registration list as a csv spreadsheet
        if ('downloadRegistrationList' === Input::get('act')) {
            $opt = [];

            // Add fields
            $arrSkip = ['bookingToken'];
            $opt['arrSelectedFields'] = [];

            foreach (array_keys($GLOBALS['TL_DCA']['tl_calendar_events_member']['fields']) as $k) {
                if (!\in_array($k, $arrSkip, true)) {
                    $opt['arrSelectedFields'][] = $k;
                }
            }

            $exportConfig = (new Config('tl_calendar_events_member'))
                ->setExportType('CSV')
                ->setFilter([['tl_calendar_events_member.pid=?'], [Input::get('id')]])
                ;

            $this->exportTable->run($exportConfig);

        }
    }
}
