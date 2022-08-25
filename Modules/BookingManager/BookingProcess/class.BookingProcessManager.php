<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class BookingProcessManager
{
    public function __construct()
    {
    }

    /**
     * Get missing availability of an object for
     * a given weekly recurrence, object id, starting slot and requested nr
     */
    public function getRecurrenceMissingAvailability(
        int $obj_id,
        int $slot_from,
        int $slot_to,
        int $recurrence_weeks,
        int $requested_nr,
        \ilDate $until
    ) : array {
        $end = $until->get(IL_CAL_UNIX);
        $cut = 0;
        $cycle = $recurrence_weeks * 7;
        $booked_out_slots = [];
        $check_slot_from = $slot_from;
        while ($cut < 1000 && $check_slot_from <= $end) {
            $check_slot_from = $this->addDaysStamp($slot_from, $cycle * $cut);
            $check_slot_to = $this->addDaysStamp($slot_to, $cycle * $cut);
            $available = \ilBookingReservation::getAvailableObject(array($obj_id), $check_slot_from, $check_slot_to, false, true);
            $available = $available[$obj_id];
            $available = 0;
            if ($available < $requested_nr) {
                $booked_out_slots[] = [
                    "from" => $check_slot_from,
                    "to" => $check_slot_to,
                    "missing" => $requested_nr - $available
                ];
            }
            $cut++;
        }
        return $booked_out_slots;
    }

    protected function addDaysDate(
        string $a_date,
        int $a_days
    ) : string {
        $date = date_parse($a_date);
        $stamp = mktime(0, 0, 1, $date["month"], $date["day"] + $a_days, $date["year"]);
        return date("Y-m-d", $stamp);
    }

    protected function addDaysStamp(
        int $a_stamp,
        int $a_days
    ) : int {
        $date = getdate($a_stamp);
        return mktime(
            $date["hours"],
            $date["minutes"],
            $date["seconds"],
            $date["mon"],
            $date["mday"] + $a_days,
            $date["year"]
        );
    }

    /**
     * Book object for date
     * @return int reservation id
     * @throws ilDateTimeException
     */
    public function bookSingle(
        int $object_id,
        int $user_to_book,
        int $assigner_id = 0,
        int $context_obj_id = 0,
        int $a_from = null,
        int $a_to = null,
        int $a_group_id = null
    ) : int {
        $reservation = new \ilBookingReservation();
        $reservation->setObjectId($object_id);
        $reservation->setUserId($user_to_book);
        $reservation->setAssignerId($assigner_id);
        $reservation->setFrom((int) $a_from);
        $reservation->setTo((int) $a_to);
        $reservation->setGroupId((int) $a_group_id);
        $reservation->setContextObjId($context_obj_id);
        $reservation->save();

        if ($a_from) {
            $this->lng->loadLanguageModule('dateplaner');
            $def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_BOOK, $this->user_id_to_book, $this->lng->txt('cal_ch_personal_book'), true);

            $object = new ilBookingObject($a_object_id);

            $entry = new ilCalendarEntry();
            $entry->setStart(new ilDateTime($a_from, IL_CAL_UNIX));
            $entry->setEnd(new ilDateTime($a_to, IL_CAL_UNIX));
            $entry->setTitle($this->lng->txt('book_cal_entry') . ' ' . $object->getTitle());
            $entry->setContextId($reservation->getId());
            $entry->save();

            $assignment = new ilCalendarCategoryAssignments($entry->getEntryId());
            if ($def_cat !== null) {
                $assignment->addAssignment($def_cat->getCategoryID());
            }
        }

        return $reservation->getId();
    }
}
