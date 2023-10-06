<?php

namespace ImportStatus\I;

interface ImportStatusFactoryInterface
{
    public function createImportStatus(): ilImportStatusInterface;
    public function createImportStatusCollection(): ilImportStatusCollectionInterface;
    public function createImportStatusContentBuilder(): ilImportStatusContentBuilderInterface;
}
