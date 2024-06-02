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

use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\EventBookingCheckoutController;
use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\EventBookingListController;
use Markocupic\CalendarEventBookingBundle\Controller\FrontendModule\EventUnsubscribeController;
use Markocupic\CalendarEventBookingBundle\EventBooking\Booking\BookingState;

// Palettes
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventBookingCheckoutController::TYPE] = '{title_legend},name,headline,type,cebb_jumpToOnCheckoutCompletion;{checkout_legend},cebb_checkoutType;{form_legend},form;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventUnsubscribeController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][EventBookingListController::TYPE] = '{title_legend},name,headline,type;{config_legend},cebb_memberListAllowedBookingStates;{template_legend},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// Fields
$GLOBALS['TL_DCA']['tl_module']['fields']['cebb_memberListAllowedBookingStates'] = [
    'eval'      => ['multiple' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'options'   => BookingState::ALL,
    'sql'       => "varchar(512) NOT NULL default '".serialize([BookingState::STATE_CONFIRMED])."'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['cebb_checkoutType'] = [
    'eval'      => ['multiple' => false, 'tl_class' => 'w50'],
    'exclude'   => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'sql'       => "varchar(512) NOT NULL default 'default_checkout'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['cebb_jumpToOnCheckoutCompletion'] = [
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql'        => "int(10) unsigned NOT NULL default 0",
    'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
];


