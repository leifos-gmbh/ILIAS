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

use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\UI\Component\Table\Data as ilDataTable;
use ILIAS\UI\Factory as ilUIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;

class ilPollUserTableGUI
{
    public const string TABLE_COL_LOGIN = 'login';
    public const string TABLE_COL_LASTNAME = 'lastname';
    public const string TABLE_COL_FIRSTNAME = 'firstname';
    public const string TABLE_COL_ANSWER_PREFIX = 'answer';
    public const string LNG_TABLE_COL_LOGIN = 'login';
    public const string LNG_TABLE_COL_LASTNAME = 'lastname';
    public const string LNG_TABLE_COL_FIRSTNAME = 'firstname';
    protected const string TABLE_ID = 'pllusrtbl';
    protected const string TABLE_ACTION_ID = 'table_action';
    protected const string ROW_ID = 'row_ids';
    protected const string ALL_OBJECTS = "ALL_OBJECTS";

    protected URLBuilder $url_builder;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;
    protected ilDataTable $table;

    public function __construct(
        protected ilPollUserTableDataRetrieval $data_retrieval,
        protected ilUIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilLanguage $lng,
        protected ilHTTPServices $http_services
    ) {
        $lng->loadLanguageModule('poll');
    }

    public function getColumns(): array
    {
        $columns = [
            self::TABLE_COL_LOGIN => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LOGIN)
            )
                ->withHighlight(true),
            self::TABLE_COL_FIRSTNAME => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_FIRSTNAME)
            ),
            self::TABLE_COL_LASTNAME => $this->ui_factory->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LASTNAME)
            )
        ];

        foreach ($this->data_retrieval->getAnswers() as $answer) {
            $columns[self::TABLE_COL_ANSWER_PREFIX . (int) ($answer["id"] ?? 0)] = $this->ui_factory->table()->column()->statusIcon(
                (string) ($answer["answer"] ?? '')
            );
        }
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
            $this->data_retrieval,
            $title,
            $this->getColumns()
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
