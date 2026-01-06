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

namespace ILIAS\MetaData\OERHarvester\ControlCenter;

use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoFetcherInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\LinkFactoryInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\LinkFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoFetcher;
use ILIAS\MetaData\OERHarvester\ExposedRecords\DatabaseRepository as ExposedRecordsRepository;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\Handler as ObjectHandler;
use ILIAS\MetaData\OERHarvester\Export\Handler as ExportHandler;
use ILIAS\Export\ExportHandler\Factory as ExportService;
use ILIAS\MetaData\OERHarvester\Publisher\Publisher;
use ILIAS\MetaData\OERHarvester\XML\Writer;
use ILIAS\MetaData\OERHarvester\ControlCenter\Http\RequestParser;
use ILIAS\MetaData\OERHarvester\ControlCenter\Content\ContentFactory;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;

class Initiator
{
    protected StateInfoFetcherInterface $state_info_fetcher;
    protected ComponentFactoryInterface $component_factory;

    protected LinkFactoryInterface $link_factory;
    protected PublisherInterface $publisher;
    protected ControlCenterGUI $control_center_gui;

    public function __construct(
        protected InternalServices $services
    ) {
    }

    public function controlCenterGUI(string $link_to_parent): ControlCenterGUI
    {
        $data_factory = new DataFactory();
        $link_to_parent = $data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            ltrim($link_to_parent, '/')
        );

        return $this->control_center_gui ??= new ControlCenterGUI(
            $link_to_parent,
            $this->services->dic()->ctrl(),
            $this->services->dic()->ui()->mainTemplate(),
            $this->services->dic()->ui()->factory(),
            $this->services->dic()->ui()->renderer(),
            new RequestParser(
                $this->services->dic()->http(),
                $this->services->dic()->refinery()
            ),
            new ContentFactory(
                $this->services->dic()->ui()->factory(),
                $this->services->presentation()->utilities(),
                $this->linkFactory(),
                $this->services->copyright()->repository()
            ),
            $this->services->presentation()->utilities(),
            $this->stateInfoFetcher(),
            $this->publisher()
        );
    }

    public function stateInfoFetcher(): StateInfoFetcherInterface
    {
        return $this->state_info_fetcher ??= new StateInfoFetcher(
            $this->services->dic()->access(),
            new ExposedRecordsRepository($this->services->dic()->database()),
            $this->services->oerHarvester()->statusRepository(),
            $this->services->oerHarvester()->settings(),
            new ObjectHandler($this->services->dic()->repositoryTree()),
            $this->services->copyright()->identifiersHandler(),
            $this->publisher(),
            $this->services->repository()->repository(),
            $this->services->paths()->navigatorFactory(),
            $this->services->paths()->pathFactory()
        );
    }

    public function componentFactory(): ComponentFactoryInterface
    {
        return $this->component_factory ??= new ComponentFactory(
            $this->services->dic()->ui()->factory(),
            new DataFactory(),
            $this->linkFactory(),
            $this->services->presentation()->utilities()
        );
    }

    protected function linkFactory(): LinkFactoryInterface
    {
        return $this->link_factory ??= new LinkFactory(
            $this->services->dic()->ctrl()
        );
    }

    protected function publisher(): PublisherInterface
    {
        return $this->publisher ??= new Publisher(
            new ExposedRecordsRepository($this->services->dic()->database()),
            $this->services->oerHarvester()->statusRepository(),
            new ObjectHandler($this->services->dic()->repositoryTree()),
            new ExportHandler(
                $this->services->dic()->user(),
                new ExportService(), // should be replaced by proper API
                new DataFactory()
            ),
            $this->services->oerHarvester()->settings(),
            new Writer(
                $this->services->repository()->repository(),
                $this->services->xml()->simpleDCWriter()
            ),
            $this->services->dic()->access()
        );
    }
}
