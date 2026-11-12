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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\MetaData\Copyright\RendererInterface as CopyrightRenderer;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\Renderer;
use ILIAS\MetaData\Copyright\DatabaseRepository;
use ILIAS\Data\URI;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\FileUpload\MimeType;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\MetaData\Copyright\Database\Wrapper;
use ILIAS\MetaData\Settings\GUI\Copyright\ComponentFactory;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\Settings\GUI\Copyright\ImageUpload\HandlerGUI as ImageUploadHandlerGUI;
use ILIAS\MetaData\Settings\GUI\Copyright\ImageUpload\Stakeholder as ImageUploadStakeholder;

/**
 * @ilCtrl_Calls ilMDCopyrightConfigurationGUI: ILIAS\MetaData\Settings\GUI\Copyright\ImageUpload\HandlerGUI
 * @ilCtrl_Calls ilMDCopyrightConfigurationGUI: ilMDCopyrightUsageGUI
 */
class ilMDCopyrightConfigurationGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar_gui;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected IRSS $irss;

    protected ilObjMDSettingsGUI $parent_obj_gui;
    protected ilMDSettingsAccessService $access_service;
    protected CopyrightRenderer $renderer;
    protected RepositoryInterface $repository;
    protected ComponentFactory $component_factory;

    public function __construct(ilObjMDSettingsGUI $parent_obj_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar_gui = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->irss = $DIC->resourceStorage();

        $this->parent_obj_gui = $parent_obj_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_obj_gui->getRefId(),
            $DIC->access()
        );
        $internal = new InternalServices($DIC);
        $this->renderer = $internal->copyright()->renderer();
        $this->repository = $internal->copyright()->repository();
        $this->component_factory = new ComponentFactory(
            $internal->presentation()->utilities(),
            $DIC->ui()->factory(),
            $DIC->refinery(),
            new DataFactory()
        );

        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (!$this->access_service->hasCurrentUserReadAccess()) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilMDCopyrightUsageGUI::class):
                // this command is used if copyrightUsageGUI calls getParentReturn (see ...UsageGUI->setTabs)
                $this->ctrl->setReturn($this, 'showCopyrightSelection');
                $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
                $gui = new ilMDCopyrightUsageGUI($entry);
                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(ImageUploadHandlerGUI::class):
                $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
                $file_id = empty($entry?->copyrightData()?->imageFile()) ? '' : $entry?->copyrightData()?->imageFile();
                $handler = new ImageUploadHandlerGUI($file_id);
                $this->ctrl->forwardCommand($handler);

                // no break
            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'showCopyrightSelection';
                }

                $this->$cmd();
                break;
        }
    }

    public function showCopyrightSelection(): void
    {
        $has_write = $this->access_service->hasCurrentUserWriteAccess();

        $table_gui = new ilMDCopyrightTableGUI(
            $this->repository,
            $this->renderer,
            $this,
            'showCopyrightSelection',
            $has_write
        );
        $table_gui->setTitle($this->lng->txt("md_copyright_selection"));
        $table_gui->parseSelections();

        $edit_prompts = [];
        if ($has_write) {

            if (!isset($entry)) {
                $title = $this->lng->txt('md_copyright_add');
                $post_url = $this->ctrl->getLinkTarget($this, 'saveEntry');
            } else {
                $title = $this->lng->txt('md_copyright_edit');
                $this->ctrl->setParameter($this, 'entry_id', $entry->id());
                $post_url = $this->ctrl->getLinkTarget($this, 'updateEntry');
                $this->ctrl->clearParameters($this);
            }

            foreach ($this->repository->getAllEntries() as $entry) {
                $this->ctrl->setParameter($this, 'entry_id', $entry->id());
                $async_target = $this->ctrl->getLinkTarget($this, 'showEditEntry');
                $this->ctrl->clearParameters($this);
                $prompt = $this->component_factory->prompt(
                    $this->lng->txt('md_copyright_edit'),
                    $async_target
                );
                $table_gui->setEditDialogSignal($entry->id(), $prompt->getShowSignal());
                $edit_prompts[] = $prompt;
            }
            $add_prompt = $this->component_factory->prompt(
                $this->lng->txt('md_copyright_add'),
                $this->ctrl->getLinkTarget($this, 'showCreateEntry')
            );
            $this->toolbar_gui->addComponent($this->ui_factory->button()->standard(
                $this->lng->txt('md_copyright_add'),
                $add_prompt->getShowSignal()
            ));

            $table_gui->addMultiCommand("confirmDeleteEntries", $this->lng->txt("delete"));
            $table_gui->setSelectAllCheckbox("entry_id");
        }

        $this->tpl->setContent(
            $table_gui->getHTML() .
            (isset($add_prompt) ? $this->ui_renderer->render($add_prompt) : '') .
            $this->ui_renderer->render($edit_prompts)
        );
    }

    protected function showCreateEntry(): never
    {
        $form = $this->component_factory->copyrightEditForm(
            $this->ctrl->getLinkTarget($this, 'saveEntry')
        );
        $state = $this->component_factory->showPromptState($form);
        echo $this->ui_renderer->renderAsync($state);
        exit;
    }

    protected function showEditEntry(): never
    {
        $this->ctrl->saveParameter($this, 'entry_id');
        $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
        $form = $this->component_factory->copyrightEditForm(
            $this->ctrl->getLinkTarget($this, 'updateEntry'),
            $entry
        );
        $state = $this->component_factory->showPromptState($form);
        echo $this->ui_renderer->renderAsync($state);
        exit;
    }

    public function saveEntry(): never
    {
        $form = $this->component_factory
            ->copyrightEditForm($this->ctrl->getLinkTarget($this, 'saveEntry'))
            ->withRequest($this->http->request());

        if ($data = $form->getData()) {
            $this->repository->createEntry(
                $data['title'],
                $data['description'],
                $data['usage_status'] === 'outdated',
                $data['usage_status'] === 'featured',
                $data['copyright']['full_name'],
                $data['copyright']['link'],
                $data['copyright']['image'],
                $data['copyright']['alt_text']
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $state = $this->component_factory->redirectPromptState(
                $this->ctrl->getLinkTarget($this, 'showCopyrightSelection')
            );
            echo $this->ui_renderer->renderAsync($state);
            exit;
        }
        $state = $this->component_factory->showPromptState($form);
        echo $this->ui_renderer->renderAsync($state);
        exit;
    }

    public function confirmDeleteEntries(): void
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSelection();
            return;
        }

        $c_gui = new ilConfirmationGUI();
        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteEntries"));
        $c_gui->setHeaderText($this->lng->txt("md_delete_cp_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "showCopyrightSelection");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteEntries");

        // add items to delete
        foreach ($entry_ids as $entry_id) {
            $entry = $this->repository->getEntry($entry_id);
            $c_gui->addItem('entry_id[]', (string) $entry_id, $entry->title());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function deleteEntries(): bool
    {
        $entry_ids = $this->initEntryIdFromPost();
        if (!count($entry_ids)) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('select_one'));
            $this->showCopyrightSelection();
            return true;
        }

        foreach ($entry_ids as $entry_id) {
            $entry = $this->repository->getEntry($entry_id);
            $this->deleteFile($entry);
            $this->repository->deleteEntry($entry_id);
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('md_copyrights_deleted'));
        $this->showCopyrightSelection();
        return true;
    }

    public function updateEntry(): never
    {
        $entry = $this->repository->getEntry($this->initEntryIdFromQuery());
        $form = $this->component_factory
            ->copyrightEditForm($this->ctrl->getLinkTarget($this, 'updateEntry'), $entry)
            ->withRequest($this->http->request());

        if ($data = $form->getData()) {
            $this->deleteFileIfChanged($entry, $data);
            $this->repository->updateEntry(
                $entry->id(),
                $data['title'],
                $data['description'],
                $data['usage_status'] === 'outdated',
                $data['usage_status'] === 'featured',
                $data['copyright']['full_name'],
                $data['copyright']['link'],
                $data['copyright']['image'],
                $data['copyright']['alt_text']
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $state = $this->component_factory->redirectPromptState(
                $this->ctrl->getLinkTarget($this, 'showCopyrightSelection')
            );
            echo $this->ui_renderer->renderAsync($state);
            exit;
        }
        $state = $this->component_factory->showPromptState($form);
        echo $this->ui_renderer->renderAsync($state);
        exit;
    }

    public function saveCopyrightPosition(): bool
    {
        if (!$this->http->wrapper()->post()->has('order')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_select_one'), true);
            $this->ctrl->redirect($this, 'showCopyrightSelection');
            return false;
        }
        $positions = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf(
                $this->refinery->kindlyTo()->string()
            )
        );
        asort($positions);
        $ids = [];
        foreach ($positions as $entry_id => $position_ignored) {
            $ids[] = (int) $entry_id;
        }
        $this->repository->reorderEntries(...$ids);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'showCopyrightSelection');
        return false;
    }

    protected function deleteFile(EntryInterface $entry): void
    {
        if (($image_file = $entry->copyrightData()->imageFile()) === '') {
            return;
        }
        if ($id = $this->irss->manage()->find($image_file)) {
            $this->irss->manage()->remove($id, new ImageUploadStakeholder());
        }
    }

    protected function deleteFileIfChanged(
        EntryInterface $entry,
        array $data
    ): void {
        if (($image_file = $entry->copyrightData()->imageFile()) === '') {
            return;
        }
        if (
            $image_file === ($data['copyright']['image'] ?? null)
        ) {
            return;
        }
        $this->deleteFile($entry);
    }

    protected function initEntryIdFromQuery(): int
    {
        $entry_id = 0;
        if ($this->http->wrapper()->query()->has('entry_id')) {
            $entry_id = $this->http->wrapper()->query()->retrieve(
                'entry_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return $entry_id;
    }

    protected function initEntryIdFromPost(): array
    {
        $entries = [];
        if ($this->http->wrapper()->post()->has('entry_id')) {
            return $this->http->wrapper()->post()->retrieve(
                'entry_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }
}
