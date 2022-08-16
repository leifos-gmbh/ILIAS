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
class WeekGUI
{
    /**
     * @var int[]
     */
    protected array $obj_ids = [];

    public function __construct(
        array $obj_ids
    ) {
        $this->obj_ids = $obj_ids;
    }

    public function getHTML() : string
    {
        $start1 = new \ilDateTime("2022-08-17 10:00:00", IL_CAL_DATETIME);
        $end1 = new \ilDateTime("2022-08-17 11:00:00", IL_CAL_DATETIME);

        $entry1 = new WeekGridEntry(
            $start1->get(IL_CAL_UNIX),
            $end1->get(IL_CAL_UNIX),
            "Moin 1"
        );

        $start2 = new \ilDateTime("2022-08-19 12:00:00", IL_CAL_DATETIME);
        $end2 = new \ilDateTime("2022-08-19 13:00:00", IL_CAL_DATETIME);

        $entry2 = new WeekGridEntry(
            $start2->get(IL_CAL_UNIX),
            $end2->get(IL_CAL_UNIX),
            "Moin 2"
        );

        $week_widget = new WeekGridGUI([$entry1, $entry2]);
        return $week_widget->render();
    }

    protected function renderSlots(
        ilBookingSchedule $schedule,
        array $object_ids,
        string $title
    ) : string {
        $ilUser = $this->user;

        // fix
        if (!$schedule->getRaster()) {
            $mytpl = new ilTemplate('tpl.booking_reservation_fix.html', true, true, 'Modules/BookingManager');

            $mytpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this));
            $mytpl->setVariable('TXT_TITLE', $this->lng->txt('book_reservation_title'));
            $mytpl->setVariable('TXT_INFO', $this->lng->txt('book_reservation_fix_info'));
            $mytpl->setVariable('TXT_OBJECT', $title);
            $mytpl->setVariable('TXT_CMD_BOOK', $this->lng->txt('book_confirm_booking'));
            $mytpl->setVariable('TXT_CMD_CANCEL', $this->lng->txt('cancel'));

