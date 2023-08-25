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

namespace ILIAS\Wiki\Page;

use ILIAS\Wiki\InternalDataService;
use ILIAS\Wiki\InternalRepoService;

/**
 * Page manager
 */
class PageManager
{
    protected \ilObjWiki $wiki;
    protected $ref_id;
    protected InternalDataService $data_service;

    public function __construct(
        InternalDataService $data_service,
        PageDBRepository $page_repo,
        \ILIAS\Wiki\Wiki\DomainService $wiki_domain,
        DomainService $page_domain,
        int $ref_id
    ) {
        $this->wiki_ref_id = $ref_id;
        $this->data_service = $data_service;
        $this->page_repo = $page_repo;
        $this->wiki_domain = $wiki_domain;
        $this->page_domain = $page_domain;
        $this->wiki = $this->wiki_domain->object($ref_id);
    }

    protected function getWikiId() : int
    {
        return $this->wiki_domain->getObjId($this->wiki_ref_id);
    }

    public function createWikiPage(
        string $title,
        int $wpg_id = 0,
        string $lang = "-",
        int $template_page = 0
    ) : int {
        // todo check if $wpg_id is given if lang is set
        // todo check if $wpg_id is not given if lang is not set
        // todo check if $wpg_id belongs to wiki, if given
        // todo check if page (id/lang) does not exist already
        // todo check if title is not empty

        // check if template has to be used
        if ($template_page === 0) {
            if (!$this->wiki->getEmptyPageTemplate()) {
                $wt = new \ilWikiPageTemplate($this->getId());
                $ts = $wt->getAllInfo(\ilWikiPageTemplate::TYPE_NEW_PAGES);
                if (count($ts) === 1) {
                    $t = current($ts);
                    $a_template_page = $t["wpage_id"];
                }
            }
        }

        // master language
        if ($lang === "-") {
            $page = new \ilWikiPage(0);
            $page->setWikiId($this->getId());
            $page->setWikiRefId($this->wiki_ref_id);
            $page->setTitle(ilWikiUtil::makeDbTitle($title));
            if ($this->wiki->getRating() && $this->wiki->getRatingForNewPages()) {
                $page->setRating(true);
            }
            // needed for notification
            $page->create();
        } else {
            $orig_page = $this->page_domain->getWikiPage($this->wiki_ref_id, $wpg_id, 0, "-");
            $orig_page->copyPageToTranslation($lang);

            $page = $this->page_domain->getWikiPage($this->wiki_ref_id, $wpg_id, 0, $lang);
            $page->setTitle(\ilWikiUtil::makeDbTitle($title));
            $page->update();
        }

        // copy template into new page
        if ($template_page > 0) {
            $t_page = $this->page_domain->getWikiPage($this->wiki_ref_id, $template_page, 0, $lang);
            $t_page->copy($page->getId());

            // #15718
            if ($lang === "-") {
                \ilAdvancedMDValues::_cloneValues(
                    0,
                    $this->getId(),
                    $this->getId(),
                    "wpg",
                    $a_template_page,
                    $page->getId()
                );
            }
        }
        return $page->getId();
    }

    /**
     * @return iterable<Page>
     */
    public function getWikiPages(string $lang = "-") : \Iterator
    {
        return $this->page_repo->getWikiPages(
            $this->getWikiId(),
            $lang
        );
    }

    public function getMasterPagesWithoutTranslation(string $trans) : \Iterator
    {
        return $this->page_repo->getMasterPagesWithoutTranslation(
            $this->getWikiId(),
            $trans
        );
    }

}