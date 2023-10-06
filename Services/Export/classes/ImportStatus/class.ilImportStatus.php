<?php

namespace ImportStatus;

use ImportStatus\Dummy\ilImportStatusContentBuilderDummy;
use ImportStatus\I\ilImportStatusContentBuilderInterface;
use ImportStatus\I\ilImportStatusInterface;

class ilImportStatus implements ilImportStatusInterface
{
    private StatusType $type;
    private ilImportStatusContentBuilderInterface $content_builder;

    public function __construct()
    {
        $this->type = StatusType::NONE;
        $this->content_builder = new ilImportStatusContentBuilderDummy();
    }

    public function getType(): StatusType
    {
        return $this->type;
    }

    public function getContentBuilder(): ilImportStatusContentBuilderInterface
    {
        return $this->content_builder;
    }

    public function withType(StatusType $type): ilImportStatusInterface
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function withContentBuilder(ilImportStatusContentBuilderInterface $content_builder): ilImportStatusInterface
    {
        $clone = clone $this;
        $clone->content_builder = $content_builder;
        return $clone;
    }
}
