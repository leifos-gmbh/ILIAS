<?php

namespace ImportStatus\I\Content;

use ImportStatus\I\Content\Builder\ilFactoryInterface as ilImportStatusContentBuilderFactoryInterface;

interface ilFactoryInterface
{
    public function builder(): ilImportStatusContentBuilderFactoryInterface;
}
