<?php

namespace ImportStatus;

use ImportStatus\I\ilImportStatusContentBuilderInterface;

class ilImportStatusContentBuilderString implements ilImportStatusContentBuilderInterface
{
    private string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function contentToString(): string
    {
        return $this->content;
    }
}
