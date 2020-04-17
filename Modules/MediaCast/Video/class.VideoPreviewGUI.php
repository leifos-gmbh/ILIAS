<?php

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
     * Constructor
     */
    public function __construct($file, $onclick, $playing_time)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->file = $file;
        $this->onclick = $onclick;
        $this->playing_time = $playing_time;
	}

    /**
     * Render
     * @return string
     * @throws \ilTemplateException
     */
    public function render()
    {
        $tpl = new \ilTemplate("tpl.video_preview.html", true, true, "Modules/MediaCast/Video");
        $im = $this->ui->factory()->image()->responsive($this->file, "");
        $tpl->setVariable("IMAGE", $this->ui->renderer()->render($im));
        $tpl->setVariable("ONCLICK", $this->onclick);
        $tpl->setVariable("PLAYING_TIME", $this->playing_time);

        return $tpl->get();
	}

}