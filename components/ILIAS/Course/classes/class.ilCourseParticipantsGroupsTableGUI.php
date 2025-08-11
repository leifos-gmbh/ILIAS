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

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ILIAS\UI\Component\Table\Data as ilDataTable;
use ILIAS\UI\Component\Input\Container\Filter\Filter as ilFilter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken as ilURLBuilderToken;
use JetBrains\PhpStorm\NoReturn;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;

class ilCourseParticipantsGroupsTableGUI
{
    public const TABLE_COL_NAME = 'name';
    public const TABLE_COL_LOGIN = 'login';
    public const TABLE_COL_GROUP_NUMBER = 'groups_number';
    public const TABLE_COL_GROUPS = 'groups';
    public const TABLE_FILTER_NAME = 'filter_name';
    public const TABLE_FILTER_GROUPS = 'filter_groups';
    protected const ALL_OBJECTS = 'ALL_OBJECTS';
    protected const TABLE_ID = 'crsprtcpntsgrpstbl';
    protected const FILTER_ID = 'crsprtcpntsgrpstbl_filter';
    protected const ROW_ID = 'row_ids';
    protected const TABLE_ACTION_ID = 'table_action';
    protected const TABLE_ACTION_ADD_TO_GROUP = 'add_to_group';
    protected const LNG_TABLE_COL_NAME = 'name';
    protected const LNG_TABLE_COL_LOGIN = 'login';
    protected const LNG_TABLE_COL_GROUP_NUMBER = 'crs_groups_nr';
    protected const LNG_TABLE_COL_GROUPS = 'groups';
    protected const LNG_TABLE_ACTION_CONFIRM_UNSUBSCRIBE = 'grp_unsubscribe';
    protected const LNG_TABLE_TITLE = 'crs_grp_assignments';
    protected const LNG_ADD_TO_GROUP = 'crs_add_to_group';

    protected URLBuilder $url_builder;
    protected ilURLBuilderToken $action_parameter_token;
    protected ilURLBuilderToken $row_id_token;
    protected ilDataTable $table;
    protected ilFilter $filter;

    public function __construct(
        protected ilCourseParticipantsGroupsTableDataRetrieval $data_retrieval,
        protected ilUIServices $ui_services,
        protected ilUIService $ui_service,
        protected ilHTTPServices $http_services,
        protected ilRefineryFactory $refinery,
        protected ilLanguage $lng,
        protected ilCtrl $ctrl,
        protected ilDataFactory $data_factory,
        protected ilGlobalTemplateInterface $tpl,
        protected ilAccess $access,
        protected ilObjectDataCache $object_data_cache
    ) {
        $this->lng->loadLanguageModule('grp');
    }

    protected function buildAddToGroupString(int $group_ref_id): string
    {
        return self::TABLE_ACTION_ADD_TO_GROUP . '_' . $group_ref_id;
    }

