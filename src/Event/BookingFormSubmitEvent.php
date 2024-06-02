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

namespace Markocupic\CalendarEventBookingBundle\Event;

use Contao\Form;
use Markocupic\CalendarEventBookingBundle\EventBooking\Config\EventConfig;
use Markocupic\CalendarEventBookingBundle\Model\CebbCartModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class BookingFormSubmitEvent extends Event
{
    public function __construct(
        private readonly Request $request,
        private readonly Form $form,
        private readonly CebbCartModel $cart,
        private readonly EventConfig $eventConfig,
        private array $arrExisting,
        private array $arrNew,
        private readonly array $arrSubmitted,
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function getCart(): CebbCartModel
    {
        return $this->cart;
    }

    public function getEventConfig(): EventConfig
    {
        return $this->eventConfig;
    }

    public function getExistingRegistrations(): array
    {
        return $this->arrExisting;
    }

    public function getNewRegistrations(): array
    {
        return $this->arrNew;
    }

    public function getSubmittedData(): array
    {
        return $this->arrSubmitted;
    }

    public function setExistingRegistrations(array $arrExisting): void
    {
        $this->arrExisting = $arrExisting;
    }

    public function setNewRegistrations(array $arrNew): void
    {
        $this->arrNew = $arrNew;
    }
}
