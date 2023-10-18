<?php

namespace ImportStatus\Content;

use ImportStatus\I\Content\Builder\ilFactoryInterface as ilImportStatusContentBuilderFactoryInterface;
use ImportStatus\I\Content\ilFactoryInterface as ilImportStatusContentFactoryInterface;
use ImportStatus\Content\Builder\ilFactory as ilImportStatusContentBuilderFactory;

class ilFactory implements ilImportStatusContentFactoryInterface
{
    public function builder(): ilImportStatusContentBuilderFactoryInterface
    {
        return new ilImportStatusContentBuilderFactory();
    }
}
