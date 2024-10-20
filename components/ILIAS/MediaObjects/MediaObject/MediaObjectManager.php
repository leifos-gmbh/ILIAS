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

namespace ILIAS\MediaObjects;

use ilDBInterface;
use ILIAS\Exercise\IRSS\IRSSWrapper;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Util\Convert\Images;
use ILIAS\Filesystem\Util\Convert\ImageOutputOptions;

class MediaObjectManager
{
    protected ImageOutputOptions $output_options;
    protected Images $image_converters;
    protected MediaObjectRepository $repo;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected \ilMobStakeholder $stakeholder
    )
    {
        $this->repo = $repo->mediaObject();
        $this->image_converters = new Images(false);
        $this->output_options = new ImageOutputOptions();
    }

    public function create(
        int $id,
        string $title
    ): void
    {
        $this->repo->create(
            $id,
            $title,
            $this->stakeholder
        );
    }

    public function addFileFromLegacyUpload(int $mob_id, string $tmp_name) : void
    {
        $this->repo->addFileFromLegacyUpload($mob_id, $tmp_name);
    }

    public function addFileFromUpload(int $mob_id, UploadResult $result) : void
    {
        $this->repo->addFileFromUpload($mob_id, $result);
    }

    public function getLocationSrc(int $mob_id, string $location):string
    {
        return $this->repo->getLocationSrc($mob_id, $location);
    }

    public function generatePreview(
        int $mob_id,
        string $std_location
    )
    {

        $converter = $this->image_converters->resizeToFixedSize(
            $this->buildStream($path_to_original),
            $width,
            $height,
            $crop_if_true_and_resize_if_false,
            $this->output_options
                ->withQuality($image_quality)
                ->withFormat(ImageOutputOptions::FORMAT_PNG)
        );
        return $this->storeStream($converter, $path_to_output);
    }

}
