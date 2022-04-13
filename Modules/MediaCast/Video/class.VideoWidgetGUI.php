<?php

// begin patch (whole file) videocast – Killing 22.07.2020

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaCast\Video;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoWidgetGUI
{
    /**
     * @var string
     */
    protected $dom_wrapper_id;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var VideoItem|null
     */
    protected $video;

    /**
     * Constructor
     */
    public function __construct($main_tpl, $dom_wrapper_id)
    {
        global $DIC;

        $main_tpl->addJavaScript("Modules/MediaCast/Video/js/video_widget.js");
        $this->dom_wrapper_id = $dom_wrapper_id;
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
    }

    /**
     * Set video
     * @param VideoItem|null $a_val video
     */
    function setVideo(VideoItem $a_val = null)
    {
        $this->video = $a_val;
    }

    /**
     * Get video
     * @return VideoItem|null video
     */
    function getVideo()
    {
        return $this->video;
    }


    /**
     * Render
     * @return string
     * @throws \ilTemplateException
     */
    public function render()
    {
        $ui = $this->ui;
        $video_tpl = new \ilTemplate("tpl.widget.html", true, true, "Modules/MediaCast/Video");
        $video_tpl->setVariable("CLASS", " ");

        $tpl = new \ilTemplate("tpl.wrapper.html", true, true, "Modules/MediaCast/Video");
        $tpl->setVariable("ID", $this->dom_wrapper_id);
        $tpl->setVariable("TPL", $video_tpl->get());
        $f = $ui->factory();

        if ($this->getVideo() != "") {
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