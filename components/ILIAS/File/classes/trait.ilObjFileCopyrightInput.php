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

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
trait ilObjFileCopyrightInput
{
    public function getCopyrightSelectionInput(string $lang_var_title): FormInput
    {
        static $copyright_input;
        // we chache the input field to avoid multiple creation in lists
        if ($copyright_input !== null) {
            return $copyright_input;
        }

        $copyright_input = $this->lom_services->copyrightHelper()
            ->getCopyrightInputBuilder()
            ->withNonFeaturedEntries()
            ->withCustomInput()
            ->get()
            ->withLabel($this->getLanguage()->txt($lang_var_title));
        return $copyright_input;
    }

    abstract protected function getUIFactory(): Factory;

    abstract protected function getLanguage(): \ilLanguage;
}
