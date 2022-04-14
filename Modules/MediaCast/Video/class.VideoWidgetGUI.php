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

namespace ILIAS\MediaCast\Video;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoWidgetGUI
{
    protected string $dom_wrapper_id;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected ?VideoItem $video;

    public function __construct(
        \ilGlobalTemplateInterface $main_tpl,
        string $dom_wrapper_id)
    {
        global $DIC;

        $main_tpl->addJavaScript("Modules/MediaCast/Video/js/video_widget.js");
        $this->dom_wrapper_id = $dom_wrapper_id;
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
    }

    function setVideo(?VideoItem $a_val = null) : void
    {
        $this->video = $a_val;
    }

    function getVideo() : ?VideoItem
    {
        return $this->video;
    }

    /**
     * @throws \ilTemplateException
     */
    public function render() : string
    {
        $ui = $this->ui;
        $video_tpl = new \ilTemplate("tpl.widget.html", true, true, "Modules/MediaCast/Video");
        $video_tpl->setVariable("CLASS", " ");

        $tpl = new \ilTemplate("tpl.wrapper.html", true, true, "Modules/MediaCast/Video");
        $tpl->setVariable("ID", $this->dom_wrapper_id);
        $tpl->setVariable("TPL", $video_tpl->get());
        $f = $ui->factory();

        if (!is_null($this->getVideo())) {
            $tpl->setCurrentBlock("loadfile");
            $tpl->setVariable("FID", $this->dom_wrapper_id);
            $video_tpl->setVariable("POSTER", $this->getVideo()->getPreviewPic());
            $tpl->setVariable("FILE", $this->getVideo()->getResource());
            $tpl->setVariable("TITLE", $this->getVideo()->getTitle());
            $tpl->setVariable("DESCRIPTION", $this->getVideo()->getDescription());

        }

        $item = $f->item()->standard('<span data-elementtype="title"></span>')
                  ->withDescription('<span data-elementtype="description-wrapper"><span data-elementtype="description"></span></span>');
        $tpl->setVariable("ITEM",
            $ui->renderer()->render($item));


        /*
        $back = $f->button()->standard("<span class=\"glyphicon glyphicon-chevron-left \" aria-hidden=\"true\"></span>", "")
            ->withOnLoadCode(function ($id) {
                return
                    "$(\"#$id\").click(function() { il.VideoWidget.previous(\"".$this->dom_wrapper_id."\"); return false;});";
        });
        $next = $f->button()->standard("<span class=\"glyphicon glyphicon-chevron-right \" aria-hidden=\"true\"></span>", "")
              ->withOnLoadCode(function ($id) {
                  return
                      "$(\"#$id\").click(function() { il.VideoWidget.next(\"".$this->dom_wrapper_id."\"); return false;});";
        });*/


        /*
        $description_link = $f->button()->shy($this->lng->txt("mcst_show_description"), "")->withOnLoadCode(function ($id) {
            return
                "$(\"#$id\").click(function() { $(document).find(\"[data-elementtype='description']\").removeClass('ilNoDisplay'); $(document).find(\"[data-elementtype='description-trigger']\").addClass('ilNoDisplay'); return false;});";
        });
        $tpl->setVariable("DESCRIPTION_LINK", $ui->renderer()->render($description_link));*/

        //$tpl->setVariable("VIEWCONTROL", $ui->renderer()->render([$back,$next]));

        /*
        $tpl->setCurrentBlock("autoplay");
        $tpl->setVariable("TXT_AUTOPLAY",
            $this->lng->txt("mcst_autoplay"));
        $tpl->parseCurrentBlock();*/

        return $tpl->get();
    }

}