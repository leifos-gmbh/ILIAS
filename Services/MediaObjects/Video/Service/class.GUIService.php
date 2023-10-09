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

namespace ILIAS\MediaObjects\Video;

use ILIAS\MediaObjects\InternalGUIService;
use ILIAS\MediaObjects\InternalDomainService;

class GUIService
{
    protected \ilGlobalTemplateInterface $tpl;
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
        $this->tpl = $gui_service->ui()->mainTemplate();
    }

    public function addPreviewExtractionToToolbar(
        int $mob_id,
        string $gui_class,
        string $extract_cmd = "extractPreviewImage"
    ) : void {
        $toolbar = $this->gui_service->toolbar();
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->domain_service->lng();

        if (\ilFFmpeg::enabled()) {
            $mob = new \ilObjMediaObject($mob_id);

            $conv_cnt = 0;
            // we had other purposes as source as well, but
            // currently only "Standard" is implemented in the convertFile method
            $p = "Standard";
            $med = $mob->getMediaItem($p);
            if (is_object($med)) {
                if (\ilFFmpeg::supportsImageExtraction($med->getFormat())) {
                    // second
                    $ni = new \ilTextInputGUI($lng->txt("mcst_second"), "sec");
                    $ni->setMaxLength(4);
                    $ni->setSize(4);
                    $ni->setValue(1);
                    $toolbar->addInputItem($ni, true);

                    $toolbar->addFormButton($lng->txt("mcst_extract_preview_image"), "extractPreviewImage");
                    $toolbar->setFormAction($ctrl->getFormActionByClass($gui_class));
                }
            }
        }
    }

    public function handleExtractionRequest(
        int $mob_id
    ) : void
    {
        $mob = new \ilObjMediaObject($mob_id);
        $lng = $this->domain_service->lng();
        $add = "";
        try {
            $sec = $this->gui_service->standardRequest()->getSeconds();
            if ($sec < 0) {
                $sec = 0;
            }

            $mob->generatePreviewPic(320, 240, $sec);
            if ($mob->getVideoPreviewPic() !== "") {
                $this->tpl->setOnScreenMessage('info', $lng->txt("mcst_image_extracted"), true);
            } else {
                $this->tpl->setOnScreenMessage('failure', $lng->txt("mcst_no_extraction_possible"), true);
            }
        } catch (\ilException $e) {
            if (DEVMODE === 1) {
                $ret = \ilFFmpeg::getLastReturnValues();
                $add = (is_array($ret) && count($ret) > 0)
                    ? "<br />" . implode("<br />", $ret)
                    : "";
            }
            $this->tpl->setOnScreenMessage('failure', $e->getMessage() . $add, true);
        }
    }
}
