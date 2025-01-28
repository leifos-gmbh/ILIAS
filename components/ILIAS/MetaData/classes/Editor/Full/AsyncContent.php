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

namespace ILIAS\MetaData\Editor\Full;

use ILIAS\UI\Component\Prompt\State\State;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleModal;
use ILIAS\MetaData\Editor\Http\RequestInterface;

class AsyncContent
{
    protected FullEditorServices $services;

    public function __construct(
        FullEditorServices $services
    ) {
        $this->services = $services;
    }

    public function contentForEdit(
        PathInterface $base_path,
        ElementInterface $element,
        RequestInterface $request
    ): State {
        if ($element->isScaffold()) {
            return $this->services->actions()->getModal()->createContent(
                $base_path,
                $element,
                $request
            );
        }
        return $this->services->actions()->getModal()->updateContent(
            $base_path,
            $element,
            $request
        );
    }

    public function contentForDelete(
        PathInterface $base_path,
        ElementInterface $element
    ): FlexibleModal {
        return $this->services->actions()->getModal()->deleteContent($base_path, $element);
    }
}
