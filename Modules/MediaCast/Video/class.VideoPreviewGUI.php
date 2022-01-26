<?php

// begin patch (whole file) videocast – Killing 22.07.2020

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\MediaCast\Video;

/**
 * Video preview UI
 * @author Alexander Killing <killing@leifos.de>
 */
class VideoPreviewGUI
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $onclick;

    /**
     * @var string
     */
    protected $playing_time;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct($file, $onclick, $playing_time)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->file = $file;
        $this->onclick = $onclick;
        $this->playing_time = $playing_time;
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("mcst");
	}

    /**
     * Render
     * @return string
     * @throws \ilTemplateException
     */
    public function render()
    {
        $lng = $this->lng;

        $tpl = new \ilTemplate("tpl.video_preview.html", true, true, "Modules/MediaCast/Video");
        $im = $this->ui->factory()->image()->responsive($this->file, "");
        $tpl->setVariable("IMAGE", $this->ui->renderer()->render($im));
        $tpl->setVariable("ONCLICK", $this->onclick);
        $tpl->setVariable("PLAYING_TIME", $this->playing_time);
        $tpl->setVariable("WATCHED", $lng->txt("mcst_watched"));

        return $tpl->get();
	}

}