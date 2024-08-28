<?php

namespace ILIAS\Export\ExportHandler\Part\Component;

use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\ilFactoryInterface as ilExportHanlderPartComponentFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\ilHandlerInterface as ilExportHandlerComponentInterface;
use ILIAS\Export\ExportHandler\Part\Component\ilHandler as ilExportHandlerComponent;

class ilFactory implements ilExportHanlderPartComponentFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerComponentInterface
    {
        return new ilExportHandlerComponent();
    }
}
