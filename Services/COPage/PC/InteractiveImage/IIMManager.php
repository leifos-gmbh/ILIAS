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

namespace ILIAS\COPage\PC\InteractiveImage;

use ILIAS\COPage\InternalDomainService;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Handler\HandlerResult;


/**
 * @author Alexander Killing <killing@leifos.de>
 */
class IIMManager
{
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain
    )
    {
        $this->domain = $domain;
    }

    public function handleUploadResult(
        FileUpload $upload,
        UploadResult $result
    ): BasicHandlerResult
    {

        $title = $result->getName();

        $mob = new \ilObjMediaObject();
        $mob->setTitle($title);
        $mob->setDescription("");
        $mob->create();

        $mob->createDirectory();
        $media_item = new \ilMediaItem();
        $mob->addMediaItem($media_item);
        $media_item->setPurpose("Standard");

        $mob_dir = \ilObjMediaObject::_getRelativeDirectory($mob->getId());
        $file_name = \ilObjMediaObject::fixFilename($title);
        $file = $mob_dir . "/" . $file_name;

        $upload->moveOneFileTo(
            $result,
            $mob_dir,
            Location::WEB,
            $file_name,
            true
        );

        // get mime type
        $format = \ilObjMediaObject::getMimeType($file);
        $location = $file_name;

        // set real meta and object data
        $media_item->setFormat($format);
        $media_item->setLocation($location);
        $media_item->setLocationType("LocalFile");
        $mob->update();

        return new BasicHandlerResult(
            "mob_id",
            HandlerResult::STATUS_OK,
            (string) $mob->getId(),
            ''
        );
    }
}
