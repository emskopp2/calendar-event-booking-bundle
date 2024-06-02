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

namespace Markocupic\CalendarEventBookingBundle\EventListener\ContaoHooks\LoadFormField;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Date;
use Contao\Form;
use Contao\Widget;
use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventConfig;
use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventFactory;
use Markocupic\CalendarEventBookingBundle\EventBooking\EventRegistration\EventRegistration;
use Markocupic\CalendarEventBookingBundle\EventListener\ContaoHooks\AbstractHook;

#[AsHook(FormatValues::HOOK, priority: 1000)]
final class FormatValues extends AbstractHook
{
    public const HOOK = 'loadFormField';

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly EventFactory $eventFactory,
        private readonly EventRegistration $eventRegistration,
    ) {
    }

    public function __invoke(Widget $objWidget, string $formId, array $arrForm, Form $objForm): Widget
    {
        if (!self::isEnabled()) {
            return $objWidget;
        }

        $dateAdapter = $this->framework->getAdapter(Date::class);
        $configAdapter = $this->framework->getAdapter(Config::class);
        $controllerAdapter = $this->framework->getAdapter(Controller::class);

        if ($objForm->isCalendarEventBookingForm) {
            if (null === ($event = EventConfig::getEventFromRequest())) {
                return $objWidget;
            }

            $eventConfig = $this->eventFactory->create($event);

            if (!$eventConfig->get('enableBookingForm') || !$eventConfig->get('published')) {
                return $objWidget;
            }

            // Load DCA
            $controllerAdapter->loadDataContainer($this->eventRegistration->getTable());
            $dca = $GLOBALS['TL_DCA'][$this->eventRegistration->getTable()];

            // Convert timestamps to formatted date strings
            if (isset($dca['fields'][$objWidget->name]['eval']['rgxp'])) {
                if (!empty($objWidget->value)) {
                    if (is_numeric($objWidget->value)) {
                        if ('date' === $dca['fields'][$objWidget->name]['eval']['rgxp']) {
                            $objWidget->value = $dateAdapter->parse($configAdapter->get('dateFormat'), $objWidget->value);
                        }

                        if ('datim' === $dca['fields'][$objWidget->name]['eval']['rgxp']) {
                            $objWidget->value = $dateAdapter->parse($configAdapter->get('datimFormat'), $objWidget->value);
                        }
                    }
                }
            }

            // Fit select menu to max escorts per member
            if ('escorts' === $objWidget->name) {
                $max = $eventConfig->get('maxEscortsPerMember');

                if ($max > 0) {
                    $opt = [];

                    for ($i = 0; $i <= $max; ++$i) {
                        $opt[] = [
                            'value' => $i,
                            'label' => $i,
                        ];
                    }
                    $objWidget->options = serialize($opt);
                }
            }

            if ('quantity' === $objWidget->name) {
                $max = $eventConfig->get('maxQuantityPerRegistration');

                if ($max > 0) {
                    $opt = [];

                    // Quantity should at least be 1
                    for ($i = 1; $i <= $max; ++$i) {
                        $opt[] = [
                            'value' => $i,
                            'label' => $i,
                        ];
                    }
                    $objWidget->options = serialize($opt);
                }
            }
        }

        return $objWidget;
    }
}
