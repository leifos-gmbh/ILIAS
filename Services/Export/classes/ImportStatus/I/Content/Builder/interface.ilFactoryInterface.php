<?php

namespace ImportStatus\I\Content\Builder;

use ImportStatus\I\Content\Builder\ilStringInterface as ilImportStatusStringContentBuilderInterface;

interface ilFactoryInterface
{
    public function string(): ilImportStatusStringContentBuilderInterface;
}
