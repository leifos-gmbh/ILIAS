<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Calendar\Recurrence;

use ilLanguage;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ilCalendarUserSettings;
use ilCalendarUtil;
use ilCalendarRecurrence;
use ilDate;

class InputBuilderImpl implements InputBuilder
{
    protected const string RULE = 'rule';
    protected const string END = 'end';
    protected const string NO_RECURRENCE = 'none';
    protected const string DAILY = 'daily';
    protected const string WEEKLY = 'weekly';
    protected const string MONTHLY_BY_DAY = 'monthly_by_day';
    protected const string MONTHLY_BY_DATE = 'monthly_by_date';
    protected const string YEARLY_BY_DAY = 'yearly_by_day';
    protected const string YEARLY_BY_DATE = 'yearly_by_date';
    protected const string INTERVAL = 'interval';
    protected const string MONTH = 'month';
    protected const string WEEK = 'week';
    protected const string DAY = 'day';
    protected const string DAY_OF_MONTH = 'day_of_month';
    protected const string MONDAY = 'MO';
    protected const string TUESDAY = 'TU';
    protected const string WEDNESDAY = 'WE';
    protected const string THURSDAY = 'TH';
    protected const string FRIDAY = 'FR';
    protected const string SATURDAY = 'SA';
    protected const string SUNDAY = 'SU';
    protected const int FIRST = 1;
    protected const int SECOND = 2;
    protected const int THIRD = 3;
    protected const int FOURTH = 4;
    protected const int FIFTH = 5;
    protected const int LAST = -1;
    protected const int JANUARY = 1;
    protected const int FEBRUARY = 2;
    protected const int MARCH = 3;
    protected const int APRIL = 4;
    protected const int MAY = 5;
    protected const int JUNE = 6;
    protected const int JULY = 7;
    protected const int AUGUST = 8;
    protected const int SEPTEMBER = 9;
    protected const int OCTOBER = 10;
    protected const int NOVEMBER = 11;
    protected const int DECEMBER = 12;
    protected const string NO_UNTIL = 'no_until';
    protected const string COUNT = 'count';
    protected const string UNTIL_COUNT = 'until_count';
    protected const string END_DATE = 'end_date';
    protected const string UNTIL_END_DATE = 'until_end_date';

    protected bool $unlimited_recurrences = true;
    protected bool $daily = true;
    protected bool $weekly = true;
    protected bool $monthly = true;
    protected bool $yearly = true;

    public function __construct(
        protected ilCalendarRecurrence $recurrence,
        protected UIFactory $ui_factory,
        protected Refinery $refinery,
        protected ilLanguage $lng,
        protected ilCalendarUserSettings $user_settings
    ) {
    }

    public function withoutUnlimitedRecurrences(bool $without = true): InputBuilder
    {
        $clone = clone $this;
        $clone->unlimited_recurrences = !$without;
        return $clone;
    }

    public function withoutDaily(bool $without = true): InputBuilder
    {
        $clone = clone $this;
        $clone->daily = !$without;
        return $clone;
    }

    public function withoutWeekly(bool $without = true): InputBuilder
    {
        $clone = clone $this;
        $clone->weekly = !$without;
        return $clone;
    }

    public function withoutMonthly(bool $without = true): InputBuilder
    {
        $clone = clone $this;
        $clone->monthly = !$without;
        return $clone;
    }

    public function withoutYearly(bool $without = true): InputBuilder
    {
        $clone = clone $this;
        $clone->yearly = !$without;
        return $clone;
    }

    public function hasUnlimitedRecurrences(): bool
    {
        return $this->unlimited_recurrences;
    }

    public function hasDaily(): bool
    {
        return $this->daily;
    }

    public function hasWeekly(): bool
    {
        return $this->weekly;
    }

    public function hasMonthly(): bool
    {
        return $this->monthly;
    }

    public function hasYearly(): bool
    {
        return $this->yearly;
    }

    public function get(): Group
    {
        $rule_input = $this->getRuleInput();
        $end_input = $this->getEndInput();
        $output_trafo = $this->getOutputTransformation();
        return $this->ui_factory->input()->field()->group([
            self::RULE => $rule_input,
            self::END => $end_input
        ])->withAdditionalTransformation($output_trafo);
    }

