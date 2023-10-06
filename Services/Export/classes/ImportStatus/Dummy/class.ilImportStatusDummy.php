<?php

namespace ImportStatus\Dummy;

use ImportStatus\I\ilImportStatusContentBuilderInterface;
use ImportStatus\I\ilImportStatusInterface;
use ImportStatus\StatusType;

class ilImportStatusDummy implements ilImportStatusInterface
{
    public function getType(): StatusType
    {
        return StatusType::DUMMY;
    }

    public function getContentBuilder(): ilImportStatusContentBuilderInterface
    {
        return new ilImportStatusContentBuilderDummy();
    }

    public function withType(StatusType $type): ilImportStatusInterface
    {
        return clone $this;
    }

    public function withContentBuilder(ilImportStatusContentBuilderInterface $content_builder): ilImportStatusInterface
    {
        return clone $this;
    }
}
