<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;

use ILIAS\Survey\Mode\FeatureConfig;
use ILIAS\Survey\Mode\ModeFactory;

/**
 * Survey internal domain service
 * @author killing@leifos.de
 */
class InternalDomainService
{
    /**
     * @var ModeFactory
     */
    protected $mode_factory;

    /**
     * @var \ilTree
     */
    protected $repo_tree;

    /**
     * Constructor
     */
    public function __construct(
        ModeFactory $mode_factory
    )
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->repo_tree = $DIC->repositoryTree();

        $this->mode_factory = $mode_factory;
    }

    /**
     * Repository tree
     * @return \ilTree
     */
    public function repositoryTree()
    {
        return $this->repo_tree;
    }

    public function modeFeatureConfig(int $mode) : FeatureConfig
    {
        $mode_provider = $this->mode_factory->getModeById($mode);
        return $mode_provider->getFeatureConfig();
    }

    public function participants() : Participants\DomainService
    {
        return new Participants\DomainService();
    }

}
