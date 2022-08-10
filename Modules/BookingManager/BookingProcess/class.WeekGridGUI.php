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
class WeekGridGUI
{
    protected int $week_start;
    protected \ilDate $seed;
    protected string $seed_str;
    protected int $time_format;
    protected \ilLanguage $lng;
    protected int $day_end;
    protected int $day_start;
    protected string $title;
    protected array $entries;

    public function __construct()
    {
        global $DIC;

        $this->title = "Test";
        $this->form_action = "#";
        $this->lng = $DIC->language();
        $this->day_start = 8;
        $this->day_end = 19;
        $this->time_format = \ilCalendarSettings::TIME_FORMAT_24;
        $this->seed_str = "";
        $this->seed = ($this->seed_str !== "")
            ? new \ilDate($this->seed_str, IL_CAL_DATE)
            : new \ilDate(time(), IL_CAL_UNIX);
        $this->week_start = \ilCalendarSettings::WEEK_START_MONDAY;
    }

    public function render() : string
    {
        $mytpl = new \ilTemplate(
            'tpl.week_grid.html',
            true,
            true,
            'Modules/BookingManager/BookingProcess'
        );

        $mytpl->setVariable('TXT_TITLE', $this->lng->txt('book_reservation_title'));
        $mytpl->setVariable('TXT_INFO', $this->lng->txt('book_reservation_fix_info'));
        $mytpl->setVariable('TXT_OBJECT', $this->title);

        $morning_aggr = $this->day_start;
        $evening_aggr = $this->day_end;
        $hours = array();
        for ($i = $morning_aggr;$i <= $evening_aggr;$i++) {
            switch ($this->time_format) {
                case \ilCalendarSettings::TIME_FORMAT_24:
                    $hours[$i] = "";
                    if ($morning_aggr > 0 && $i === $morning_aggr) {
                        $hours[$i] = sprintf('%02d:00', 0) . "-";
                    }
                    $hours[$i] .= sprintf('%02d:00', $i);
                    if ($evening_aggr < 23 && $i === $evening_aggr) {
                        $hours[$i] .= "-" . sprintf('%02d:00', 23);
                    }
                    break;

                case \ilCalendarSettings::TIME_FORMAT_12:
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

        $week_start = $this->week_start;

        /** @var \ilDateTime $date */
        foreach (\ilCalendarUtil::_buildWeekDayList($this->seed, $week_start)->get() as $date) {
            $date_info = $date->get(IL_CAL_FKT_GETDATE, '', 'UTC');

            $mytpl->setCurrentBlock('weekdays');
            $mytpl->setVariable('TXT_WEEKDAY', \ilCalendarUtil::_numericDayToString((int) $date_info['wday']));
            $mytpl->setVariable('TXT_DATE', $date_info['mday'] . ' ' . \ilCalendarUtil::_numericMonthToString($date_info['mon']));
            $mytpl->parseCurrentBlock();
        }

        var_dump($hours);
        exit;
        foreach ($hours as $hour => $days) {
            $caption = $days;

            foreach (\ilCalendarUtil::_buildWeekDayList($this->seed, $week_start)->get() as $date) {
                $this->renderCell($date, $hour);
                $mytpl->setCurrentBlock('dates');
                $mytpl->setVariable('CONTENT', '&nbsp;');
                $mytpl->parseCurrentBlock();
            }

            $mytpl->setCurrentBlock('slots');
            $mytpl->setVariable('TXT_HOUR', $caption);
            $mytpl->parseCurrentBlock();
        }
        return $mytpl->get();
    }
}
