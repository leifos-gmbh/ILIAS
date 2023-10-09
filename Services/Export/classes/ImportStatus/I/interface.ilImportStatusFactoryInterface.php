<?php

namespace ImportStatus\I;

interface ilImportStatusFactoryInterface
{
    public function createImportStatus(): ilImportStatusInterface;
    public function createImportStatusCollection(): ilImportStatusCollectionInterface;
    public function createImportStatusContentBuilder(): ilImportStatusContentBuilderInterface;
}
