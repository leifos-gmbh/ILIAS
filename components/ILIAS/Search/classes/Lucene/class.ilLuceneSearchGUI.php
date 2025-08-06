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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_IsCalledBy ilLuceneSearchGUI: ilSearchControllerGUI
 */
class ilLuceneSearchGUI extends ilSearchBaseGUI
{
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;

    protected ?int $root_node;
    protected array $admin_panel_commands = [];
    protected array $admin_view_button = [];
    protected array $creation_selector = [];

    protected string $page_form_action = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->initFilter(self::SEARCH_FORM_LUCENE);
        $this->initUserSearchCache();
    }

    /**
     * Execute Command
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->prepareOutput();
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->handleCommand($cmd);
                break;
        }
    }

    /**
     * Add admin panel command
     */
    public function prepareOutput(): void
    {
        parent::prepareOutput();
        $this->getTabs();
    }

    /**
     * @todo rename
     */
    protected function getType(): int
    {
        return self::SEARCH_DETAILS;
    }

    /**
     * Needed for base class search form
     * @todo rename
     */
    protected function getDetails(): array
    {
        return $this->search_cache->getItemFilter();
    }

    /**
     * Needed for base class search form
     * @todo rename
     */
    protected function getMimeDetails(): array
    {
        return $this->search_cache->getMimeFilter();
    }

    /**
     * Search from main menu
     */
    protected function remoteSearch(): void
    {
        $queryString = '';
        if ($this->http->wrapper()->post()->has('queryString')) {
            $queryString = $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
        }
        $root_id = ROOT_FOLDER_ID;
        if ($this->http->wrapper()->post()->has('root_id')) {
            $root_id = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $qp = new ilLuceneQueryParser($queryString);
        $qp->parseAutoWildcard();

        $query = $qp->getQuery();

        $this->search_cache->setRoot($root_id);
        $this->search_cache->setQuery(ilUtil::stripSlashes($query));
        $this->search_cache->save();

        $this->search();
    }

    /**
     * Show saved results
     */
    protected function showSavedResults(): bool
    {
        if (!strlen($this->search_cache->getQuery())) {
            $this->showSearchForm();
            return false;
        }

        $qp = new ilLuceneQueryParser($this->search_cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Load saved results
        $filter = ilLuceneSearchResultFilter::getInstance($this->user->getId());
        $filter->loadFromDb();

        // Highlight
        $searcher->highlight($filter->getResultObjIds());

        $this->showSearchForm();

        if ($filter->getResults()) {
            $result_presenter = $this->presenter->result();
            $result_panel_and_modals = $result_presenter->getLuceneSearchResultAsPanel(
                $filter,
                $searcher->getHighlighter(),
                $this->initSortationFromQuery(),
                $this->search_cache->getResultPageNumber(),
                $this->initMaxPageFromSession(),
                $this->getPaginationAction(),
                self::PAGE_NUMBER_PARAM,
                $this->getSortationAction(),
                self::ORDER_PARAM
            );

            $this->tpl->setVariable(
                'SEARCH_RESULTS',
                $result_presenter->replacePlaceholders($this->ui_renderer->render($result_panel_and_modals))
            );
        } elseif (strlen($this->search_cache->getQuery())) {
            $this->tpl->setOnScreenMessage(
                'info',
                sprintf(
                    $this->lng->txt('search_no_match_hint'),
                    ilLegacyFormElementsUtil::prepareFormOutput($qp->getQuery())
                )
            );
        }
        return true;
    }

    /**
     * Search (button pressed)
     */
    protected function search(): void
    {
        $this->search_cache->deleteCachedEntries();
        ilSession::clear(self::MAX_PAGE_PARAM);

        // Reset details
        ilSubItemListGUI::resetDetails();
        $this->performSearch();
    }

    protected function performSearchFilter(): void
    {
        $this->search();
    }

    /**
     * Perform search
     */
    protected function performSearch(): void
    {
        if (!$this->initPageNumberFromQuery() and $this->search_mode != 'in_results') {
            ilSession::clear(self::MAX_PAGE_PARAM);
            $this->search_cache->deleteCachedEntries();
        }
        $filter_query = '';
        if ($this->search_cache->getItemFilter() and ilSearchSettings::getInstance()->isLuceneItemFilterEnabled()) {
            $filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();
            foreach ($this->search_cache->getItemFilter() as $obj => $value) {
                if (!$filter_query) {
                    $filter_query .= '+( ';
                } else {
                    $filter_query .= 'OR';
                }
                $filter_query .= (' ' . $filter_settings[$obj]['filter'] . ' ');
            }
            $filter_query .= ') ';
        }
        // begin-patch mime_filter
        $mime_query = '';
        if ($this->search_cache->getMimeFilter() and ilSearchSettings::getInstance()->isLuceneMimeFilterEnabled()) {
            $filter_settings = ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions();
            foreach ($this->search_cache->getMimeFilter() as $mime => $value) {
                if (!$mime_query) {
                    $mime_query .= '+( ';
                } else {
                    $mime_query .= 'OR';
                }
                $mime_query .= (' ' . $filter_settings[$mime]['filter'] . ' ');
            }
            $mime_query .= ') ';
        }

        // begin-patch creation_date
        $cdate_query = $this->parseCreationFilter();



        $filter_query = $filter_query . ' ' . $mime_query . ' ' . $cdate_query;


        $query = $this->search_cache->getQuery();
        if ($query) {
            $query = ' +(' . $query . ')';
        }
        $qp = new ilLuceneQueryParser($filter_query . $query);
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->search();

        // Filter results
        $filter = ilLuceneSearchResultFilter::getInstance($this->user->getId());
        $filter->addFilter(new ilLucenePathFilter($this->search_cache->getRoot()));
        $filter->setCandidates($searcher->getResult());
        $filter->filter();

        if ($filter->getResultObjIds()) {
            $searcher->highlight($filter->getResultObjIds());
        }

        // Show results
        $this->showSearchForm();

        if ($filter->getResultObjIds()) {
            $result_presenter = $this->presenter->result();
            $result_panel_and_modals = $result_presenter->getLuceneSearchResultAsPanel(
                $filter,
                $searcher->getHighlighter(),
                $this->initSortationFromQuery(),
                $this->search_cache->getResultPageNumber(),
                $this->initMaxPageFromSession(),
                $this->getPaginationAction(),
                self::PAGE_NUMBER_PARAM,
                $this->getSortationAction(),
                self::ORDER_PARAM
            );

            $this->tpl->setVariable(
                'SEARCH_RESULTS',
                $result_presenter->replacePlaceholders($this->ui_renderer->render($result_panel_and_modals))
            );
        } else {
            $this->tpl->setOnScreenMessage(
                'info',
                sprintf(
                    $this->lng->txt('search_no_match_hint'),
                    ilLegacyFormElementsUtil::prepareFormOutput($qp->getQuery())
                )
            );
        }
    }

    /**
     * get tabs
     */
    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent("src_luc");

        $this->tabs->addTarget('search', $this->ctrl->getLinkTarget($this));

        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tabs->addTarget('search_user', $this->ctrl->getLinkTargetByClass('illuceneusersearchgui'));
        }

        $this->tabs->setTabActive('search');
    }

    protected function initUserSearchCache(): void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_DEFAULT);
        $page_number = $this->initPageNumberFromQuery();

        if ($this->http->wrapper()->post()->has('cmd')) {
            $requested_cmd = (array) $this->http->wrapper()->post()->retrieve('cmd', $this->getStringArrayTransformation());
        } elseif ($this->http->wrapper()->query()->has('cmd')) {
            $requested_cmd = (array) $this->http->wrapper()->query()->retrieve(
                'cmd',
                $this->refinery->kindlyTo()->string()
            );
            $requested_cmd = [$requested_cmd[0] => "Search"];
        } else {
            $requested_cmd = [];
        }
        $new_search = (bool) ($requested_cmd["performSearch"] ?? false);
        $new_filter = (bool) ($requested_cmd["performSearchFilter"] ?? false);
        $new_search_or_filter = $new_search || $new_filter;

        if ($this->http->wrapper()->post()->has('root_id')) {
            $filter_scope = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        } else {
            $filter_scope = (int) ($this->search_filter_data["search_scope"] ?? ROOT_FOLDER_ID);
        }

        $filter_type_active = (is_null($this->search_filter_data["search_type"] ?? null))
            ? false
            : true;
        $requested_filter_type = (array) ($this->search_filter_data["search_type"] ?? []);
        $requested_filter_type = array_flip($requested_filter_type);
        $requested_filter_type = array_fill_keys(array_keys($requested_filter_type), "1");

        if ($page_number) {
            $this->search_cache->setResultPageNumber($page_number);
        }

        if ($this->http->wrapper()->post()->has('term')) {
            $term = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        } else {
            $term = $this->search_cache->getQuery();
        }
        $this->search_cache->setQuery($term);

        if ($filter_type_active) {
            $filtered = [];
            foreach (ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions() as $type => $data) {
                if ($requested_filter_type[$type] ?? false) {
                    $filtered[$type] = 1;
                }
            }
            $this->search_cache->setItemFilter($filtered);

            // Mime filter
            $mime = [];
            foreach (ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions() as $type => $data) {
                if ($requested_filter_type[$type] ?? false) {
                    $mime[$type] = 1;
                }
            }
            $this->search_cache->setMimeFilter($mime);
        }
        $this->search_cache->setCreationFilter($this->loadCreationFilter());
        if (!$filter_type_active) {
            // @todo: keep item filter settings?
            $this->search_cache->setItemFilter([]);
            $this->search_cache->setMimeFilter([]);
        }
        if (!isset($this->search_filter_data["search_date"])) {
            $this->search_cache->setCreationFilter([]);
        }

        if ($new_filter) {
            $this->search_cache->setRoot($filter_scope);
        }
    }

    protected function showSearchForm(): void
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.search.html', 'components/ILIAS/Search');
        $this->renderSearch($this->search_cache->getQuery(), $this->search_cache->getRoot());
    }

    /**
     * Parse creation date
     */
    protected function parseCreationFilter(): string
    {
        $options = $this->search_cache->getCreationFilter();

        if (!($options['date_start'] ?? false) && !($options['date_end'] ?? false)) {
            return '';
        }

        $start = null;
        $end = null;
        if (($options['date_start'] ?? false)) {
            $start = new ilDate($options['date_start'] ?? "", IL_CAL_DATE);
        }
        if (($options['date_end'] ?? false)) {
            $end = new ilDate($options['date_end'] ?? "", IL_CAL_DATE);
        }

        if ($start && is_null($end)) {
            $now = new ilDate(time(), IL_CAL_UNIX);
            return '+(cdate:[' . $start->get(IL_CAL_DATE) . ' TO ' . $now->get(IL_CAL_DATE) . '*]) ';
        } elseif ($end && is_null($start)) {
            return '+(cdate:[* TO ' . $end->get(IL_CAL_DATE) . ']) ';
        } else {
            return '+(cdate:[' . $start->get(IL_CAL_DATE) . ' TO ' . $end->get(IL_CAL_DATE) . '*]) ';
        }

        return '';
    }
}
