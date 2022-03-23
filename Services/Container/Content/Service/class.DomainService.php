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

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalRepoService;
use ILIAS\Container\InternalDataService;
use ILIAS\Container\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    protected ItemSessionRepository $item_repo;
    protected ModeSessionRepository $mode_repo;

    /**
     * @var array<int, ItemSetManager>
     */
    protected static array $flat_item_set_managers = [];

    /**
     * @var array<int, ItemSetManager>
     */
    protected static array $tree_item_set_managers = [];

    public function __construct(
        InternalRepoService $repo_service,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->item_repo = $this->repo_service->content()->item();
        $this->mode_repo = $this->repo_service->content()->mode();
    }

    /**
     * Controls item state (e.g. expanded)
     */
    public function items(\ilContainer $container) : ItemManager
    {
        return new ItemManager(
            $container,
            $this->item_repo
        );
    }

    /**
     * Manages item retrieval, filtering, grouping and sorting
     */
    public function itemPresentation(\ilContainer $container) : ItemPresentationManager
    {
        return new ItemPresentationManager(
            $this->domain_service,
            $container
        );
    }

    /**
     * Manages set of conatiner items (flat version)
     */
    public function itemSetFlat(int $ref_id) : ItemSetManager
    {
        if (!isset(self::$flat_item_set_managers[$ref_id])) {
            self::$flat_item_set_managers[$ref_id] = new ItemSetManager(
                $this->domain_service,
                ItemSetManager::FLAT,
                $ref_id
            );
        }
        return self::$flat_item_set_managers[$ref_id];
    }

    /**
     * Manages set of conatiner items (flat version)
     */
    public function itemSetTree(int $ref_id) : ItemSetManager
    {
        if (!isset(self::$tree_item_set_managers[$ref_id])) {
            self::$tree_item_set_managers[$ref_id] = new ItemSetManager(
                $this->domain_service,
                ItemSetManager::TREE,
                $ref_id
            );
        }
        return self::$tree_item_set_managers[$ref_id];
    }

    public function view(\ilContainer $container) : ViewManager
    {
        $view_mode = $container->getViewMode();
        if ($container->filteredSubtree()) {
            $view_mode = \ilContainer::VIEW_SIMPLE;
        }
        switch ($view_mode) {
            case \ilContainer::VIEW_SIMPLE:
                $container_view = new SimpleContentViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;

            case \ilContainer::VIEW_OBJECTIVE:
                $container_view = new ObjectiveViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;

            // all items in one block
            case \ilContainer::VIEW_SESSIONS:
            case \ilCourseConstants::IL_CRS_VIEW_TIMING: // not nice this workaround
                $container_view = new SessionsViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;

            // all items in one block
            case \ilContainer::VIEW_BY_TYPE:
            default:
                $container_view = new ByTypeViewManager(
                    $this->data_service->content(),
                    $this->domain_service,
                    $container
                );
                break;
        }

        return $container_view;
    }

    /**
     * Controls admin/content view state
     */
    public function mode() : ModeManager
    {
        return new ModeManager(
            $this->mode_repo
        );
    }

    /*
    public function access(int $ref_id, int $user_id) : Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }*/
}
