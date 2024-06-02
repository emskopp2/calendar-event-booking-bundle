# API changes

## Version 5.* to 6.0

### Notification Center Tokens

The following Notification Center Tokens have been renamed:

| old                          | new                          |
|------------------------------|------------------------------|
| `member_unsubscribeHref`     | `cancelRegistrationUrl`      |
| `event_city`                 | `event_location`             |
| `event_enableDeregistration` | `event_enableUnsubscription` |

### Event Booking Module

The booking module has been completely revised and now works with checkout steps.
The partial templates of each step can be customized,
but must be registered in 'config/config.yaml`. More about this in the README.md.

### Database changes

The registration table has been renamed from `tl_calendar_events_member` to `tl_cebb_registration`
In addition, two tables have been added: `tl_cebb_order` and `tl_cebb_cart`

### `tl_cebb_registration.escorts`

Accompanying persons are no longer counted to the total number of participants.

### New field `tl_cebb_registration.quantity`

A new `quantity` field has been added to `tl_cebb_registration` so that multiple tickets can be purchased during registration.
If the `quantity` field is not displayed in the booking form, the field value is automatically set to 1 during the booking process.
**Important! The value of `quantity` is added to the total number of participants.**

| table                  | column name old                     | column name new             |
|------------------------|-------------------------------------|-----------------------------|
| `tl_calendar_events`   | `enableNotificationCenter`          | `enableBookingNotification` |
| `tl_calendar_events`   | `addBookingForm`                    | `enableBookingForm`         |
| `tl_calendar_events`   | `enableDeregistration`              | `enableUnsubscription`      |
| `tl_calendar_events`   | `eventBookingNotificationCenterIds` | `eventBookingNotification`  |
| `tl_calendar_events`   | `enableMultiBookingWithSameAddress` | `allowDuplicateEmail`       |
| `tl_cebb_registration` | `addedOn`                           | `dateAdded`                 |
| `tl_cebb_registration` | `regToken`                          | `uuid`                      |



