<?php

namespace ImportStatus\Content\Builder;

use ImportStatus\I\Content\Builder\ilFactoryInterface as ilImportStatusContentBuilderFactoryInterface;
use ImportStatus\I\Content\Builder\ilStringInterface as ilImportStatusStringContentBuilderInterface;
use ImportStatus\Content\Builder\ilString as ilImportStatusStringContentBuilder;

class ilFactory implements ilImportStatusContentBuilderFactoryInterface
{
    public function string(): ilImportStatusStringContentBuilderInterface
    {
        return new ilImportStatusStringContentBuilder();
    }
}
