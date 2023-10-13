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

namespace ILIAS\Exercise\IRSS;

use \ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

class CollectionWrapper
{
    public function __construct()
    {
        global $DIC;

        $this->irss = $DIC->resourceStorage();
        $this->upload = $DIC->upload();
    }

    public function getNewCollectionId() :ResourceCollectionIdentification
    {
        return $this->irss->collection()->id();
    }

    public function getNewCollectionIdAsString() : string
    {
        return $this->getNewCollectionId()->serialize();
    }

    public function getCollectionForIdString(string $rcid) : ResourceCollection
    {
        return $this->irss->collection()->get($this->irss->collection()->id($rcid));
    }

    public function importFilesFromLegacyUploadToCollection(
        ResourceCollection $collection,
        array $file_input,
        ResourceStakeholder $stakeholder
    ) : void
    {
        $upload = $this->upload;

        if (is_array($file_input)) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            foreach ($upload->getResults() as $name => $result) {
                // we must check if these are files from this input
                if (!in_array($name, $file_input["tmp_name"] ?? [], true)) {
                    continue;
                }
                // if the result is not OK, we skip it
                if (!$result->isOK()) {
                    continue;
                }

                // we store the file in the IRSS
                $rid = $this->irss->manage()->upload(
                    $result,
                    $stakeholder
                );
                // and add its identification to the collection
                $collection->add($rid);
            }
            // we store the collection after all files have been added
            $this->irss->collection()->store($collection);
        }
    }


}