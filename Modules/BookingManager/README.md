# Booking Manager

## Interfaces
### Using Booking Manager in Repository Objects

Currently additional features are organised by ilObjectServiceSettingsGUI. You need to integrate this into your settings form initialisation and update procedure:

```
ilObjectServiceSettingsGUI::initServiceSettingsForm(
	$this->object->getId(),
	$form,
	array(
	[...],
		ilObjectServiceSettingsGUI::BOOKING
	)
);
```

```
// after $form initialisation
...
ilObjectServiceSettingsGUI::updateServiceSettingsForm(
	$this->object->getId(),
	$form,
	array(
		[...],
		ilObjectServiceSettingsGUI::BOOKING
	)
);
```


Furthermore you need to a tab to your UI which points to the class ilBookingGatewayGUI:

```
$tabs->addTab("booking", $lng->txt("..."),
	$ctrl->getLinkTargetByClass(array("ilbookinggatewaygui"), ""));
```

The same class needs to be integrated in your executeCommand control flow:

...

# General Documentation

This section documents the general concepts and structures of the Booking Manager. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.


* [Overview](#overview)
* [Booking Pool](#booking-pool)
* [Schedules](#schedules)
* [Booking Objects](#booking-objects)
* [Reservations](#reservations)
* [Participants](#participants)


## Overview

* A **booking pool** is a repository object that manages resources (booking objects) and their usage (reservations). There are two main types: Pools that are using schedules (e.g. for booking rooms) and pools without schedules (e.g. for booking term paper topics).
* A pool can hold multiple **schedules**. Schedules contain a set of weekly time **slots** where bookings for objects can be made, e.g. "Monday 10:00-11:00".
* A pool manages multiple **booking objects** (resources), e.g. a room or a set of beamers. A booking object uses either no schedule (depending on the pool type) or exactly one schedule.
* Users can make **reservations** for booking objects on specific dates that correspond to a time slot of the schedule attached to the booking object.
* Users that make reservations in a pool are called **participants**. It is also possible to manually add participants to the pool, that did not make any reservations yet.

## Booking Pool

A booking pool is the main entity for managing booking objects (resources) and their usage (reservations).

* **Code**: `Modules/BookingManager`
* **DB Tables**: `booking_settings`

### Properties

* **Fixed Schedule** or **No Schedule**: There are two main types of booking pools, those which are using schedules (e.g. for booking rooms) and those who don't (e.g. for selection of term paper topics). (`booking_settings.schedule_type`)
* **Public Reservations**: The list of reservations can be made publicly available for all users with read permission. (`booking_settings.public_log`)
* **Overall Limit of Bookings** (No schedule only): Limits the maximum number of bookings a single user can do in this pool. (`booking_settings.ovlimit`)
* **Default Period for Reservation List** (Fixed schedule only): Sets the default period of the filter in the reservation list view. (`booking_settings.rsv_filter_period`)
* **Reminder**: A reminder can be activated (`booking_settings.reminder_status`) to remind users of their upcoming bookings. The period before users are reminded can be set (`booking_settings.reminder_day`). A cronjob stores the timestamp for last execution (`booking_settings.last_remind_ts`).

*Deprecated*

* `booking_settings.slots_no` ?

## Schedules

* **Code**: `Modules/BookingManager/Schedule`
* **DB Tables**: `booking_schedule`, `booking_schedule_slot`

### Properties

...

## Booking Objects

* **Code**: `Modules/BookingManager/Objects`
* **DB Tables**: `booking_objects`

### Properties

...

## Reservations

* **Code**: `Modules/BookingManager/Reservations`
* **DB Tables**: `booking_reservation`

### Properties

...

## Participants

* **Code**: `Modules/BookingManager/Participants`
* **DB Tables**: `booking_member`

### Properties

...
