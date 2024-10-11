<?php

namespace ILIAS\Export\ExportHandler\Part\Component;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\HandlerInterface as ilExportHanlderExportComponentInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\HandlerInterface as ilExportHanlderExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\HandlerInterface as ilExportHandlerPartComponentInterface;
use ilXmlWriter;

class Handler implements ilExportHandlerPartComponentInterface
{
    protected ilExportHanlderExportInfoInterface $export_info;
    protected ilExportHanlderExportComponentInfoInterface $component_info;
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function withExportInfo(
        ilExportHanlderExportInfoInterface $export_info
    ): ilExportHandlerPartComponentInterface {
        $clone = clone $this;
        $clone->export_info = $export_info;
        return $clone;
    }

    public function withComponentInfo(
        ilExportHanlderExportComponentInfoInterface $component_info
    ): ilExportHandlerPartComponentInterface {
        $clone = clone $this;
        $clone->component_info = $component_info;
        return $clone;
    }

    public function getXML(bool $formatted = true): string
    {
        $attribs = array("InstallationId" => $this->export_info->getInstallationId(),
            "InstallationUrl" => $this->export_info->getHTTPPath(),
            "Entity" => $this->component_info->getTarget()->getComponent(),
            "SchemaVersion" => $this->component_info->getSchemaVersion(),
            /* "TargetRelease" => $a_target_release, */
            "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
            "xmlns:exp" => "http://www.ilias.de/Services/Export/exp/4_1",
            "xsi:schemaLocation" => $this->component_info->getXSDSchemaLocation()
        );
        if ($this->component_info->usesCustomNamespace()) {
            $attribs["xmlns"] = $this->component_info->getNamespace();
        }
        if ($this->component_info->usesDataset()) {
            $attribs["xmlns:ds"] = $this->component_info->getDatasetNamespace();
        }
        $export_writer = new ilXmlWriter();
        $export_writer->xmlHeader();
        $export_writer->xmlStartTag('exp:Export', $attribs);
        foreach ($this->component_info->getTarget()->getObjectIds() as $id) {
            $export_writer->xmlStartTag('exp:ExportItem', array("Id" => $id));
            $comp_exporter = $this->component_info->getComponentExporter($this->export_info->getCurrentElement());
            $xml = $comp_exporter->getXmlRepresentation(
                $this->component_info->getTarget()->getType(),
                $this->component_info->getSchemaVersion(),
                (string) $id
            );
            $export_writer->appendXML($xml);
            $export_writer->xmlEndTag('exp:ExportItem');
        }
        $export_writer->xmlEndTag('exp:Export');
        return $export_writer->xmlDumpMem($formatted);
    }
}
