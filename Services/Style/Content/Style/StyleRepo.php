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

namespace ILIAS\Style\Content\Style;

use ilDBInterface;
use ILIAS\Style\Content\InternalDataService;
use ILIAS\Exercise\IRSS\CollectionWrapper;

class StyleRepo
{
    protected CollectionWrapper $irss;
    protected ilDBInterface $db;
    protected InternalDataService $factory;

    public function __construct(
        ilDBInterface $db,
        InternalDataService $factory
    ) {
        $this->db = $db;
        $this->factory = $factory;
        // to do: migrate this on merge
        $data = new \ILIAS\Exercise\InternalDataService();
        $this->irss = new CollectionWrapper($data);
    }

    public function readRid(int $style_id) : string
    {
        $set = $this->db->queryF("SELECT rid FROM style_data " .
            " WHERE obj_id = %s ",
            ["integer"],
            [$style_id]
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            return (string) $rec["rid"];
        }
        return "";
    }

    protected function getOrCreateRid(int $style_id) : string
    {
        $rid = $this->readRid($style_id);
        if ($rid === "") {
            $rid = $this->createRid($style_id);
        }
        return $rid;
    }

    public function writeCss(
        string $rid,
        string $css
    )
    {

    }
}