    protected function getColumns(): array
    {
        return [
            self::TABLE_COL_NAME => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_NAME)
            ),
            self::TABLE_COL_LOGIN => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_LOGIN)
            ),
            self::TABLE_COL_GROUP_NUMBER => $this->ui_services->factory()->table()->column()->number(
                $this->lng->txt(self::LNG_TABLE_COL_GROUP_NUMBER)
            ),
            self::TABLE_COL_GROUPS => $this->ui_services->factory()->table()->column()->text(
                $this->lng->txt(self::LNG_TABLE_COL_GROUPS)
            )->withIsSortable(false)
        ];
    }

    protected function getActions(): array
    {
        $this->url_builder = new URLBuilder($this->data_factory->uri($this->http_services->request()->getUri()->__toString()));
        list($this->url_builder, $this->action_parameter_token, $this->row_id_token) =
            $this->url_builder->acquireParameters(
                ['datatable', self::TABLE_ID],
                self::TABLE_ACTION_ID,
                self::ROW_ID
            );
        $actions = [];
        foreach ($this->data_retrieval->getSelectableGroups() as $ref_id => $group_name) {
            $action_id = $this->buildAddToGroupString((int) $ref_id);
            $actions[$action_id] = $this->ui_services->factory()->table()->action()->multi(
                $this->lng->txt(self::LNG_ADD_TO_GROUP) . ': ' . $group_name,
                $this->url_builder->withParameter($this->action_parameter_token, $action_id),
                $this->row_id_token
            );
            $action_id = $this->data_retrieval->buildConfirmUnsubscribeActionId((int) $ref_id);
            $actions[$action_id] = $this->ui_services->factory()->table()->action()->single(
                $group_name . ' ' . $this->lng->txt(self::LNG_TABLE_ACTION_CONFIRM_UNSUBSCRIBE),
                $this->url_builder->withParameter($this->action_parameter_token, $action_id),
                $this->row_id_token
            )->withAsync(true);
        }
        return $actions;
    }

    private function initFilter(): void
    {
        if (isset($this->filter)) {
            return;
        }
        $filter_fields = $this->getFilterFields();
        $this->filter = $this->ui_service->filter()->standard(
            self::FILTER_ID,
            $this->ctrl->getLinkTargetByClass(ilCourseParticipantsGroupsGUI::class, 'show'),
            $filter_fields,
            array_fill(0, count($filter_fields), true),
            true,
            true
        );
    }

    /**
     * @return \ILIAS\UI\Component\Input\Container\Filter\FilterInput[]
     */
    protected function getFilterFields(): array
    {
        return [
            self::TABLE_FILTER_NAME => $this->ui_services->factory()->input()->field()->text(
                $this->lng->txt(self::LNG_TABLE_COL_NAME)
            ),
            self::TABLE_FILTER_GROUPS => $this->ui_services->factory()->input()->field()->select(
                $this->lng->txt(self::TABLE_COL_GROUPS),
                $this->data_retrieval->getSelectableGroups()
            )
        ];
    }

    protected function initTable(): void
    {
        if (isset($this->table)) {
            return;
        }
        $this->table = $this->ui_services->factory()->table()->data(
            $this->lng->txt(self::LNG_TABLE_TITLE),
            $this->getColumns(),
            $this->data_retrieval
        )
            ->withId(self::TABLE_ID)
            ->withActions($this->getActions())
            ->withRequest($this->http_services->request());
    }

    protected function readIdsFromQuery(): array
    {
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        return is_null($tokens) ? [] : (is_array($tokens) ? $tokens : [$tokens]);
    }

    protected function addToGroup(array $user_ids, int $group_ref_id): void
    {
        if (!$this->access->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $group_ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilCourseParticipantsGroupsGUI::class, 'show');
            return;
        }
        $members_obj = ilGroupParticipants::_getInstanceByObjId($this->object_data_cache->lookupObjId($group_ref_id));
        $rejected_count = 0;
        foreach ($user_ids as $new_member) {
            if (!$members_obj->add((int) $new_member, ilParticipants::IL_GRP_MEMBER)) {
                $rejected_count++;
                continue;
            }
            $members_obj->sendNotification(
                ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER,
                (int) $new_member
            );
        }
        if ($rejected_count === 0) {
            $message = $this->lng->txt('grp_msg_member_assigned');
        } else {
            $accepted_count = count($user_ids) - $rejected_count;
            $message = sprintf(
                $this->lng->txt('grp_not_all_users_assigned_msg'),
                $accepted_count,
                $rejected_count
            );
        }
        $this->tpl->setOnScreenMessage('success', $message, true);
        $this->ctrl->redirectByClass(ilCourseParticipantsGroupsGUI::class, 'show');
    }

    protected function unsubscribe(array $user_ids, int $group_id): void
    {
        foreach ($user_ids as $user_id) {
            if (!$this->access->checkRbacOrPositionPermissionAccess('manage_members', 'manage_members', $group_id)) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
                $this->ctrl->redirectByClass(ilCourseParticipantsGroupsGUI::class, 'show');
                return;
            }
            $members_obj = ilGroupParticipants::_getInstanceByObjId($this->object_data_cache->lookupObjId($group_id));
            $members_obj->delete((int) $user_id);
            // Send notification
            $members_obj->sendNotification(
                ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER,
                (int) $user_id
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("grp_msg_membership_annulled"), true);
        $this->ctrl->redirectByClass(ilCourseParticipantsGroupsGUI::class, "show");
    }

    #[NoReturn] protected function showConfirmUnsubscribeModal(array $user_ids, int $group_ref_id): void
    {
        $items = [];
        foreach ($user_ids as $user_id) {
            $items[] = $this->ui_services->factory()->modal()->interruptiveItem()->standard(
                $user_id . '',
                ilUserUtil::getNamePresentation($user_id, false, false, "", true),
                $this->ui_services->factory()->image()->standard(ilUtil::getImagePath('standard/icon_usr.svg'), '')
            );
        }
        echo($this->ui_services->renderer()->renderAsync([
            $this->ui_services->factory()->modal()->interruptive(
                $this->lng->txt('confirm'),
                $this->lng->txt('grp_dismiss_member'),
                (string) $this->url_builder
                    ->withParameter(
                        $this->action_parameter_token,
                        $this->data_retrieval->buildUnsubscribeActionId($group_ref_id)
                    )->withParameter(
                        $this->row_id_token,
                        $user_ids
                    )->buildURI()
            )->withAffectedItems($items)
        ]));
        exit();
    }

    public function getHTML(): string
    {
        $this->initTable();
        $this->initFilter();
        return $this->ui_services->renderer()->render([
            $this->filter,
            $this->table->withFilter($this->filter->getInputs())
        ]);
    }

    public function handleCommands(): void
    {
        $this->initTable();
        if (!$this->http_services->wrapper()->query()->has($this->action_parameter_token->getName())) {
            return;
        }
        $action = $this->http_services->wrapper()->query()->retrieve(
            $this->action_parameter_token->getName(),
            $this->refinery->to()->string()
        );
        $tokens = $this->http_services->wrapper()->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->custom()->transformation(fn($v) => $v)
        );
        $all_entries = ($tokens[0] ?? "") === self::ALL_OBJECTS;
        $user_ids = [];
        if ($all_entries) {
            $user_ids = $this->data_retrieval->getAllUserIds();
        }
        if (!$all_entries) {
            $user_ids = $this->readIdsFromQuery();
        }
        foreach ($this->data_retrieval->getSelectableGroups() as $ref_id => $group_name) {
            if ($action === $this->buildAddToGroupString((int) $ref_id)) {
                $this->addToGroup($user_ids, $ref_id);
                break;
            }
            if ($action === $this->data_retrieval->buildUnsubscribeActionId($ref_id)) {
                $this->unsubscribe($user_ids, $ref_id);
                break;
            }
            if ($action === $this->data_retrieval->buildConfirmUnsubscribeActionId($ref_id)) {
                $this->showConfirmUnsubscribeModal($user_ids, $ref_id);
                break;
            }
        }
    }

    /*
    protected int $ref_id;
    protected int $obj_id;
    protected array $filter = [];
    protected array $groups = [];
    protected array $groups_rights = [];
    protected array $participants = [];

    protected ilAccessHandler $access;
    protected ilTree $tree;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $ref_id)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($ref_id);

        $this->setId('tblcrsprtgrp_' . $ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->lng->loadLanguageModule('grp');
        $this->setLimit(9999);

        $this->setTitle($this->lng->txt('crs_grp_assignments'));

        $this->addColumn('', '', '0');
        $this->addColumn($this->lng->txt("name"), "name", '35%');
        $this->addColumn($this->lng->txt("login"), "login", '35%');
        $this->addColumn($this->lng->txt("crs_groups_nr"), "groups_number");
        $this->addColumn($this->lng->txt("groups"));

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.crs_members_grp_row.html", "components/ILIAS/Course");
        $this->setSelectAllCheckbox("usrs");

        $this->initGroups();

        if ($this->groups) {
            $selectable_groups = [];
            foreach ($this->groups as $ref_id => $something) {
                if ($this->groups_rights[$ref_id]['manage_members']) {
                    $selectable_groups[$ref_id] = $this->groups[$ref_id];
                }
            }
            if ($selectable_groups !== []) {
                $this->addMultiItemSelectionButton(
                    "grp_id",
                    $selectable_groups,
                    "add",
                    $this->lng->txt("crs_add_to_group")
                );
            }
            $this->initFilter();
        }
        $this->getItems();
    }

     # find groups in course, exclude groups in groups
    public function initGroups(): void
    {
        $parent_node = $this->tree->getNodeData($this->ref_id);
        $groups = $this->tree->getSubTree($parent_node, true, ['grp']);
        if (is_array($groups) && count($groups)) {
            $this->participants = $this->groups = $this->groups_rights = array();
            foreach ($groups as $idx => $group_data) {
                // check for group in group
                if ($group_data["parent"] != $this->ref_id && $this->tree->checkForParentType(
                    $group_data["ref_id"],
                    "grp",
                    true
                )) {
                    unset($groups[$idx]);
                } else {
                    $this->groups[$group_data["ref_id"]] = $group_data["title"];
                    $this->groups_rights[$group_data["ref_id"]]["manage_members"] = $this->access->checkRbacOrPositionPermissionAccess(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id']
                    );

                    $this->groups_rights[$group_data["ref_id"]]["edit_permission"] = $this->access->checkAccess(
                        "edit_permission",
                        "",
                        $group_data["ref_id"]
                    );

                    $gobj = ilGroupParticipants::_getInstanceByObjId($group_data["obj_id"]);

                    $members = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id'],
                        $gobj->getMembers()
                    );
                    $admins = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'manage_members',
                        'manage_members',
                        $group_data['ref_id'],
                        $gobj->getAdmins()
                    );

                    $this->participants[$group_data["ref_id"]]["members"] = $members;
                    $this->participants[$group_data["ref_id"]]["admins"] = $admins;
                }
            }
        }
    }

    public function initFilter(): void
    {
        $item = $this->addFilterItemByMetaType("name", ilTable2GUI::FILTER_TEXT);
        $this->filter["name"] = $item->getValue();

        if ($this->groups) {
            $item = $this->addFilterItemByMetaType("group", ilTable2GUI::FILTER_SELECT);
            $item->setOptions(array("" => $this->lng->txt("all")) + $this->groups);
            $this->filter["group"] = $item->getValue();
        }
    }

    public function getItems(): void
    {
        if ($this->groups) {
            $part = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
            $members = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'manage_members',
                'manage_members',
                $this->ref_id,
                $part->getMembers()
            );

            if ($members !== []) {
                $usr_data = array();
                foreach ($members as $usr_id) {
                    $name = ilObjUser::_lookupName($usr_id);
                    $user_groups = array("members" => array(), "admins" => array());
                    $user_groups_number = 0;
                    foreach (array_keys($this->participants) as $group_id) {
                        if (in_array($usr_id, $this->participants[$group_id]["members"])) {
                            $user_groups["members"][$group_id] = $this->groups[$group_id];
                            $user_groups_number++;
                        } elseif (in_array($usr_id, $this->participants[$group_id]["admins"])) {
                            $user_groups["admins"][$group_id] = $this->groups[$group_id];
                            $user_groups_number++;
                        }
                    }

                    if ((!$this->filter["name"] || stristr(implode("", $name), $this->filter["name"])) &&
                        (!$this->filter["group"] || array_key_exists($this->filter["group"], $user_groups["members"]) ||
                            array_key_exists($this->filter["group"], $user_groups["admins"]))) {
                        $usr_data[] = array("usr_id" => $usr_id,
                                            "name" => $name["lastname"] . ", " . $name["firstname"],
                                            "groups" => $user_groups,
                                            "groups_number" => $user_groups_number,
                                            "login" => $name["login"]
                        );
                    }
                }

                $usr_data = array_slice($usr_data, $this->getOffset(), $this->getLimit());
                $this->setMaxCount(count($members));
                $this->setData($usr_data);
            }
        }
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);

        $this->tpl->setVariable("TXT_USER", $a_set["name"]);
        $this->tpl->setVariable("TXT_LOGIN", $a_set["login"]);
        $this->tpl->setVariable("VAL_GROUP_NUMBER", $a_set["groups_number"]);

        if (count($a_set["groups"]) !== 0) {
            foreach ($a_set["groups"] as $type => $groups) {
                foreach ($groups as $grp_id => $title) {
                    if (
                        ($type == "admins" && $this->groups_rights[$grp_id]["edit_permission"]) ||
                        ($type == "members" && $this->groups_rights[$grp_id]["manage_members"])
                    ) {
                        $this->tpl->setCurrentBlock("groups_remove");

                        $this->tpl->setVariable("TXT_GROUP_REMOVE", $this->lng->txt("grp_unsubscribe"));

                        $this->ctrl->setParameter($this->parent_obj, "usr_id", $a_set["usr_id"]);
                        $this->ctrl->setParameter($this->parent_obj, "grp_id", $grp_id);
                        $this->tpl->setVariable(
                            "URL_REMOVE",
                            $this->ctrl->getLinkTarget($this->parent_obj, "confirmremove")
                        );
                        $this->ctrl->setParameter($this->parent_obj, "grp_id", "");
                        $this->ctrl->setParameter($this->parent_obj, "usr_id", "");

                        $this->tpl->parseCurrentBlock();
                    }

                    $this->tpl->setCurrentBlock("groups");
                    $this->tpl->setVariable("TXT_GROUP_TITLE", $title);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }
    */
}
