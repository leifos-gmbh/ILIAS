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

use ILIAS\User\Profile\PublicProfileGUI;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Search\Service\Service;
use ILIAS\Search\Presentation\Result\ResultPresenter;
use ILIAS\Search\GUI\Global\User\Actions;
use ILIAS\Search\GUI\Global\SearchStateHandler;
use ILIAS\Search\Presentation\Result\ViewControls\PaginationInfos;
use ILIAS\Search\GUI\Global\Param;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilLuceneUserSearchGUI: ILIAS\User\Profile\PublicProfileGUI
 * @ilCtrl_IsCalledBy ilLuceneUserSearchGUI: ilSearchControllerGUI
 */
class ilLuceneUserSearchGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected UIRenderer $ui_renderer;
    protected ilObjUser $user;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilSearchSettings $settings;
    protected ResultPresenter $result_presenter;
    protected Actions $actions;
    protected SearchStateHandler $state_handler;

    public function __construct()
    {
        global $DIC;

        $service = new Service($DIC);

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('search');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->settings = ilSearchSettings::getInstance();
        $this->result_presenter = $service->presentation()->result();
        $this->actions = $service->gui()->userSearchActions();
        $this->state_handler = $service->gui()->searchStateHandler();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case strtolower(PublicProfileGUI::class):

                $user_id = 0;
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $user_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $profile = new PublicProfileGUI($user_id);
                $profile->setBackUrl((string) $this->actions->showSavedResults());
                $ret = $this->ctrl->forwardCommand($profile);
                $this->tpl->setContent($ret);
                break;


            default:
                if (!$this->actions->isValidCommand($cmd)) {
                    $cmd = 'showSavedResults';
                }
                $this->$cmd();
                break;
        }
    }

    protected function search(): void
    {
        $cache = $this->fetchCache();

        $term = $this->state_handler->fetchRequestedSearchTerm();
        $page = 1;
        $max_page = 1;

        $this->state_handler->resetMaxPage();
        $cache->deleteCachedEntries();
        $cache->setQuery($term);
        $cache->save();

        $this->renderSearchInput($term);
        $pagination_infos = $this->buildPaginationInfos($page, $max_page);

        $result = $this->performSearch($term);
        $this->renderResults($result, $pagination_infos, $term);
    }

    /**
     * Search from main menu
     */
    protected function remoteSearch(): void
    {
        $cache = $this->fetchCache();

        $term = $this->state_handler->fetchRequestedSearchTerm();
        $page = 1;
        $max_page = 1;

        $this->state_handler->resetMaxPage();
        $cache->deleteCachedEntries();
        $cache->setQuery($term);
        $cache->save();

        $this->renderSearchInput($term);
        $pagination_infos = $this->buildPaginationInfos($page, $max_page);

        $result = $this->performSearch($term);
        $this->renderResults($result, $pagination_infos, $term);
    }

    protected function showSavedResults(): void
    {
        $cache = $this->fetchCache();

        $term = $cache->getQuery();
        $page = $cache->getResultPageNumber();
        $max_page = $this->state_handler->fetchMaxPage();

        $this->renderSearchInput($term);
        $pagination_infos = $this->buildPaginationInfos($page, $max_page);

        $result = $this->performSearch($term);
        $this->renderResults($result, $pagination_infos, $term);
    }

    protected function switchResultPage(): void
    {
        $cache = $this->fetchCache();

        $term = $cache->getQuery();
        $page = $this->state_handler->fetchRequestedPage();
        $max_page = max($this->state_handler->fetchMaxPage(), $page);

        $this->state_handler->updateMaxPage($max_page);
        $cache->setResultPageNumber($page);
        $cache->save();

        $this->renderSearchInput($term);
        $pagination_infos = $this->buildPaginationInfos($page, $max_page);

        $result = $this->performSearch($term);
        $this->renderResults($result, $pagination_infos, $term);
    }

    protected function performSearch(string $term): ilLuceneSearchResult
    {
        $qp = new ilLuceneQueryParser($term);
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->setType(ilLuceneSearcher::TYPE_USER);
        $searcher->search();

        return $searcher->getResult();
    }

    protected function fetchCache(): ilUserSearchCache
    {
        $cache = ilUserSearchCache::_getInstance($this->user->getId());
        $cache->switchSearchType(ilUserSearchCache::LUCENE_USER_SEARCH);
        return $cache;
    }

    protected function renderResults(
        ilLuceneSearchResult $result,
        PaginationInfos $pagination_infos,
        string $term
    ): void {
        if ($result->getCandidates() || $pagination_infos->currentPage() > 1) {
            $result_panel = $this->result_presenter->getLuceneUserSearchResultAsPanel(
                $pagination_infos,
                ...$result->getCandidates()
            );
            $this->tpl->setVariable(
                'SEARCH_RESULTS',
                $this->ui_renderer->render($result_panel)
            );
        } elseif ($term !== '') {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_INFO,
                sprintf(
                    $this->lng->txt('search_no_match_hint'),
                    $term
                )
            );
        } else {
            $this->tpl->setOnScreenMessage(
                GlobalTemplate::MESSAGE_TYPE_INFO,
                $this->lng->txt('search_no_match')
            );
        }
    }

    protected function renderSearchInput(string $term)
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_usr_search.html', 'components/ILIAS/Search');
        $this->tpl->addJavascript("assets/js/Search.js");

        $this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "performSearch"));
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($term));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
    }

    protected function buildPaginationInfos(
        int $page,
        int $max_page
    ): PaginationInfos {
        return $this->result_presenter->getPaginationInfos(
            $page,
            $max_page,
            $this->settings->getMaxHits(),
            $this->actions->switchResultPage(),
            Param::PAGE_NUMBER
        );
    }
}
