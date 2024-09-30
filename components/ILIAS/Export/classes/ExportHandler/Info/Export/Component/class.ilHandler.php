<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Export\ExportHandler\Info\Export\Component;

use ilExport;
use ilExportException;
use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\ilCollectionInterface as ilExportHandlerExportComponentInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\ilHandlerInterface as ilExportHandlerExportComponentInfoInterface;
use ILIAS\Export\ExportHandler\I\Target\ilHandlerInterface as ilExportHandlerTargetInterface;
use ilXmlExporter;

class ilHandler implements ilExportHandlerExportComponentInfoInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilXmlExporter $exporter;
    protected ilExportHandlerTargetInterface $export_target;
    protected array $sv;
    protected string $path_in_container;

    public function __construct(ilExportHandlerFactoryInterface $export_handler)
    {
        $this->export_handler = $export_handler;
        $this->sv = [];
    }

    protected function init(): void
    {
        $component = $this->getTarget()->getComponent() === "components/ILIAS/Object" ? "components/ILIAS/ILIASObject" : $this->getTarget()->getComponent();
        $class_name = $this->getTarget()->getClassname() === "ilObjectExporter" ? "ilILIASObjectExporter" : $this->getTarget()->getClassname();
        if (!class_exists($class_name)) {
            $export_class_file = "./" . $component . "/classes/class." . $class_name . ".php";
            if (!is_file($export_class_file)) {
                throw new ilExportException('Export class file "' . $export_class_file . '" not found.');
            }
        }
        $this->exporter = new ($class_name)();
        $this->exporter->setExport(new ilExport());
        $this->exporter->init();
        $this->sv = $this->exporter->determineSchemaVersion($component, $this->getTarget()->getTargetRelease());
        $this->sv["uses_dataset"] ??= false;
        $this->sv['xsd_file'] ??= '';
    }

    public function withExportTarget(ilExportHandlerTargetInterface $export_target): ilExportHandlerExportComponentInfoInterface
    {
        $clone = clone $this;
        $clone->export_target = $export_target;
        $clone->init();
        return $clone;
    }

    public function withPathInContainer(string $path_in_container): ilExportHandlerExportComponentInfoInterface
    {
        $clone = clone $this;
        $clone->path_in_container = $path_in_container;
        return $clone;
    }

    public function getTarget(): ilExportHandlerTargetInterface
    {
        return $this->export_target;
    }

    public function getPathInContainer(): string
    {
        return $this->path_in_container;
    }

    public function getXSDSchemaLocation(): string
    {
        $schema_location = "http://www.ilias.de/Services/Export/exp/4_1 " . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/ilias_export_4_1.xsd";
        if ($this->usesCustomNamespace()) {
            $schema_location .= " " . $this->sv["namespace"] . " " . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/" . $this->sv["xsd_file"];
        }
        if ($this->usesDataset()) {
            $schema_location .= " " . "http://www.ilias.de/Services/DataSet/ds/4_3 " . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/ilias_ds_4_3.xsd";
        }
        return $schema_location;
    }

    public function getComponentExporter(): ilXmlExporter
    {
        return $this->exporter;
    }

    protected function getComponentInfos(array $sequence): ilExportHandlerExportComponentInfoCollectionInterface
    {
        $component_infos = $this->export_handler->info()->export()->component()->collection();
        foreach ($sequence as $s) {
            $comp = explode("/", $s["component"]);
            $component = str_replace("_", "", $comp[2]);
            $exp_class = "il" . $component . "Exporter";
            $component_infos = $component_infos->withComponent((new ilHandler($this->export_handler))->withExportTarget(
                $this->export_handler->target()->handler()
                    ->withClassname($exp_class)
                    ->withComponent($s["component"])
                    ->withType($s["entity"])
                    ->withTargetRelease($this->getTarget()->getTargetRelease())
                    ->withObjectIds((array) $s["ids"])
            ));
        }
        return $component_infos;
    }

    public function getHeadComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface
    {
        return $this->getComponentInfos($this->getComponentExporter()->getXmlExportHeadDependencies(
            $this->getTarget()->getType(),
            $this->getTarget()->getTargetRelease(),
            $this->getTarget()->getObjectIds()
        ));
    }

    public function getSchemaVersion(): string
    {
        return $this->sv["schema_version"] ?? "";
    }

    public function getTailComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface
    {
        return $this->getComponentInfos($this->getComponentExporter()->getXmlExportTailDependencies(
            $this->getTarget()->getType(),
            $this->getTarget()->getTargetRelease(),
            $this->getTarget()->getObjectIds()
        ));
    }

    public function getNamespace(): string
    {
        return $this->sv["namespace"];
    }

    public function getDatasetNamespace(): string
    {
        return "http://www.ilias.de/Services/DataSet/ds/4_3";
    }

    public function usesDataset(): bool
    {
        return $this->sv["uses_dataset"];
    }

    public function usesCustomNamespace(): bool
    {
        return ($this->sv["namespace"] ?? "") !== "" && ($this->sv["xsd_file"] ?? "") !== "";
    }
}
