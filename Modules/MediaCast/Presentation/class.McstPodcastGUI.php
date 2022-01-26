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
     * @var ilCtrl
     */
    protected $ctrl;
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
        $this->ctrl = $DIC->ctrl();
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
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $items = [];
        foreach ($this->media_cast->getSortedItemsArray() as $med_item) {
            $mob = new \ilObjMediaObject($med_item["mob_id"]);
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
                ->withProperties([$this->lng->txt("mcst_duration") => $med_item["playtime"]])
                ->withDescription($mob->getDescription());

            if ($this->media_cast->getDownloadable()) {
                $ctrl->setParameterByClass("ilobjmediacastgui", "item_id", $med_item["id"]);
                $ctrl->setParameterByClass("ilobjmediacastgui", "purpose", "Standard");
                $download = $ctrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem");
                $actions = $f->dropdown()->standard(array(
                    $f->button()->shy($lng->txt("download"), $download),
                ));
                $item = $item->withActions($actions);
            }


            $items[] = $item;
        }

        $list = $f->panel()->listing()->standard($this->lng->txt("mcst_audio_files"), [
            $f->item()->group("", $items)
            ]
        );

        return $renderer->render($list);
    }

}