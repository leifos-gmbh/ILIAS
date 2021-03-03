<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Podcast GUI for mediacasts
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class McstPodcastGUI
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
     * Constructor
     */
    public function __construct(\ilObjMediaCast $obj, $tpl = null)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->media_cast = $obj;
        $this->tpl = $tpl;
        $this->user = $DIC->user();
    }

    /**
     * Get HTML
     * @param
     * @return
     */
    public function getHTML()
    {
        $f = $this->ui->factory();
        $renderer = $this->ui->renderer();

        $items = [];
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $resource = $med->getLocation();
            } else {
                $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                $resource = $path_to_file;
            }

            $audio = $f->audio(
                $resource,
                ""
            );

            $item = $f->item()->standard($mob->getTitle())
                ->withLeadAudio($audio)
                ->withProperties([$this->lng->txt("mcst_duration") => $item["playtime"]])
                ->withDescription($mob->getDescription());

            $items[] = $item;
        }

        $list = $f->panel()->listing()->standard($this->lng->txt("mcst_audio_files"), [
            $f->item()->group("", $items)
            ]
        );

        return $renderer->render($list);
    }

}