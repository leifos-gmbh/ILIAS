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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Search\Presentation\Result\Sortation;
use ILIAS\Data\URI;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Search\Presentation\PresenterImpl;
use ILIAS\Search\Presentation\Presenter;

/**
* Class ilSearchBaseGUI
*
* Base class for all search gui classes. Offers functionallities like set Locator set Header ...
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @package ilias-search
*
* @ilCtrl_IsCalledBy ilSearchBaseGUI: ilSearchControllerGUI
*
*
*/
class ilSearchBaseGUI
{
    protected const string ORDER_PARAM = 'sortation';
    protected const string MAX_PAGE_PARAM = 'max_page';
    protected const string PAGE_NUMBER_PARAM = 'page_number';

    public const int SEARCH_FAST = 1;
    public const int SEARCH_DETAILS = 2;
    public const string SEARCH_AND = 'and';
    public const string SEARCH_OR = 'or';

    public const int SEARCH_FORM_LUCENE = 1;
    public const int SEARCH_FORM_STANDARD = 2;
    public const int SEARCH_FORM_USER = 3;

    protected ilUserSearchCache $search_cache;
    protected string $search_mode = '';

    protected ilSearchSettings $settings;
    protected ?ilSearchFilterGUI $search_filter = null;
    protected ?array $search_filter_data = null;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected DataFactory $data_factory;
    protected Presenter $presenter;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng->loadLanguageModule('search');
        $this->settings = new ilSearchSettings();
        $this->user = $DIC->user();
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->data_factory = new DataFactory();
        $this->presenter = new PresenterImpl($DIC);
    }

    protected function initPageNumberFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has(self::PAGE_NUMBER_PARAM)) {
            // pages in the view control are 0-indexed, in search 1-indexed
            return $this->http->wrapper()->query()->retrieve(
                self::PAGE_NUMBER_PARAM,
                $this->refinery->kindlyTo()->int()
            ) + 1;
        }
        return 0;
    }

    protected function initSortationFromQuery(): Sortation
    {
        $sortation_string = '';
        if ($this->http->wrapper()->query()->has('sortation')) {
            $sortation_string = $this->http->wrapper()->query()->retrieve(
                'sortation',
                $this->refinery->kindlyTo()->string()
            );
        }
        return Sortation::tryFrom($sortation_string) ?? Sortation::RELEVANCE_DESC;
    }

    protected function initMaxPageFromSession(): int
    {
        $max_page = max(ilSession::get(self::MAX_PAGE_PARAM), $this->search_cache->getResultPageNumber());
        ilSession::set(self::MAX_PAGE_PARAM, $max_page);
        return $max_page;
    }

    protected function getPaginationAction(): URI
    {
        return $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            $this->ctrl->getLinkTarget($this, 'performSearch')
        );
    }

    protected function getSortationAction(): URI
    {
        return $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            $this->ctrl->getLinkTarget($this, 'showSavedResults')
        );
    }


    public function prepareOutput(): void
    {
        $this->tpl->loadStandardTemplate();

        $this->tpl->setTitleIcon(
            ilObject::_getIcon(0, "big", "src"),
            ""
        );
        $this->tpl->setTitle($this->lng->txt("search"));
    }

    public function handleCommand(string $a_cmd): void
    {
        if (method_exists($this, $a_cmd)) {
            $this->$a_cmd();
        } else {
            $a_cmd .= 'Object';
            $this->$a_cmd();
        }
    }

    public function autoComplete(): void
    {
        $query = '';
        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
        }
        $list = ilSearchAutoComplete::getList($query);
        echo $list;
        exit;
    }

    protected function getSearchCache(): ilUserSearchCache
    {
        return $this->search_cache;
    }

    /**
     * @return array<{date_start: string, date_end: string}>
     */
    protected function loadCreationFilter(): array
    {
        if (!$this->settings->isDateFilterEnabled()) {
            return [];
        }

        $options = [];
        if (isset($this->search_filter_data["search_date"])) {
            $options["date_start"] = $this->search_filter_data["search_date"][0];
            $options["date_end"] = $this->search_filter_data["search_date"][1];
        }

        return $options;
    }

    protected function renderSearch(string $term, int $root_node = 0)
    {
        $this->tpl->addJavascript("assets/js/Search.js");

        $this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "performSearch"));
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($term));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());

        if ($root_node) {
            $this->renderFilter($root_node);
        }
    }

    protected function renderFilter(int $root_node)
    {
        $filter_html = $this->search_filter->getHTML();
        preg_match('/id="([^"]+)"/', $filter_html, $matches);
        $filter_id = $matches[1];
        $this->tpl->setVariable("SEARCH_FILTER", $filter_html);
        // scope in filter must be manipulated by JS if search is triggered in meta bar
        $this->tpl->addOnLoadCode("il.Search.syncFilterScope('" . $filter_id . "', '" . $root_node . "');");
    }

    protected function initFilter(int $mode)
    {
        $this->search_filter = new ilSearchFilterGUI($this, $mode);
        $this->search_filter_data = $this->search_filter->getData();
    }

    protected function getStringArrayTransformation(): ILIAS\Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(
            static function (array $arr): array {
                // keep keys(!), transform all values to string
                return array_map(
                    static function ($v): string {
                        return \ilUtil::stripSlashes((string) $v);
                    },
                    $arr
                );
            }
        );
    }
}
