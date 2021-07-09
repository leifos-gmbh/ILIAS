<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

/**
 * Survey internal data service
 *
 * @author killing@leifos.de
 */
class InternalRepoService
{
    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct(InternalDataService $data)
    {
        $this->data = $data;
    }

}