    protected function getRuleInput(): Input
    {
        $groups = [];
        $groups[self::NO_RECURRENCE] = $this->ui_factory->input()->field()->group(
            [],
            $this->lng->txt('cal_no_recurrence')
        );
        if ($this->hasDaily()) {
            $groups[self::DAILY] = $this->getDailyGroup();
        }
        if ($this->hasWeekly()) {
            $groups[self::WEEKLY] = $this->getWeeklyGroup();
        }
        if ($this->hasMonthly()) {
            $groups[self::MONTHLY_BY_DAY] = $this->getMonthlyByDayGroup();
            $groups[self::MONTHLY_BY_DATE] = $this->getMonthlyByDateGroup();
        }
        if ($this->hasYearly()) {
            $groups[self::YEARLY_BY_DAY] = $this->getYearlyByDayGroup();
            $groups[self::YEARLY_BY_DATE] = $this->getYearlyByDateGroup();
        }
        return $this->ui_factory->input()->field()->switchableGroup(
            $groups,
            $this->lng->txt('cal_recurrences')
        )->withValue(self::NO_RECURRENCE);
    }

    protected function getDailyGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [self::INTERVAL => $this->getIntervalInput('cal_recurrence_day_interval')],
            $this->lng->txt('cal_daily')
        );
    }

    protected function getWeeklyGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput('cal_recurrence_week_interval'),
                self::DAY => $this->getDayInput()
            ],
            $this->lng->txt('cal_weekly')
        );
    }

    protected function getMonthlyByDayGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput('Every x Month(s)'),
                self::WEEK => $this->getWeekInput(),
                self::DAY => $this->getDayInput()
            ],
            $this->lng->txt('cal_monthly_by_day')
        );
    }

    protected function getMonthlyByDateGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput('Every x Month(s)'),
                self::DAY_OF_MONTH => $this->getDayOfMonthInput()
            ],
            $this->lng->txt('cal_monthly_by_date')
        );
    }

    protected function getYearlyByDayGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput('Every x Years(s)'),
                self::MONTH => $this->getMonthInput(),
                self::WEEK => $this->getWeekInput(),
                self::DAY => $this->getDayInput()
            ],
            $this->lng->txt('cal_yearly_by_day')
        );
    }

    protected function getYearlyByDateGroup(): Group
    {
        return $this->ui_factory->input()->field()->group(
            [
                self::INTERVAL => $this->getIntervalInput('Every x Year(s)'),
                self::MONTH => $this->getMonthInput(),
                self::DAY_OF_MONTH => $this->getDayOfMonthInput()
            ],
            $this->lng->txt('cal_yearly_by_date')
        );
    }

    protected function getEndInput(): Input
    {
        $groups = [];

        if ($this->unlimited_recurrences) {
            $groups[self::NO_UNTIL] = $this->ui_factory->input()->field()->group(
                [],
                $this->lng->txt('cal_no_ending')
            );
        }

        $count = $this->ui_factory->input()->field()->numeric($this->lng->txt('cal_rec_count'))
                         ->withValue(1)
                         ->withRequired(true)
                         ->withAdditionalTransformation(
                             $this->refinery->in()->series([
                                 $this->refinery->int()->isGreaterThanOrEqual(1),
                                 $this->refinery->int()->isLessThanOrEqual(100),
                             ])
                         );
        $groups[self::UNTIL_COUNT] = $this->ui_factory->input()->field()->group(
            [self::COUNT => $count],
            $this->lng->txt('cal_rec_until_count')
        );

        $end_date = $this->ui_factory->input()->field()->dateTime(
            $this->lng->txt('cal_rec_end_date'),
            $this->lng->txt('cal_rec_end_date_info')
        )->withUseTime(false)
         ->withRequired(true);
        $groups[self::UNTIL_END_DATE] = $this->ui_factory->input()->field()->group(
            [self::END_DATE => $end_date],
            $this->lng->txt('cal_rec_until_end_date')
        );

        return $this->ui_factory->input()->field()->switchableGroup(
            $groups,
            $this->lng->txt('cal_rec_until'),
            $this->lng->txt('cal_rec_until_info')
        )->withValue(self::NO_UNTIL);
    }

    protected function getIntervalInput(string $label): Input
    {
        return $this->ui_factory->input()->field()->numeric($label)
                                ->withValue(1)
                                ->withRequired(true)
                                ->withAdditionalTransformation(
                                    $this->refinery->int()->isGreaterThanOrEqual(1)
                                );
    }

    protected function getDayInput(): Input
    {
        $days = [
            0 => self::SUNDAY,
            1 => self::MONDAY,
            2 => self::TUESDAY,
            3 => self::WEDNESDAY,
            4 => self::THURSDAY,
            5 => self::FRIDAY,
            6 => self::SATURDAY,
            7 => self::SUNDAY
        ];
        $options = [];
        for ($i = $this->user_settings->getWeekStart(); $i < 7 + $this->user_settings->getWeekStart(); $i++) {
            $options[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
        }
        return $this->ui_factory->input()->field()->multiSelect(
            $this->lng->txt('cal_day_s'),
            $options
        )->withRequired(true);
    }

    protected function getWeekInput(): Input
    {
        $options = [
            self::FIRST => $this->lng->txt('cal_first'),
            self::SECOND => $this->lng->txt('cal_second'),
            self::THIRD => $this->lng->txt('cal_third'),
            self::FOURTH => $this->lng->txt('cal_fourth'),
            self::FIFTH => $this->lng->txt('cal_fifth'),
            self::LAST => $this->lng->txt('cal_last')
        ];
        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('week'),
            $options
        )->withRequired(true);
    }

    protected function getDayOfMonthInput(): Input
    {
        return $this->ui_factory->input()->field()->numeric($this->lng->txt('cal_day_of_month'))
                                ->withValue(1)
                                ->withRequired(true)
                                ->withAdditionalTransformation(
                                    $this->refinery->in()->series([
                                        $this->refinery->int()->isGreaterThanOrEqual(1),
                                        $this->refinery->int()->isLessThanOrEqual(31),
                                    ])
                                );
    }

    protected function getMonthInput(): Input
    {
        $months = [
            1 => self::JANUARY,
            2 => self::FEBRUARY,
            3 => self::MARCH,
            4 => self::APRIL,
            5 => self::MAY,
            6 => self::JUNE,
            7 => self::JULY,
            8 => self::AUGUST,
            9 => self::SEPTEMBER,
            10 => self::OCTOBER,
            11 => self::NOVEMBER,
            12 => self::DECEMBER
        ];
        $options = [];
        foreach ($months as $month => $key) {
            $options[$key] = ilCalendarUtil::_numericMonthToString($month);
        }
        return $this->ui_factory->input()->field()->select(
            $this->lng->txt('month'),
            $options
        )->withRequired(true);
    }

    protected function getOutputTransformation(): Transformation
    {
        $recurrence = clone $this->recurrence;
        $with_daily = $this->hasDaily();
        $with_weekly = $this->hasWeekly();
        $with_monthly = $this->hasMonthly();
        $with_yearly = $this->hasYearly();
        $with_unlimited = $this->hasUnlimitedRecurrences();

        return $this->refinery->custom()->transformation(function ($values) use (
            $recurrence,
            $with_daily,
            $with_weekly,
            $with_monthly,
            $with_yearly,
            $with_unlimited
        ) {
            $recurrence->reset();

            $rule_data = $values[self::RULE];
            switch ($rule_data[0]) {
                case self::DAILY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_DAILY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    break;

                case self::WEEKLY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_WEEKLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYDAY(implode(',', $rule_data[1][self::DAY]));
                    break;

                case self::MONTHLY_BY_DAY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_MONTHLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $index = $rule_data[1][self::WEEK];
                    $recurrence->setBYDAY($index . implode(',' . $index, $rule_data[1][self::DAY]));
                    break;

                case self::MONTHLY_BY_DATE:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_MONTHLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYMONTHDAY((string) $rule_data[1][self::DAY_OF_MONTH]);
                    break;

                case self::YEARLY_BY_DAY:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_YEARLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYMONTH((string) $rule_data[1][self::MONTH]);
                    $index = $rule_data[1][self::WEEK];
                    $recurrence->setBYDAY($index . implode(',' . $index, $rule_data[1][self::DAY]));
                    break;

                case self::YEARLY_BY_DATE:
                    $recurrence->setFrequenceType(ilCalendarRecurrence::FREQ_YEARLY);
                    $recurrence->setInterval((int) $rule_data[1][self::INTERVAL]);
                    $recurrence->setBYMONTH((string) $rule_data[1][self::MONTH]);
                    $recurrence->setBYMONTHDAY((string) $rule_data[1][self::DAY_OF_MONTH]);
                    break;

                default:
                case self::NO_RECURRENCE:
                    break;
            }

            $end_data = $values[self::END];
            if ($end_data[0] === self::UNTIL_COUNT) {
                $recurrence->setFrequenceUntilCount($end_data[1][self::COUNT]);
            }
            if ($end_data[0] === self::UNTIL_END_DATE) {
                $recurrence->setFrequenceUntilDate(new ilDate(
                    $end_data[1][self::END_DATE]->getTimestamp(),
                    IL_CAL_UNIX
                ));
            }

            return $recurrence;
        });
    }
}