            $user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());

            $morning_aggr = $user_settings->getDayStart();
            $evening_aggr = $user_settings->getDayEnd();
            $hours = array();
            for ($i = $morning_aggr;$i <= $evening_aggr;$i++) {
                switch ($user_settings->getTimeFormat()) {
                    case ilCalendarSettings::TIME_FORMAT_24:
                        $hours[$i] = "";
                        if ($morning_aggr > 0 && $i === $morning_aggr) {
                            $hours[$i] = sprintf('%02d:00', 0) . "-";
                        }
                        $hours[$i] .= sprintf('%02d:00', $i);
                        if ($evening_aggr < 23 && $i === $evening_aggr) {
                            $hours[$i] .= "-" . sprintf('%02d:00', 23);
                        }
                        break;

                    case ilCalendarSettings::TIME_FORMAT_12:
                        if ($morning_aggr > 0 && $i === $morning_aggr) {
                            $hours[$i] = date('h a', mktime(0, 0, 0, 1, 1, 2000)) . "-";
                        }
                        $hours[$i] .= date('h a', mktime($i, 0, 0, 1, 1, 2000));
                        if ($evening_aggr < 23 && $i === $evening_aggr) {
                            $hours[$i] .= "-" . date('h a', mktime(23, 0, 0, 1, 1, 2000));
                        }
                        break;
                }
            }

            if ($this->seed !== "") {
                $find_first_open = false;
                $seed = new ilDate($this->seed, IL_CAL_DATE);
            } else {
                $find_first_open = true;
                $seed = ($this->sseed !== "")
                    ? new ilDate($this->sseed, IL_CAL_DATE)
                    : new ilDate(time(), IL_CAL_UNIX);
            }

            $week_start = $user_settings->getWeekStart();

            $dates = array();
            if (!$find_first_open) {
                $this->buildDatesBySchedule($week_start, $hours, $schedule, $object_ids, $seed, $dates);
            } else {

                //loop for 1 week
                $has_open_slot = $this->buildDatesBySchedule($week_start, $hours, $schedule, $object_ids, $seed, $dates);

                // find first open slot
                if (!$has_open_slot) {
                    // 1 year is limit for search
                    $limit = clone($seed);
                    $limit->increment(ilDateTime::YEAR, 1);
                    $limit = $limit->get(IL_CAL_UNIX);

                    while (!$has_open_slot && $seed->get(IL_CAL_UNIX) < $limit) {
                        $seed->increment(ilDateTime::WEEK, 1);

                        $dates = array();
                        $has_open_slot = $this->buildDatesBySchedule($week_start, $hours, $schedule, $object_ids, $seed, $dates);
                    }
                }
            }

            $navigation = new ilCalendarHeaderNavigationGUI($this, $seed, ilDateTime::WEEK, 'book');
            $mytpl->setVariable('NAVIGATION', $navigation->getHTML());

            /** @var ilDateTime $date */
            foreach (ilCalendarUtil::_buildWeekDayList($seed, $week_start)->get() as $date) {
                $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');

                $mytpl->setCurrentBlock('weekdays');
                $mytpl->setVariable('TXT_WEEKDAY', ilCalendarUtil::_numericDayToString((int) $date_info['wday']));
                $mytpl->setVariable('TXT_DATE', $date_info['mday'] . ' ' . ilCalendarUtil::_numericMonthToString($date_info['mon']));
                $mytpl->parseCurrentBlock();
            }

            $color = array();
            $all = ilCalendarAppointmentColors::_getColorsByType('crs');
            for ($loop = 0; $loop < 7; $loop++) {
                $col = $all[$loop];
                $fnt = ilCalendarUtil::calculateFontColor($col);
                $color[$loop + 1] = 'border-bottom: 1px solid ' . $col . '; background-color: ' . $col . '; color: ' . $fnt;
            }

            $counter = 0;
            foreach ($dates as $hour => $days) {
                $caption = $days;
                $caption = array_shift($caption);

                for ($loop = 1; $loop < 8; $loop++) {
                    if (!isset($days[$loop])) {
                        $mytpl->setCurrentBlock('dates');
                        $mytpl->setVariable('DUMMY', '&nbsp;');
                    } elseif (isset($days[$loop]['captions'])) {
                        foreach ($days[$loop]['captions'] as $slot_id => $slot_caption) {
                            $mytpl->setCurrentBlock('choice');
                            $mytpl->setVariable('TXT_DATE', $slot_caption);
                            $mytpl->setVariable('VALUE_DATE', $slot_id);
                            $mytpl->setVariable('DATE_COLOR', $color[$loop]);
                            $mytpl->setVariable(
                                'TXT_AVAILABLE',
                                sprintf(
                                    $this->lng->txt('book_reservation_available'),
                                    $days[$loop]['available'][$slot_id]
                                )
                            );
                            $mytpl->parseCurrentBlock();
                        }

                        $mytpl->setCurrentBlock('dates');
                        $mytpl->setVariable('DUMMY', '');
                    } elseif (isset($days[$loop]['in_slot'])) {
                        $mytpl->setCurrentBlock('dates');
                        $mytpl->setVariable('DATE_COLOR', $color[$loop]);
                    } else {
                        $mytpl->setCurrentBlock('dates');
                        $mytpl->setVariable('DUMMY', '&nbsp;');
                    }
                    $mytpl->parseCurrentBlock();
                }

                $mytpl->setCurrentBlock('slots');
                $mytpl->setVariable('TXT_HOUR', $caption);
                if ($counter % 2) {
                    $mytpl->setVariable('CSS_ROW', 'tblrow1');
                } else {
                    $mytpl->setVariable('CSS_ROW', 'tblrow2');
                }
                $mytpl->parseCurrentBlock();

                $counter++;
            }
            return $mytpl->get();
        }

        return "";
    }
}
