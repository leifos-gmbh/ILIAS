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
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

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
        $this->toolbar = $DIC->toolbar();
    }

    /**
     * Execute command
     */
    function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, array("downloadAll"))) {
                    $this->$cmd();
                }
        }
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
        $toolbar = $this->toolbar;

        // toolbar
        $toolbar->setFormAction($ctrl->getFormAction($this));
        if ($this->media_cast->getDownloadable()) {
            $toolbar->addFormButton($lng->txt("mcst_download_all"), "downloadAll");
        }

        // cards and modals
        $cards = [];
        $modals = [];

        $pages = [];
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $resource = $med->getLocation();
            } else {
                $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                $resource = ilWACSignedPath::signFile($path_to_file);
            }

            $image = $f->image()->responsive(
                $resource,
                $mob->getTitle()
            );

            $pages[] = $f->modal()->lightboxImagePage($image, $mob->getTitle());
        }
        $main_modal = $f->modal()->lightbox($pages);

        $cnt = 0;
        foreach ($this->media_cast->getSortedItemsArray() as $item) {
            $mob = new \ilObjMediaObject($item["mob_id"]);
            $med = $mob->getMediaItem("Standard");

            if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                $resource = $med->getLocation();
            } else {
                $path_to_file = \ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                $resource = ilWACSignedPath::signFile($path_to_file);
            }
            $preview_resource = $resource;
            if ($mob->getVideoPreviewPic() != "") {
                $preview_resource =ilWACSignedPath::signFile($mob->getVideoPreviewPic());
            }


            $preview_image = $f->image()->responsive(
                $preview_resource,
                $mob->getTitle()
            );

            $image = $f->image()->responsive(
                $resource,
                $mob->getTitle()
            );

            $modal = $main_modal;

            $card_image = $preview_image->withAction($modal->getShowSignal());
            $card_image = $card_image->withAdditionalOnLoadCode(function ($id) use ($cnt) {
                return "$('#$id').click(function(e) { document.querySelector('.modal-body .carousel [data-slide-to=\"".$cnt."\"]').click(); });";
            });
            $cnt++;

            $sections = ($mob->getDescription())
                ? [$f->legacy($mob->getDescription())]
                : [];

            if ($this->media_cast->getDownloadable()) {
                $ctrl->setParameterByClass("ilobjmediacastgui", "item_id", $item["id"]);
                $ctrl->setParameterByClass("ilobjmediacastgui", "purpose", "Standard");
                $download = $ctrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem");
                $sections[] = $f->button()->standard($lng->txt("download"), $download);
            }

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

        if (count($pages) == 0) {
            return "";
        }
        return $renderer->render(array_merge([$deck], [$main_modal]));
    }

    /**
     * Download all
     */
    protected function downloadAll()
    {
        $user = $this->user;
        include_once ("./Modules/MediaCast/BackgroundTasks/DownloadAllBackgroundTask.php");
        $download_task = new \ILIAS\MediaCast\BackgroundTasks\DownloadAllBackgroundTask(
            (int) $user->getId(),
            (int) $this->media_cast->getRefId(),
            (int) $this->media_cast->getId()
        );

        if ($download_task->run()) {
            ilUtil::sendSuccess($this->lng->txt('mcst_download_started_bg'), true);
        }

        $this->ctrl->redirectByClass("ilobjmediacastgui", "showContent");
    }

}