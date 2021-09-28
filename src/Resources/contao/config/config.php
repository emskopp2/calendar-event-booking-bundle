<?php

/*
 * This file is part of Calendar Event Booking Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/calendar-event-booking-bundle
 */

use Markocupic\CalendarEventBookingBundle\Listener\ContaoHooks\CompileFormFields;
use Markocupic\CalendarEventBookingBundle\Listener\ContaoHooks\LoadFormField;
use Markocupic\CalendarEventBookingBundle\Listener\ContaoHooks\PrepareFormData;
use Markocupic\CalendarEventBookingBundle\Listener\ContaoHooks\ProcessFormData;
use Markocupic\CalendarEventBookingBundle\Listener\ContaoHooks\ValidateFormField;
use Markocupic\CalendarEventBookingBundle\Model\CalendarEventsMemberModel;

// Add child table tl_calendar_events_member to tl_calendar_events
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_calendar_events_member';

if (TL_MODE === 'BE')
{
	// Add Backend CSS
	$GLOBALS['TL_CSS'][] = 'bundles/markocupiccalendareventbooking/css/be_stylesheet.css';
}

// Register custom models
$GLOBALS['TL_MODELS']['tl_calendar_events_member'] = CalendarEventsMemberModel::class;

/**
 * Notification center
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['calendar-event-booking-bundle'] = array(
	'booking-notification'           => array(
		'email_sender_name'    => array('organizer_name'),
		'email_sender_address' => array('organizer_email', 'admin_email'),
		'recipients'           => array('organizer_email', 'member_email', 'admin_email'),
		'email_recipient_cc'   => array('organizer_email', 'member_email', 'admin_email'),
		'email_recipient_bcc'  => array('organizer_email', 'member_email', 'admin_email'),
		'email_replyTo'        => array('organizer_email', 'member_email', 'admin_email'),
		'email_subject'        => array('event_*', 'event_title', 'event_unsubscribeLimitTstamp', 'event_unsubscribeHref', 'member_*', 'member_dateOfBirth', 'member_salutation', 'organizer_*', 'organizer_name', 'organizer_email', 'admin_email'),
		'email_text'           => array('event_*', 'event_title', 'event_unsubscribeLimitTstamp', 'event_unsubscribeHref', 'member_*', 'member_dateOfBirth', 'member_salutation', 'organizer_*', 'organizer_name', 'organizer_email', 'admin_email'),
		'email_html'           => array('event_*', 'event_title', 'event_unsubscribeLimitTstamp', 'event_unsubscribeHref', 'member_*', 'member_dateOfBirth', 'member_salutation', 'organizer_*', 'organizer_name', 'organizer_email', 'admin_email'),
	),
	'event-unsubscribe-notification' => array(
		'email_sender_name'    => array('organizer_name'),
		'email_sender_address' => array('organizer_email', 'admin_email'),
		'recipients'           => array('organizer_email', 'member_email', 'admin_email'),
		'email_recipient_cc'   => array('organizer_email', 'member_email', 'admin_email'),
		'email_recipient_bcc'  => array('organizer_email', 'member_email', 'admin_email'),
		'email_replyTo'        => array('organizer_email', 'member_email', 'admin_email'),
		'email_subject'        => array('event_*', 'event_title', 'event_unsubscribeLimitTstamp', 'event_unsubscribeHref', 'member_*', 'member_dateOfBirth', 'member_salutation', 'organizer_*', 'organizer_name', 'organizer_email', 'admin_email'),
		'email_text'           => array('event_*', 'event_title', 'event_unsubscribeLimitTstamp', 'event_unsubscribeHref', 'member_*', 'member_dateOfBirth', 'member_salutation', 'organizer_*', 'organizer_name', 'organizer_email', 'admin_email'),
		'email_html'           => array('event_*', 'event_title', 'event_unsubscribeLimitTstamp', 'event_unsubscribeHref', 'member_*', 'member_dateOfBirth', 'member_salutation', 'organizer_*', 'organizer_name', 'organizer_email', 'admin_email'),
	),
);
