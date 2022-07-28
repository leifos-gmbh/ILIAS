<?php declare(strict_types = 1);

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

namespace ILIAS\News\Dashboard;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\News\InternalDataService;
use ILIAS\News\InternalDomainService;
use ILIAS\Repository\Filter\FilterAdapterGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    protected DashboardNewsManager $manager;
    protected InternalDataService $data;
    protected InternalDomainService $domain;
    protected \ILIAS\News\InternalGUIService $gui;
    protected ?FilterAdapterGUI $filter = null;

    public function __construct(
        InternalDataService $data_service,
        InternalDomainService $domain_service,
        \ILIAS\News\InternalGUIService $gui_service
    ) {
        $this->data = $data_service;
        $this->domain = $domain_service;
        $this->gui = $gui_service;
        $this->manager = $domain_service->dashboard();
    }

    public function getFilter($force_re_init = false) : FilterAdapterGUI
    {
        $gui = $this->gui;
        $lng = $this->domain->lng();
        if (is_null($this->filter) || $force_re_init) {
            $per_options = $this->manager->getPeriodOptions();
            $context_options = $this->manager->getContextOptions();

            $this->filter = $gui->filter(
                "news_dashboard_filter",
                [\ilDashboardGUI::class, \ilPDNewsGUI::class],
                "view",
                false,
                false
            )
                ->select(
                    "news_per",
                    $lng->txt("news_time_period"),
                    $per_options,
                    true,
                    (string) $this->manager->getDashboardNewsPeriod(),
                    true
                )
                ->select("news_ref_id", $lng->txt("context"), $context_options, true, null, true);
        }
        return $this->filter;
    }

    public function getTimelineGUI() : \ilNewsTimelineGUI
    {
        $filter = $this->getFilter();
        $data = $filter->getData();
        $t = \ilNewsTimelineGUI::getInstance((int) ($data["news_ref_id"] ?? 0), true);
        $t->setPeriod((int) ($data["news_per"] ?? 0));
        $t->setEnableAddNews(false);
        $t->setUserEditAll(false);
        return $t;
    }
}
