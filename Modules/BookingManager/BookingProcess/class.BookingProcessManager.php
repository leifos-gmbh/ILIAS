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
    protected int $time_format;
    public function __construct(
        int $time_format = \ilCalendarSettings::TIME_FORMAT_24
    ) {
        $this->time_format = $time_format;
    }

    protected function buildDatesBySchedule(
        int $week_start,
        array $hours,
        \ilBookingSchedule $schedule,
        array $object_ids,
        \ilDate $seed,
        array &$dates
    ) : bool {
        $map = array('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su');
        $definition = $schedule->getDefinition();
        $av_from = ($schedule->getAvailabilityFrom() && !$schedule->getAvailabilityFrom()->isNull())
            ? $schedule->getAvailabilityFrom()->get(IL_CAL_DATE)
            : null;
        $av_to = ($schedule->getAvailabilityTo() && !$schedule->getAvailabilityTo()->isNull())
            ? $schedule->getAvailabilityTo()->get(IL_CAL_DATE)
            : null;

        $has_open_slot = false;
        // iterate weekdays...
        /** @var \ilDateTime $date */
        foreach (\ilCalendarUtil::_buildWeekDayList($seed, $week_start)->get() as $date) {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');

            #24045 and #24936
            if ($av_from || $av_to) {
                $today = $date->get(IL_CAL_DATE);

                if ($av_from && $av_from > $today) {
                    continue;
                }

                if ($av_to && $av_to < $today) {
                    continue;
                }
            }

            // get all slots of current weekday (e.g. "mo")
            $slots = array();
            if (isset($definition[$map[$date_info['isoday'] - 1]])) {
                $slots = array();
                foreach ($definition[$map[$date_info['isoday'] - 1]] as $slot) {
                    $slot = explode('-', $slot);
                    $slots[] = array('from' => str_replace(':', '', $slot[0]),
                                     'to' => str_replace(':', '', $slot[1]));
                }
            }

            $slot_captions = array();
            // iterate all $hours (presentation "rows") of the day
            foreach ($hours as $hour => $period) {
                $dates[$hour][0] = $period;

                // here e.g. $hour = 8 and $period = "00:00-08:00"

                $period = explode("-", $period);

                // #13738
                if ($this->time_format === \ilCalendarSettings::TIME_FORMAT_12) {
                    $period[0] = date("H", strtotime($period[0]));
                    if (count($period) === 2) {
                        $period[1] = date("H", strtotime($period[1]));
                    }
                }

                $period_from = (int) substr($period[0], 0, 2) . "00";
                if (count($period) === 1) {
                    $period_to = (int) substr($period[0], 0, 2) . "59";
                } else {
                    $period_to = (int) substr($period[1], 0, 2) . "59";
                }

                $column = $date_info['isoday'];
                if (!$week_start) {
                    if ($column < 7) {
                        $column++;
                    } else {
                        $column = 1;
                    }
                }

                if (count($slots)) {
                    $in = false;
                    foreach ($slots as $slot) {

                        // concrete slot times here, not rastered
                        $slot_from = mktime(substr($slot['from'], 0, 2), substr($slot['from'], 2, 2), 0, $date_info["mon"], $date_info["mday"], $date_info["year"]);
                        $slot_to = mktime(substr($slot['to'], 0, 2), substr($slot['to'], 2, 2), 0, $date_info["mon"], $date_info["mday"], $date_info["year"]);

                        // always single object, we can sum up
                        // (currently object_ids has always exactly one item)
                        $nr_available = \ilBookingReservation::getAvailableObject($object_ids, $slot_from, $slot_to - 1, false, true);

                        // any objects available?
                        if (!array_sum($nr_available)) {
                            continue;
                        }

                        // check deadline
                        if ($schedule->getDeadline() >= 0) {
                            // 0-n hours before slots begins
                            if ($slot_from < (time() + $schedule->getDeadline() * 60 * 60)) {
                                continue;
                            }
                        } elseif ($slot_to < time()) {
                            continue;
                        }

                        // is slot active in current hour?
                        if ((int) $slot['from'] < $period_to && (int) $slot['to'] > $period_from) {
                            $from = ilDatePresentation::formatDate(new ilDateTime($slot_from, IL_CAL_UNIX));
                            $from_a = explode(' ', $from);
                            $from = array_pop($from_a);
                            $to = ilDatePresentation::formatDate(new ilDateTime($slot_to, IL_CAL_UNIX));
                            $to_a = explode(' ', $to);
                            $to = array_pop($to_a);

                            // show caption (first hour) of slot
                            $id = $slot_from . '_' . $slot_to;
                            if (!in_array($id, $slot_captions)) {
                                $dates[$hour][$column]['captions'][$id] = $from . '-' . $to;
                                $dates[$hour][$column]['available'][$id] = array_sum($nr_available);
                                $slot_captions[] = $id;
                            }

                            $in = true;
                        }
                    }
                    // (any) active slot
                    if ($in) {
                        $has_open_slot = true;
                        $dates[$hour][$column]['in_slot'] = true;
                    }
                }
            }
        }

        return $has_open_slot;
    }
}
