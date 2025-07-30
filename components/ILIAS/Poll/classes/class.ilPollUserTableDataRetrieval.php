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

use ILIAS\UI\Component\Table\DataRetrieval as ilTableDataRetrievalInterface;
use ILIAS\UI\Factory as ilUIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class ilPollUserTableDataRetrieval implements ilTableDataRetrievalInterface
{
    protected array $data;
    protected Icon $checked_icon;
    protected Icon $unchecked_icon;

    public function __construct(
        protected ilObjPoll $poll,
        protected ilUIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilLanguage $lng
    ) {
        $this->data = [];
        $this->checked_icon = $ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath('standard/icon_ok.svg'),
            $lng->txt('poll_answer_selected_alt_text'),
            'medium'
        );
        $this->unchecked_icon = $ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath('standard/icon_not_ok.svg'),
            $lng->txt('poll_answer_selected_alt_text'),
            'medium'
        );
    }

    public function getAnswers(): array
    {
        return $this->poll->getAnswers();
    }

    public function getPollQuestion(): string
    {
        return $this->poll->getQuestion();
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $answer_ids = $this->getAnswerIds();
        $comparator = null;
        $rows = $this->data;
        for ($i = 0; $i < count($rows); $i++) {
            $rows[$i]['column'] = $column_name;
        }
        switch ($column_name) {
            case ilPollUserTableGUI::TABLE_COL_LOGIN:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['login'], $f2['login']);
                };
                break;
            case ilPollUserTableGUI::TABLE_COL_FIRSTNAME:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['firstname'], $f2['firstname']);
                };
                break;
            case ilPollUserTableGUI::TABLE_COL_LASTNAME:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['lastname'], $f2['lastname']);
                };
                break;
            default:
                $comparator = function (array $f1, array $f2) {
                    return $f1[$f1['column']] ? $f2[$f2['column']] ? 0 : 1 : -1;
                };
        }
        uasort($rows, $comparator);
        if ($direction === "DESC") {
            $rows = array_reverse($rows, true);
        }
        $rows = array_slice($rows, $range->getStart(), $range->getLength(), true);
        foreach ($rows as $row) {
            $record = [
                ilPollUserTableGUI::TABLE_COL_LOGIN => (string) ($row['login'] ?? ''),
                ilPollUserTableGUI::TABLE_COL_FIRSTNAME => (string) ($row['firstname'] ?? ''),
                ilPollUserTableGUI::TABLE_COL_LASTNAME => (string) ($row['lastname'] ?? ''),
            ];
            foreach ($answer_ids as $answer_id) {
                $icon = $row[ilPollUserTableGUI::TABLE_COL_ANSWER_PREFIX . $answer_id]
                    ? $this->checked_icon
                    : $this->unchecked_icon;
                $record[ilPollUserTableGUI::TABLE_COL_ANSWER_PREFIX . $answer_id] = $icon;
            }
            yield $row_builder->buildDataRow(
                '' . $row['login'],
                $record
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->data);
    }

    public function init(): void
    {
        $answer_ids = $this->getAnswerIds();
        $data = [];
        foreach ($this->poll->getVotesByUsers() as $user_id => $vote) {
            $answers = (array) ($vote["answers"] ?? []);
            unset($vote["answers"]);

            foreach ($answer_ids as $answer_id) {
                $vote[ilPollUserTableGUI::TABLE_COL_ANSWER_PREFIX . $answer_id] = in_array($answer_id, $answers);
            }

            $data[] = $vote;
        }
        $this->data = $data;
    }

    protected function getAnswerIds(): array
    {
        $a_answer_ids = [];
        foreach ($this->getAnswers() as $answer) {
            $a_answer_ids[] = (int) ($answer["id"] ?? 0);
        }
        return $a_answer_ids;
    }
}
