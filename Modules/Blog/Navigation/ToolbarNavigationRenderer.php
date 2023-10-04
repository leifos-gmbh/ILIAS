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

namespace ILIAS\Blog\Navigation;

use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Access\BlogAccess;

class ToolbarNavigationRenderer
{
    protected \ILIAS\Blog\Presentation\Util $util;
    protected $current_month;
    protected \ilCtrl $ctrl;
    protected BlogAccess $blog_access;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
    )
    {
        $this->domain = $domain;
        $this->gui = $gui;
        $this->util = $gui->presentation()->util();
    }

    public function renderToolbarNavigation(
        BlogAccess $blog_acces,
        array $a_items,
        int $blog_page,
        bool $single_posting,
        bool $prtf_embed,
        $month,
        int $portfolio_page
    ): void {

        $this->blog_access = $blog_acces;
        $toolbar = $this->gui->toolbar();
        $lng = $this->domain->lng();
        $this->ctrl = $ctrl = $this->gui->ctrl();
        $f = $this->gui->ui()->factory();
        $this->items = $a_items;
        $this->current_month = $month;

        $cmd = ($prtf_embed)
            ? "previewEmbedded"
            : "previewFullscreen";

        if ($single_posting) {	// single posting view
            $latest_posting = $this->getLatestPosting();
            if ($latest_posting > 0 && $blog_page !== $latest_posting) {
                $ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", $latest_posting);
                $mb = $f->button()->standard(
                    $lng->txt("blog_latest_posting"),
                    $ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, $cmd)
                );
            } else {
                $mb = $f->button()->standard($lng->txt("blog_latest_posting"), "#")->withUnavailableAction();
            }

            $prev_posting = $this->getPreviousPosting($blog_page);
            if ($prev_posting > 0) {
                $ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", $prev_posting);
                $pb = $f->button()->standard(
                    $lng->txt("previous"),
                    $ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, $cmd)
                );
            } else {
                $pb = $f->button()->standard($lng->txt("previous"), "#")->withUnavailableAction();
            }

            $next_posting = $this->getNextPosting($blog_page);
            if ($next_posting > 0) {
                $ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", $next_posting);
                $nb = $f->button()->standard(
                    $lng->txt("next"),
                    $ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, $cmd)
                );
            } else {
                $nb = $f->button()->standard($lng->txt("next"), "#")->withUnavailableAction();
            }
            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", $blog_page);
            $vc = $f->viewControl()->section($pb, $mb, $nb);
            $toolbar->addComponent($vc);
            if ($blog_acces->mayContribute() && $blog_acces->mayEditPosting($blog_page)) {
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "prvm", "");


                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", "");
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", "");
                $link = $ctrl->getLinkTargetByClass(\ilObjBlogGUI::class, "");
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", $blog_page);
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $month);
                $toolbar->addSeparator();
                $toolbar->addComponent($f->button()->standard($lng->txt("blog_edit"), $link));


                $ctrl->setParameterByClass(\ilBlogPostingGUI::class, "blpg", $blog_page);
                if ($prtf_embed) {
                    $ctrl->setParameterByClass(\ilObjPortfolioGUI::class, "ppage", $portfolio_page);
                }
                $link = $ctrl->getLinkTargetByClass(\ilBlogPostingGUI::class, "edit");
                $toolbar->addComponent($f->button()->standard($lng->txt("blog_edit_posting"), $link));
            }
        } else {		// month view
            $next_month = $this->getNextMonth($month);
            if ($next_month !== "") {
                $this->renderPreviousButton($this->getMonthTarget($next_month));
            } else {
                $this->renderPreviousButton("");
            }

            $this->renderMonthDropdown();

            $prev_month = $this->getPreviousMonth($month);
            if ($prev_month !== "") {
                $this->renderNextButton($this->getMonthTarget($prev_month));
            } else {
                $this->renderNextButton("");
            }

            $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $month);


            if ($blog_acces->mayContribute()) {
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "prvm", "");

                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", "");
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", "");
                $link = $ctrl->getLinkTargetByClass(\ilObjBlogGUI::class, "");
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "blpg", $blog_page);
                $ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $month);
                $toolbar->addSeparator();
                $toolbar->addComponent($f->button()->standard($lng->txt("blog_edit"), $link));
            }
        }
    }

    protected function getLatestPosting(): int {
        reset($this->items);
        $month = current($this->items);
        if (is_array($month)) {
            return (int) current($month)["id"];
        }
        return 0;
    }

    public function getNextPosting(
        int $blog_page
    ): int {
        reset($this->items);
        $found = "";
        $next_blpg = 0;
        foreach ($this->items as $month => $items) {
            foreach ($items as $item) {
                if ($item["id"] == $blog_page) {
                    $found = true;
                }
                if (!$found) {
                    $next_blpg = (int) $item["id"];
                }
            }
        }
        return $next_blpg;
    }

    protected function getPreviousPosting(
        int $blog_page
    ): int {
        reset($this->items);
        $found = "";
        $prev_blpg = 0;
        foreach ($this->items as $month => $items) {
            foreach ($items as $item) {
                if ($found && $prev_blpg === 0) {
                    $prev_blpg = (int) $item["id"];
                }
                if ((int) $item["id"] === $blog_page) {
                    $found = true;
                }
            }
        }
        return $prev_blpg;
    }

    protected function getMonthTarget(string $month) : string
    {
        $this->ctrl->setParameterByClass(\ilObjBlogGUI::class, "bmn", $month);
        return $this->ctrl->getLinkTargetByClass(\ilObjBlogGUI::class, "preview");
    }

    protected function renderMonthDropdown() : void
    {
        $toolbar = $this->gui->toolbar();
        $f = $this->gui->ui()->factory();
        $m = [];
        foreach ($this->items as $month => $items) {
            $label = $this->util->getMonthPresentation($month);
            if ($month === $this->current_month) {
                $label = "» " . $label;
            }
            $m[] = $f->link()->standard(
                $label,
                $this->getMonthTarget($month)
            );
        }
        if (count($m) > 0) {
            $toolbar->addStickyItem($f->dropdown()->standard($m)->withLabel(
                $this->util->getMonthPresentation($this->current_month)
            ));
        }
    }

    protected function getNextMonth(
        $current_month
    ): string {
        reset($this->items);
        $found = "";
        foreach ($this->items as $month => $items) {
            if ($month > $current_month) {
                $found = $month;
            }
        }
        return $found;
    }

    protected function getPreviousMonth(
        $current_month
    ): string {
        reset($this->items);
        $found = "";
        foreach ($this->items as $month => $items) {
            if ($month < $current_month && $found === "") {
                $found = $month;
            }
        }
        return $found;
    }

    /**
     * @param array $a_items item array
     */
    protected function getLatestMonth(): string {
        reset($this->items);
        return key($this->items);
    }

    protected function renderNextButton(string $href = "") : void
    {
        $this->renderNavButton("right", $href);
    }

    protected function renderPreviousButton(string $href = "") : void
    {
        $this->renderNavButton("left", $href);
    }

    protected function renderNavButton(string $dir, string $href = "") : void
    {
        $toolbar = $this->gui->toolbar();
        $b = $this->gui->ui()->factory()->button()->standard(
            "<span class=\"glyphicon glyphicon-chevron-" . $dir . " \" aria-hidden=\"true\"></span>",
            $href
        );
        if ($href === "") {
            $b = $b->withUnavailableAction();
        }
        $toolbar->addStickyItem($b);
    }


}