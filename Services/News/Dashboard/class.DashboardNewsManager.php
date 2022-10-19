<?php

declare(strict_types=1);

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

use ILIAS\News\InternalRepoService;
use ILIAS\News\InternalDataService;
use ILIAS\News\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DashboardNewsManager
{
    protected DashboardSessionRepository $session_repo;
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected InternalDomainService $domain;
    protected \ilFavouritesManager $fav_manager;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain
    ) {
        $this->session_repo = $repo->dashboard();
        $this->data = $data;
        $this->domain = $domain;
        $this->fav_manager = new \ilFavouritesManager();
    }

    public function getDashboardNewsPeriod(): int
    {
        $user = $this->domain->user();
        $period = $this->session_repo->getDashboardNewsPeriod();
        if ($period === 0) {
            $period = \ilNewsItem::_lookupUserPDPeriod($user->getId());
        }
        return $period;
    }

    /**
     * @return array<int,string>
     */
    public function getPeriodOptions(): array
    {
        $lng = $this->domain->lng();
        $news_set = new \ilSetting("news");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $default_per = \ilNewsItem::_lookupDefaultPDPeriod();

        $options = [
            2 => sprintf($lng->txt("news_period_x_days"), 2),
            3 => sprintf($lng->txt("news_period_x_days"), 3),
            5 => sprintf($lng->txt("news_period_x_days"), 5),
            7 => $lng->txt("news_period_1_week"),
            14 => sprintf($lng->txt("news_period_x_weeks"), 2),
            30 => $lng->txt("news_period_1_month"),
            60 => sprintf($lng->txt("news_period_x_months"), 2),
            120 => sprintf($lng->txt("news_period_x_months"), 4),
            180 => sprintf($lng->txt("news_period_x_months"), 6),
            366 => $lng->txt("news_period_1_year")
        ];

        $unset = [];
        foreach ($options as $k => $opt) {
            if (!$allow_shorter_periods && ($k < $default_per)) {
                $unset[$k] = $k;
            }
            if (!$allow_longer_periods && ($k > $default_per)) {
                $unset[$k] = $k;
            }
        }
        foreach ($unset as $k) {
            unset($options[$k]);
        }

        return $options;
    }

    /**
     * @return array<int,string>
     */
    public function getContextOptions(): array
    {
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $period = $this->getDashboardNewsPeriod();

        $cnt = [];
        \ilNewsItem::_getNewsItemsOfUser(
            $user->getId(),
            false,
            true,
            $period,
            $cnt
        );


        $fav_items = $this->fav_manager->getFavouritesOfUser($user->getId());
        $ref_ids = [];
        foreach ($fav_items as $item) {
            $ref_ids[] = (int) $item["ref_id"];
        }

        // related objects (contexts) of news
        $contexts[0] = $lng->txt("news_all_items");

        $conts = [];
        foreach ($ref_ids as $ref_id) {
            $obj_id = \ilObject::_lookupObjId($ref_id);
            $title = \ilObject::_lookupTitle($obj_id);
            $conts[$ref_id] = $title;
        }

        asort($conts);
        foreach ($conts as $ref_id => $title) {
            $contexts[$ref_id] = $title . " (" . (int) $cnt[$ref_id] . ")";
        }

        return $contexts;
    }
}
