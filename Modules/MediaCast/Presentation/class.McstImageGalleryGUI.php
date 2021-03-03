<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Image gallery GUI for mediacasts
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class McstImageGalleryGUI
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

        //Generate some content

        $cards = [];
        $modals = [];
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $resource = $med->getLocation();
            } else {
                $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                $resource = $path_to_file;
            }

            //Define the some responsive image
            $image = $f->image()->responsive(
                $resource,
                $mob->getTitle()
            );

            $page = $f->modal()->lightboxImagePage($image, $mob->getTitle());
            $modal = $f->modal()->lightbox([$page]);

            $card_image = $image->withAction($modal->getShowSignal());

            $sections = ($mob->getDescription())
                ? [$f->legacy($mob->getDescription())]
                : [];

            //$title_button = $f->button()->shy($mob->getTitle(), $modal->getShowSignal());
            $title = $mob->getTitle();

            $card = $f->card()->standard(
                $title,
                $card_image
            )->withSections(
                $sections
            )->withTitleAction($modal->getShowSignal());

            $cards[] = $card;
            $modals[] = $modal;
        }

        $deck = $f->deck($cards);

        return $renderer->render(array_merge([$deck], $modals));
    }

}