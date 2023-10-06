<?php

namespace ImportStatus\I;

use ImportStatus\StatusType;

interface ilImportStatusInterface
{
    public function getType(): StatusType;

    public function getContentBuilder(): ilImportStatusContentBuilderInterface;

    public function withType(StatusType $type): ilImportStatusInterface;

    public function withContentBuilder(ilImportStatusContentBuilderInterface $content_builder): ilImportStatusInterface;
}
