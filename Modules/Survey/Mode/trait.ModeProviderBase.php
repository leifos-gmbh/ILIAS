<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
trait ModeProviderBase
{
    /**
     * @var ?int
     */
    protected $id = null;

    /**
     * @var FeatureConfig
     */
    protected $feature_config;

    /**
     * @var UIModifier
     */
    protected $ui_modifier;

    public function getId() : int
    {
        return $this->id;
    }

    public function getFeatureConfig() : FeatureConfig
    {
        return $this->feature_config;
    }

    public function getUIModifier() : UIModifier
    {
        return $this->ui_modifier;
    }
}