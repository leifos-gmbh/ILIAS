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

namespace ILIAS;

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\ComponentLanguageFileDirectory;

class COPage implements Component\Component
{
    public static function getEditorAssetVersion(): string
    {
        return COPage\Setup\EditorAssetVersionObjective::getVersion();
    }

    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $internal[COPage\PC\Definition\Manager::class] = fn() =>
            new COPage\PC\Definition\Manager(
                new COPage\PC\Definition\Repository($seek[COPage\PC\Definition\DefinitionInterface::class]),
                new COPage\PC\Definition\Legacy\Repository($pull[Database\Connection::class]),
            );

        // TODO currently there is no other way to connect this to the legacy (internal) COPage Services
        //  than via AllModernComponents, how lazily is this initialized?
        $provide[COPage\Service\BootstrapToLegacyAdapter::class] = static fn() =>
            new COPage\Service\BootstrapToLegacyAdapter(
                $internal[COPage\Service\BootstrapToLegacyAdapter::class]
            );

        $contribute[Setup\Agent::class] = static fn() =>
            new COPage\Setup\Agent(
                $pull[Refinery\Factory::class]
            );

        // This is included via anonymous classes as a testament to the fact, that
        // the js and css of the COPage should be restructured according to the target
        // structure in the component directory and the public directory.
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/PC/InteractiveImage/js";
            }
            public function getTarget(): string
            {
                $asset_version = COPage\Setup\EditorAssetVersionObjective::getVersionFromSource();
                return "components/ILIAS/COPage/" . $asset_version . "/PC/InteractiveImage/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/PC/Tabs/js";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/PC/Tabs/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/Editor/js";
            }
            public function getTarget(): string
            {
                $asset_version = COPage\Setup\EditorAssetVersionObjective::getVersionFromSource();
                return "components/ILIAS/COPage/" . $asset_version . "/Editor/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/js";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/js";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/css";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/css";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "vendor/ilias/Style/basic_style";
            }
            public function getTarget(): string
            {
                return "components/ILIAS/COPage/basic_style";
            }
        };
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/IntLink/resources/ilIntLink.js";
            }
            public function getTarget(): string
            {
                return "assets/js/ilIntLink.js";
            }
        };
        // this is just a dummy to make the html export to the same
        // target assets/content_style/style.css work
        $contribute[Component\Resource\PublicAsset::class] = static fn() => new class () implements Component\Resource\PublicAsset {
            public function getSource(): string
            {
                return "components/ILIAS/COPage/css/content_base.css";
            }
            public function getTarget(): string
            {
                return "assets/content_style/style.css";
            }
        };

        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'adve'
        );
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'link'
        );
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'copg'
        );
        $contribute[LanguageFileDirectory::class] = fn() => new ComponentLanguageFileDirectory(
            $this,
            'content'
        );
    }
}
