<?php

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

/**
 * Table tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar_event_booking_event_booking_module'] = '{title_legend},name,headline,type;{calendar_event_booking_form_legend},form;{calendar_event_booking_notification_center_legend:hide},enableNotificationCenter;{template_legend:hide},customTpl;{calendar_event_booking_config_legend:hide},calendar_event_booking_member_admin_member_groups;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar_event_booking_unsubscribe_from_event_module'] = '{title_legend},name,headline,type;{calendar_event_booking_notification_center_legend:hide},unsubscribeFromEventNotificationIds;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['calendar_event_booking_member_list_module'] = '{title_legend},name,headline,type;{template_legend},calendar_event_booking_member_list_partial_template,customTpl;{calendar_event_booking_config_legend:hide},calendar_event_booking_member_admin_member_groups;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

// unsubscribeFromEventNotificationIds
$GLOBALS['TL_DCA']['tl_module']['fields']['unsubscribeFromEventNotificationIds'] = array(
	'exclude'    => true,
	'search'     => true,
	'inputType'  => 'select',
	'foreignKey' => 'tl_nc_notification.title',
	'eval'       => array('mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'multiple' => true, 'tl_class' => 'clr'),
	'sql'        => "blob NULL",
	'relation'   => array('type' => 'hasOne', 'load' => 'lazy'),
);

// Member list partial template
$GLOBALS['TL_DCA']['tl_module']['fields']['calendar_event_booking_member_list_partial_template'] = array(
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => array('Markocupic\CalendarEventBookingBundle\Contao\Dca\TlModule', 'getCalendarEventBookingMemberListPartialTemplate'),
	'eval'             => array('tl_class' => 'w50'),
	'sql'              => "varchar(128) NOT NULL default 'calendar_event_booking_member_list_partial'",
);

// Get admin member groups
$GLOBALS['TL_DCA']['tl_module']['fields']['calendar_event_booking_member_admin_member_groups'] = array(
	'exclude'    => true,
	'inputType'  => 'select',
	'foreignKey' => 'tl_member_group.name',
	'eval'       => array('tl_class' => 'w50', 'multiple' => true, 'chosen' => true),
	'sql'        => "blob NULL",
	'relation'   => array('type' => 'belongsToMany', 'load' => 'lazy'),
);
