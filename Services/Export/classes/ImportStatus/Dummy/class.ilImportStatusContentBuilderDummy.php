<?php

namespace ImportStatus\Dummy;

use ImportStatus\I\ilImportStatusContentBuilderInterface;

class ilImportStatusContentBuilderDummy implements ilImportStatusContentBuilderInterface
{
    public function contentToString(): string
    {
        return 'Dummy';
    }
}
