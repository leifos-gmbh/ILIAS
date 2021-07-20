<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface ModeProvider
{
    public function getId() : int;

    public function getFeatureConfig() : FeatureConfig;

    public function getUIModifier() : UIModifier;
}