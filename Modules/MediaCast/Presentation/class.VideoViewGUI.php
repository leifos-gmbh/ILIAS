<?php

// begin patch (whole file) videocast – Killing 22.07.2020

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaCast\Presentation;

use ILIAS\MediaCast\Video\VideoWidgetGUI;
use ILIAS\MediaCast\Video\VideoSequence;
use ILIAS\MediaCast\Video\VideoPreviewGUI;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoViewGUI
{
    /**
     * @var \ilObjMediaCast
     */
    protected $media_cast;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var string
     */
    protected $completed_callback = "";

    /**
     * @var string 
     */
    protected $autoplay_callback = "";

    /**
     * @var VideoSequence 
     */
    protected $video_sequence;

    /**
     * Constructor
     */
    public function __construct(\ilObjMediaCast $obj, $tpl = null)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->media_cast = $obj;
        $this->tpl = $tpl;
        include_once("./Modules/MediaCast/Video/class.VideoSequence.php");
        $this->video_sequence = new VideoSequence($this->media_cast);
        $this->user = $DIC->user();
    }

    /**
     *
     * @param string
     */
    public function setCompletedCallback($completed_callback)
    {
        $this->completed_callback = $completed_callback;
    }

    /**
     *
     * @param string
     */
    public function setAutoplayCallback($autoplay_callback)
    {
        $this->autoplay_callback = $autoplay_callback;
    }

    /**
     * Render Main column
     * @return string
     * @throws \ilTemplateException
     */
    public function renderMainColumn()
    {
        if (count($this->video_sequence->getVideos()) == 0) {
            return "";
        }


        include_once("./Modules/MediaCast/Video/class.VideoWidgetGUI.php");
        $widget = new VideoWidgetGUI($this->tpl, "mcst_video");
        //$widget->setVideo($this->video_sequence->getFirst());
        return $widget->render();
    }

    /**
     * Render side column
     * @param
     * @return
     */
    public function renderSideColumn()
    {

        $mcst_settings = \ilMediaCastSettings::_getInstance();

        $autoplay = ($this->user->existsPref("mcst_autoplay"))
            ? (bool) $this->user->getPref("mcst_autoplay")
            : ($this->media_cast->getAutoplayMode() == \ilObjMediaCast::AUTOPLAY_ACT);
        if ($this->media_cast->getAutoplayMode() == \ilObjMediaCast::AUTOPLAY_NO) {
            $autoplay = false;
        }

        $lng = $this->lng;
        $tpl = new \ilTemplate("tpl.video_cast_side.html", true, true, "Modules/MediaCast/Presentation");
        include_once("./Modules/MediaCast/Video/class.VideoPreviewGUI.php");

        $factory = $this->ui->factory();
        $renderer = $this->ui->renderer();

        // autoplay
        if ($this->media_cast->getAutoplayMode() != \ilObjMediaCast::AUTOPLAY_NO) {
            $s = new SignalGenerator();
            $autoplay_on = $s->create();
            $autoplay_off = $s->create();
            $button = $factory->button()->toggle("", $autoplay_on, $autoplay_off, $autoplay);

            $tpl->setCurrentBlock("autoplay");
            $tpl->setVariable("TXT_AUTOPLAY", $this->lng->txt("mcst_autoplay"));
            $tpl->setVariable("AUTOPLAY_ON", $autoplay_on->getId());
            $tpl->setVariable("AUTOPLAY_OFF", $autoplay_off->getId());
            $tpl->setVariable("TOGGLE",
                $renderer->render([$button]));
            $tpl->parseCurrentBlock();
        }


        // items
        $items = [];
        $has_items = false;
        foreach($this->video_sequence->getVideos() as $video) {
            $has_items = true;
            $preview = new VideoPreviewGUI($video->getPreviewPic(),
                "il.VideoPlaylist.loadItem('mcst_playlist', '".$video->getId()."', true);",
                $video->getPlayingTime());
            $completed = false;

            $re = \ilChangeEvent::_lookupReadEvents($video->getId(), $this->user->getId());
            if (count($re) > 0) {
                if ($re[0]["read_count"] > 0) {
                    $completed = true;
                }
            }

            $b = $factory->button()->shy($video->getTitle(), "")->withOnLoadCode(function ($id) use ($video){
                return
                    "$(\"#$id\").click(function() { il.VideoPlaylist.loadItem('mcst_playlist', '".$video->getId()."', true); return false;});";
            });

            $items[] = [
                "id" => $video->getId(),
                "resource" => $video->getResource(),
                "preview" => $preview->render(),
                "title" => $video->getTitle(),
                "linked_title" => $renderer->renderAsync($b),
                "mime" => $video->getMime(),
                "poster" => $video->getPreviewPic(),
                "description" => nl2br($video->getDescription()),
                "completed" => $completed,
                "duration" => $video->getDuration()
            ];
        }

        // previous items / next items links
        if ($has_items) {
            $tpl->setVariable(
                "PREV",
                $renderer->render(
                    $factory->button()->shy($lng->txt("mcst_prev_items"), "")->withOnLoadCode(
                        function ($id) {
                            return
                                "$(\"#$id\").click(function() { il.VideoPlaylist.previousItems('mcst_playlist'); return false;});";
                        }
                    )
                )
            );
            $tpl->setVariable(
                "NEXT",
                $renderer->render(
                    $factory->button()->shy($lng->txt("mcst_next_items"), "")->withOnLoadCode(
                        function ($id) {
                            return
                                "$(\"#$id\").click(function() { il.VideoPlaylist.nextItems('mcst_playlist'); return false;});";
                        }
                    )
                )
            );

            $item_tpl = new \ilTemplate("tpl.playlist_item.html", true, true, "Modules/MediaCast/Video");
            $item_tpl->setVariable("TITLE", " ");
            $item_content = str_replace("\n", "", $item_tpl->get());

            $this->tpl->addOnLoadCode(
                "il.VideoPlaylist.init('mcst_playlist', 'mcst_video', " . json_encode(
                    $items
                ) . ", '$item_content', " . ($autoplay ? "true" : "false") . ", " .
                (int) $this->media_cast->getNumberInitialVideos(
                ) . ", '" . $this->completed_callback . "', '" . $this->autoplay_callback . "', ".((int) $mcst_settings->getVideoCompletionThreshold()).");"
            );

            if (count($items) === 1) {
                return "";
            }

            return $tpl->get();
        }

        return "";
    }

    /**
     * Render
     * @return string
     * @throws \ilTemplateException
     */
    public function render()
    {
        // this is current only to include the resize mechanism when
        // the main menu is changed, so that the player is resized, too
        \ilMediaPlayerGUI::initJavascript();
        $tpl = new \ilTemplate("tpl.video_cast_layout.html", true, true, "Modules/MediaCast/Presentation");
        $side_column = $this->renderSideColumn();

        if ($side_column != "") {
            $tpl->setCurrentBlock("with_side_column");
            $tpl->setVariable("SIDE", $side_column);
        } else {
            $tpl->setCurrentBlock("video_only");
        }
        $tpl->setVariable("MAIN", $this->renderMainColumn());
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    /**
     * Render content into standard template
     */
    public function show()
    {
        if (is_object($this->tpl)) {
            $this->tpl->setContent($this->render());
        }
    }

}