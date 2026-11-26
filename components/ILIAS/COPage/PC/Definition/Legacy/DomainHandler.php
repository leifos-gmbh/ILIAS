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

namespace ILIAS\COPage\PC\Definition\Legacy;

use DOMDocument;
use stdClass;
use ilPageObject;
use ILIAS\COPage\PC\Definition\Domain\HandlerInterface;
use ilPageContent;
use ILIAS\COPage\PC\Definition\Domain\Environment\EnvironmentInterface;

// TODO maybe move this out of definition?
class DomainHandler implements HandlerInterface
{
    public function __construct(
        protected EnvironmentInterface $environment,
        protected ilPageContent $content
    ) {
    }

    public function modifyPageContentPostXsl(string $output): string
    {
        return $this->content->modifyPageContentPostXsl(
            $output,
            $this->environment->context()->outputMode()->value,
            $this->environment->context()->abstractOnly()
        );
    }

    /**
     * TODO clean up parameters
     */
    public function handleCopiedContent(
        DOMDocument $temporary_document,
        bool $self_ass,
        bool $clone_mobs,
        int $new_parent_id,
        int $obj_copy_id
    ): void {
        $this->content::handleCopiedContent(
            $temporary_document,
            $self_ass,
            $clone_mobs,
            $new_parent_id,
            $obj_copy_id
        );
    }

    /**
     * TODO clean up parameters
     */
    public function afterPageCreateOrUpdate(
        ilPageObject $page,
        DOMDocument $domdoc,
        string $xml,
        bool $creation
    ): void {
        $this->content::afterPageUpdate(
            $page,
            $domdoc,
            $xml,
            $creation
        );
    }

    /**
     * TODO clean up parameters
     */
    public function beforePageDelete(
        ilPageObject $page
    ): void {
        $this->content::beforePageDelete($page);
    }

    /**
     * TODO clean up parameters
     */
    public function afterRepositoryCopy(
        ilPageObject $page,
        array $mapping,
        int $source_ref_id
    ): void {
        $this->content::afterRepositoryCopy(
            $page,
            $mapping,
            $source_ref_id
        );
    }

    /**
     * TODO clean up parameters
     */
    public function afterPageHistoryEntry(
        ilPageObject $page,
        DOMDocument $old_domdoc,
        string $old_xml,
        int $old_nr
    ): void {
        $this->content::afterPageHistoryEntry(
            $page,
            $old_domdoc,
            $old_xml,
            $old_nr
        );
    }

    /**
     * TODO clean up parameters
     */
    public function deleteHistoryLowerEqualThan(
        string $parent_type,
        int $page_id,
        string $lang,
        int $delete_lower_than_nr
    ): void {
        $this->content::deleteHistoryLowerEqualThan(
            $parent_type,
            $page_id,
            $lang,
            $delete_lower_than_nr
        );
    }
}
