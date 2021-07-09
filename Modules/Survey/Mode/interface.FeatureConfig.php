<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface FeatureConfig
{
    public function supportsCompetences() : bool;

    public function supportsConstraints() : bool;

    public function supportsAccessCodes() : bool;

    public function supportsTutorNotification() : bool;

    public function supportsMemberReminder() : bool;

    public function supportsSumScore() : bool;
}