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

use ILIAS\HTTP\Services as HTTP;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Button\Standard as Button;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Modal\RoundTrip as RoundtripModal;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\URI;
use ILIAS\FileUpload\MimeType;
use ILIAS\Filesystem\Filesystem;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledVocabsRepository;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Settings\Vocabularies\Import\Importer;

/**
 * @ilCtrl_Calls ilMDVocabulariesGUI: ilMDVocabularyUploadHandlerGUI
 */
class ilMDVocabulariesGUI
{
    protected ilCtrl $ctrl;
    protected HTTP $http;
    protected Filesystem $temp_files;
    protected ControlledVocabsRepository $controlled_vocab_repo;
    protected PathFactory $path_factory;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilObjMDSettingsGUI $parent_obj_gui;
    protected ilMDSettingsAccessService $access_service;

    public function __construct(ilObjMDSettingsGUI $parent_obj_gui)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->temp_files = $DIC->filesystem()->temp();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->parent_obj_gui = $parent_obj_gui;
        $this->access_service = new ilMDSettingsAccessService(
            $this->parent_obj_gui->getRefId(),
            $DIC->access()
        );

        $this->lng->loadLanguageModule("meta");
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            !$this->access_service->hasCurrentUserVisibleAccess() ||
            !$this->access_service->hasCurrentUserReadAccess()
        ) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilMDVocabularyUploadHandlerGUI::class):
                $handler = new ilMDVocabularyUploadHandlerGUI();
                $this->ctrl->forwardCommand($handler);

                // no break
            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = 'showVocabularies';
                }

                $this->$cmd();
                break;
        }
    }

    public function showVocabularies(): void
    {
        $import_modal = $this->getImportModal();
        $this->toolbar->addComponent($this->getImportButton($import_modal->getShowSignal()));

        $table = $this->getTable();

        $this->tpl->setContent(
            $this->ui_renderer->render([
                $import_modal,
                $table
            ])
        );
    }

    public function tableAction(): void
    {
    }

    protected function importVocabulary(): void
    {
        $message_type = 'failure';
        $message_text = $this->lng->txt('vocab_import_upload_failed');

        $modal = $this->getImportModal()->withRequest($this->http->request());

        $upload_folder = null;
        if ($modal->getData()) {
            $upload_folder = (string) ($modal->getData()['file'][0] ?? null);
            if (!$this->temp_files->hasDir($upload_folder)) {
                $upload_folder = null;
            }
        }

        $file_content = null;
        if (!is_null($upload_folder)) {
            $files = $files = $this->temp_files->listContents($upload_folder);
            if (count($files) === 1 && ($files[0] ?? null)?->isFile()) {
                $file_content = $this->temp_files->read($files[0]->getPath());
            }
            $this->temp_files->deleteDir($upload_folder);
        }

        if (!is_null($file_content)) {
            $importer = new Importer(
                $this->path_factory,
                $this->controlled_vocab_repo
            );
            $result = $importer->import($file_content);

            if ($result->wasSuccessful()) {
                $message_type = 'success';
                $message_text = $this->lng->txt('vocab_import_successful');
            } else {
                $message_type = 'failure';
                printf(
                    $message_text = $this->lng->txt('vocab_import_invalid'),
                    implode("\n\r", $result->getErrors())
                );
            }
        }

        $this->tpl->setOnScreenMessage($message_type, $message_text, true);
        $this->ctrl->redirect($this, 'showVocabularies');
    }

    protected function deleteVocabulary(): void
    {
    }

    protected function toggleActiveForVocabulary(): void
    {
    }

    protected function toggleCustomInputForVocabulary(): void
    {
    }

    protected function getTable(): DataTable
    {
        $column_factory = $this->ui_factory->table()->column();
        $columns = [
            'element' => $column_factory->text($this->lng->txt('vocab_element_column'))->withIsSortable(false),
            'type' => $column_factory->status($this->lng->txt('vocab_type_column'))->withIsSortable(false),
            'source' => $column_factory->text($this->lng->txt('vocab_source_column'))->withIsSortable(false),
            'preview' => $column_factory->text($this->lng->txt('vocab_preview_column'))->withIsSortable(false),
            'active' => $column_factory->boolean(
                $this->lng->txt('vocab_active_column'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withIsSortable(false),
            'custom_input' => $column_factory->boolean(
                $this->lng->txt('vocab_custom_input_column'),
                $this->lng->txt('yes'),
                $this->lng->txt('no')
            )->withIsSortable(false)
        ];

        $url_builder = new URLBuilder(new URI(
            rtrim(ILIAS_HTTP_PATH, '/') . $this->ctrl->getLinkTarget($this, 'tableAction')
        ));
        list($url_builder, $action_parameter_token, $row_id_token) =
            $url_builder->acquireParameters(
                ['metadata', 'vocab'],
                'table_action',
                'vocab_ids'
            );
        $actions_factory = $this->ui_factory->table()->action();
        $actions = [
            'delete' => $actions_factory->single(
                $this->lng->txt('vocab_delete_action'),
                $url_builder->withParameter($action_parameter_token, 'delete'),
                $row_id_token
            )->withAsync(true),
            'toggle_active' => $actions_factory->single(
                $this->lng->txt('vocab_toggle_active_action'),
                $url_builder->withParameter($action_parameter_token, 'toggle_active'),
                $row_id_token
            ),
            'toggle_custom_input' => $actions_factory->single(
                $this->lng->txt('vocab_toggle_custom_input_action'),
                $url_builder->withParameter($action_parameter_token, 'toggle_custom_input'),
                $row_id_token
            ),
            'show_all' => $actions_factory->single(
                $this->lng->txt('vocab_show_all_action'),
                $url_builder->withParameter($action_parameter_token, 'show_all'),
                $row_id_token
            )->withAsync(true)
        ];

        return $this->ui_factory->table()->data(
            $this->lng->txt('vocab_table_title'),
            $columns,
            new ilMDVocabulariesDataRetrieval()
        )->withActions($actions)->withRequest($this->http->request());
    }

    protected function getImportModal(): RoundtripModal
    {
        $file_input = $this->ui_factory->input()->field()->file(
            new ilMDVocabularyUploadHandlerGUI(),
            $this->lng->txt('import_file_vocab')
        )->withAcceptedMimeTypes([MimeType::TEXT__XML])->withMaxFiles(1);

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('import_vocab_modal'),
            null,
            ['file' => $file_input],
            $this->ctrl->getLinkTarget($this, 'importVocabulary')
        );
    }

    protected function getImportButton(Signal $signal): Button
    {
        return $this->ui_factory->button()->standard(
            $this->lng->txt('import_vocab'),
            $signal
        );
    }
}
