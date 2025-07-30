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
 ********************************************************************
 */

declare(strict_types=1);

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\UI\Factory as ilUIFactory;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ILIAS\UI\Component\Table\Data as ilDataTable;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;
use ILIAS\UI\Renderer as UIRenderer;
use JetBrains\PhpStorm\NoReturn;

class ilPollAnswerTableGUI
{
    public const TABLE_COL_ORDER = 'pos';
    public const TABLE_COL_ANSWER = 'answer';
    public const TABLE_COL_CURRENT_VOTES = 'votes';
    public const TABLE_COL_CURRENT_PERCENTAGE = 'percentage';
    protected const LNG_TABLE_COL_ORDER = 'poll_sortorder';
    protected const LNG_TABLE_COL_ANSWER = 'poll_answer';
    protected const LNG_TABLE_COL_CURRENT_VOTES = 'poll_absolute';
    protected const LNG_TABLE_COL_CURRENT_PERCENTAGE = 'poll_percentage';
    protected const TABLE_ID = 'pllnswrtbl';
    protected const TABLE_ACTION_ID = 'table_action';
    protected const ROW_ID = 'row_ids';
    protected const ALL_OBJECTS = "ALL_OBJECTS";

    protected URLBuilder $url_builder;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;
    protected ilDataTable $table;

    public function __construct(
        protected ilPollAnswerTableDataRetrieval $data_retrieval,
        protected ilUIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilLanguage $lng,
        protected ilHTTPServices $http_services
    ) {
    }

    public function getColumns(): array
    {
        $columns = [
            self::TABLE_COL_ORDER => $this->ui_factory->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_ORDER)
            )
                ->withHighlight(true),
            self::TABLE_COL_ANSWER => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_ANSWER)
            ),
            self::TABLE_COL_CURRENT_VOTES => $this->ui_factory->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_CURRENT_VOTES)
            ),
            self::TABLE_COL_CURRENT_PERCENTAGE => $this->ui_factory->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_CURRENT_PERCENTAGE)
            )
                ->withUnit('%')
        ];
        return $columns;
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function initTable(): void
    {
        if (isset($this->table)) {
            return;
        }
        $title = $this->lng->txt("poll_question") . ": \"" . $this->data_retrieval->getPollQuestion() . "\"";
        $this->table = $this->ui_factory->table()->data(
            $title,
            $this->getColumns(),
            $this->data_retrieval
        )
            ->withId(self::TABLE_ID)
            ->withActions($this->getActions())
            ->withRequest($this->http_services->request());
    }

    public function getHTML(): string
    {
        $this->initTable();
        return $this->ui_renderer->render([$this->table]);
    }
}
