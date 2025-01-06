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

namespace ILIAS\components\Export\HTML;

use ILIAS\Export\HTML\ExportFileDBRepository;
use ILIAS\Export\HTML\DataService;

class ExportCollector
{
    protected string $rid = "";

    public function __construct(
        protected DataService $data,
        protected ExportFileDBRepository $repo,
        protected int $obj_id,
        protected string $type = ""
    )
    {
    }

    /**
     * @throws ExportException
     */
    public function init(string $zipname = "") : string
    {
        if ($this->rid !== "") {
            throw $this->data->exportException("HTML Export has been initialised twice.");
        }
        $this->rid = $this->repo->create(
            $this->obj_id,
            $this->type
        );

        if ($zipname === "") {
            $date = time();
            $zipname = $date . "__" . IL_INST_ID . "__" .
                \ilObject::_lookupType($this->obj_id) . "_" . $this->obj_id . ".zip";
        }
        //$this->repo->rename($this->rid, $zipname);

        return $this->rid;
    }

    /**
     * @throws ExportException
     */
    public function addString(
        string $content,
        string $path
    ): void {
        if ($this->rid === "") {
            throw $this->data->exportException("HTML Export has not been initialised.");
        }
        $this->repo->addString(
            $this->rid,
            $content,
            $path
        );
    }
}