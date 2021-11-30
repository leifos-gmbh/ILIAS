<?php declare(strict_types = 1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Style\Content;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    /**
     * @var InternalDataService
     */
    protected $data_service;

    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
    }

    // get class name of object settings gui class
    public function objectSettingsClass(bool $lower = true) : string
    {
        $class = \ilObjectContentStyleSettingsGUI::class;
        if ($lower) {
            $class = strtolower($class);
        }
        return $class;
    }

    // get instance of objecgt settings gui class
    public function objectSettingsGUI(
        ?int $selected_style_id,
        int $ref_id,
        int $obj_id = 0) : \ilObjectContentStyleSettingsGUI
    {
        return new \ilObjectContentStyleSettingsGUI(
            $this->domain_service,
            $this,
            $selected_style_id,
            $ref_id,
            $obj_id
        );
    }
}
