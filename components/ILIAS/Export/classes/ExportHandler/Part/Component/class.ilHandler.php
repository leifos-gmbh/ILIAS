<?php

namespace ILIAS\Export\ExportHandler\Part\Component;

use ILIAS\Export\ExportHandler\I\Info\Export\Component\ilHandlerInterface as ilExportHanlderExportComponentInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\ilHandlerInterface as ilExportHanlderExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\ilHandlerInterface as ilExportHandlerPartComponentInterface;
use ilXmlWriter;

class ilHandler implements ilExportHandlerPartComponentInterface
{
    protected ilExportHanlderExportInfoInterface $export_info;
    protected ilExportHanlderExportComponentInfoInterface $component_info;

    public function withExportInfo(ilExportHanlderExportInfoInterface $export_info): ilExportHandlerPartComponentInterface
    {
        $clone = clone $this;
        $clone->export_info = $export_info;
        return $clone;
    }

    public function withComponentInfo(ilExportHanlderExportComponentInfoInterface $component_info): ilExportHandlerPartComponentInterface
    {
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
            $xml = $this->component_info->getComponentExporter()->getXmlRepresentation(
                $this->component_info->getTarget()->getComponent(),
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
