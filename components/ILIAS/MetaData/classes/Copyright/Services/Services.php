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

namespace ILIAS\MetaData\Copyright\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Search\Services\Services as SearchServices;
use ILIAS\MetaData\Paths\Services\Services as PathsServices;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\DatabaseRepository;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Copyright\Renderer;
use ILIAS\MetaData\Copyright\Database\Wrapper;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface;
use ILIAS\MetaData\Copyright\Identifiers\Handler;
use ILIAS\MetaData\Copyright\Search\Factory as SearcherFactory;
use ILIAS\MetaData\Copyright\Search\FactoryInterface as SearcherFactoryInterface;
use ILIAS\MetaData\Copyright\Input\Factory as InputFactory;
use ILIAS\MetaData\Copyright\Input\FactoryInterface as InputFactoryInterface;
use ILIAS\MetaData;

class Services
{
    protected RepositoryInterface $repository;
    protected RendererInterface $renderer;
    protected HandlerInterface $handler;
    protected SearcherFactoryInterface $searcher_factory;
    protected InputFactoryInterface $input_factory;

    public function __construct(
        protected GlobalContainer $dic,
        protected SearchServices $search_services,
        protected PathsServices $paths_services
    ) {
    }

    public function repository(): RepositoryInterface
    {
        return $this->repository ??= new DatabaseRepository(
            new Wrapper($this->dic->database())
        );
    }

    public function renderer(): RendererInterface
    {
        return $this->renderer ??= new Renderer(
            $this->dic->ui()->factory(),
            $this->dic->resourceStorage()
        );
    }

    public function identifiersHandler(): HandlerInterface
    {
        return $this->handler ??= new Handler();
    }

    public function searcherFactory(): SearcherFactoryInterface
    {
        return $this->searcher_factory ??= new SearcherFactory(
            $this->search_services->searchFilterFactory(),
            $this->search_services->searchClauseFactory(),
            $this->paths_services->pathFactory(),
            $this->identifiersHandler()
        );
    }

    public function inputFactory(): InputFactoryInterface
    {
        return $this->input_factory ??= new InputFactory(
            $this->repository(),
            $this->identifiersHandler(),
            new \ILIAS\MetaData\Presentation\Utilities(
                $this->dic->language(),
                $this->dic->user(),
                $this->dic->refinery()
            ),
            $this->dic->ui()->factory(),
            $this->dic->refinery()
        );
    }
}
