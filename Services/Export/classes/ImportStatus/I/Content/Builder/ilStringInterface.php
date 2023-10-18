<?php

namespace ImportStatus\I\Content\Builder;

use ImportStatus\I\Content\ilHandlerInterface as ilImportStatusContentHandlerInterface;

interface ilStringInterface extends ilImportStatusContentHandlerInterface
{
    public function withString(string $content);
}
