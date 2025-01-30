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

use ILIAS\MetaData\Editor\Http\Parameter;
use ILIAS\MetaData\Editor\Http\Command;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\Editor\Full\FullEditorInitiator;
use ILIAS\UI\Renderer;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Editor\Http\RequestParserInterface;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Editor\Observers\ObserverHandler;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Http\RequestInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Editor\Full\FullEditor;
use ILIAS\MetaData\Editor\Full\ContentType as FullContentType;
use ILIAS\MetaData\Editor\Digest\ContentType as DigestContentType;
use ILIAS\MetaData\Editor\Digest\DigestInitiator;
use ILIAS\MetaData\Editor\Digest\Digest;
use ILIAS\MetaData\XML\Writer\WriterInterface as XMLWriter;
use ILIAS\MetaData\Editor\Http\StandardAction;
use ILIAS\MetaData\Editor\Http\AsyncAction;
use ILIAS\UI\Component\Prompt\State\State;
use ILIAS\MetaData\Editor\Http\LinkFactoryInterface;
use JetBrains\PhpStorm\NoReturn;

/**
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilMDEditorGUI
{
    public const SET_FOR_TREE = 'md_set_for_tree';
    public const PATH_FOR_TREE = 'md_path_for_tree';

    protected FullEditorInitiator $full_editor_initiator;
    protected DigestInitiator $digest_initiator;

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected Renderer $ui_renderer;
    protected PresenterInterface $presenter;
    protected RepositoryInterface $repository;
    protected RequestParserInterface $request_parser;
    protected LinkFactoryInterface $link_factory;
    protected ObserverHandler $observer_handler;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected GlobalScreen $global_screen;
    protected ilTabsGUI $tabs;
    protected UIFactory $ui_factory;
    protected XMLWriter $xml_writer;

    protected int $obj_id;
    protected int $sub_id;
    public string $type;

    public function __construct(int $obj_id, int $sub_id, string $type)
    {
        global $DIC;

        $services = new InternalServices($DIC);
        $this->full_editor_initiator = new FullEditorInitiator($services);
        $this->digest_initiator = new DigestInitiator($services);

        $this->ctrl = $services->dic()->ctrl();
        $this->tpl = $services->dic()->ui()->mainTemplate();
        $this->ui_renderer = $services->dic()->ui()->renderer();
        $this->presenter = $services->editor()->presenter();
        $this->request_parser = $services->editor()->requestParser();
        $this->link_factory = $services->editor()->linkFactory();
        $this->repository = $services->repository()->repository();
        $this->observer_handler = $services->editor()->observerHandler();
        $this->access = $services->dic()->access();
        $this->toolbar = $services->dic()->toolbar();
        $this->global_screen = $services->dic()->globalScreen();
        $this->tabs = $services->dic()->tabs();
        $this->ui_factory = $services->dic()->ui()->factory();
        $this->xml_writer = $services->xml()->standardWriter();

        $this->obj_id = $obj_id;
        $this->sub_id = $sub_id === 0 ? $obj_id : $sub_id;
        $this->type = $type;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd();
        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = Command::SHOW_DIGEST->value;
                }
                $this->$cmd();
                break;
        }
    }

    public function debug(): bool
    {
        $button = $this->renderButtonToFullEditor();

        $xml = $this->xml_writer->write($this->repository->getMD($this->obj_id, $this->sub_id, $this->type));
        $dom = new DOMDocument('1.0');
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml->asXML());
        $this->tpl->setContent($button . '<pre>' . htmlentities($dom->saveXML()) . '</pre>');
        return true;
    }

    public function listSection(): void
    {
        $this->listQuickEdit();
    }

    public function listQuickEdit(): void
    {
        $digest = $this->digest_initiator->init();
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );

        $request = $this->request_parser->fetchRequest(false);
        $this->renderDigest($set, $digest, $request);
    }

    public function updateQuickEdit(): void
    {
        $this->checkAccess();

        $digest = $this->digest_initiator->init();
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );

        $request = $this->request_parser->fetchRequest(true);
        if (!$digest->updateMD($set, $request)) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->presenter->utilities()->txt('msg_form_save_error'),
                true
            );
            $this->renderDigest($set, $digest, $request);
            return;
        }

        $this->callListeners('General');
        $this->callListeners('Rights');
        $this->callListeners('Educational');
        $this->callListeners('Lifecycle');

        // Redirect here to read new title and description
        $this->tpl->setOnScreenMessage(
            'success',
            $this->presenter->utilities()->txt("saved_successfully"),
            true
        );
        $this->ctrl->redirectToURL(
            (string) $this->link_factory->standard(Command::SHOW_DIGEST)->get()
        );
    }

    protected function renderDigest(
        SetInterface $set,
        Digest $digest,
        RequestInterface $request
    ): void {
        $content = $digest->getContent($set, $request);
        $template_content = [];
        foreach ($content as $type => $entity) {
            switch ($type) {
                case DigestContentType::FORM:
                case DigestContentType::MODAL:
                    $template_content[] = $entity;
                    break;

                case DigestContentType::JS_SOURCE:
                    $this->tpl->addJavaScript($entity);
                    break;
            }
        }
        $this->tpl->setContent(
            $this->renderButtonToFullEditor() .
            $this->ui_renderer->render($template_content)
        );
    }

    protected function fullEditorAction(): void
    {
        $this->checkAccess();

        // get the parameters from the http request
        $base_path = $this->request_parser->fetchBasePath();
        $action_path = $this->request_parser->fetchActionPath();
        $action = $this->request_parser->fetchAction();

        // get the MD
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );
        $editor = $this->full_editor_initiator->init();
        $set = $editor->manipulateMD()->prepare($set, $base_path);

        // do the action
        $success = false;
        switch ($action) {
            case StandardAction::CREATE:
            case StandardAction::UPDATE:
                $success = $this->handleFullEditorEdit(
                    $set,
                    $base_path,
                    $action_path,
                    $editor
                );
                break;

            case StandardAction::DELETE:
                $success = true;
                $base_path = $editor->manipulateMD()->deleteAndTrimBasePath(
                    $set,
                    $base_path,
                    $action_path
                );
                break;

            default:
                throw new ilMDEditorException('Invalid standard action ' . $action->value);
        }

        if (!$success) {
            return;
        }

        // call listeners
        $this->observer_handler->callObserversByPath($action_path);

        // redirect back to the full editor
        $message_var = match ($action) {
            StandardAction::CREATE => 'meta_add_element_success',
            StandardAction::UPDATE => 'meta_edit_element_success',
            StandardAction::DELETE => 'meta_delete_element_success',
        };
        $this->tpl->setOnScreenMessage(
            'success',
            $this->presenter->utilities()->txt($message_var),
            true
        );

        $link = $this->link_factory->standard(Command::SHOW_FULL)
                                   ->withParameter(Parameter::BASE_PATH, $base_path->toString())
                                   ->get();
        $this->ctrl->redirectToURL((string) $link);
    }

    protected function handleFullEditorEdit(
        SetInterface $set,
        PathInterface $base_path,
        PathInterface $action_path,
        FullEditor $full_editor
    ): bool {
        $request = $this->request_parser->fetchRequest(true);
        $success = $full_editor->manipulateMD()->createOrUpdate(
            $set,
            $base_path,
            $action_path,
            $request
        );

        if (!$success) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->presenter->utilities()->txt('msg_form_save_error'),
                true
            );
            $this->renderFullEditor($set, $base_path, $full_editor, $request);
        }
        return $success;
    }

    #[NoReturn]
    protected function fullEditorActionAsync(): void
    {
        $this->checkAccess();

        // get the parameters from the http request
        $base_path = $this->request_parser->fetchBasePath();
        $action_path = $this->request_parser->fetchActionPath();
        $action = $this->request_parser->fetchAsyncAction();

        // get the MD
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );
        $editor = $this->full_editor_initiator->init();
        $set = $editor->manipulateMD()->prepare($set, $base_path);

        $response = match ($action) {
            AsyncAction::SHOW_CREATE, AsyncAction::SHOW_UPDATE => $editor->getAsyncContentForEdit(
                $set,
                $base_path,
                $action_path,
                $this->request_parser->fetchRequest(false)
            ),

            AsyncAction::SHOW_DELETE => $editor->getAsyncContentForDelete(
                $set,
                $base_path,
                $action_path
            )->getComponent(),

            AsyncAction::CREATE, AsyncAction::UPDATE => $this->handleFullEditorAsyncEdit(
                $set,
                $base_path,
                $action_path,
                $editor,
                $action === AsyncAction::CREATE
            ),

            default => throw new ilMDEditorException('Invalid async action ' . $action->value)
        };

        echo($this->ui_renderer->renderAsync($response));
        exit;
    }

    protected function handleFullEditorAsyncEdit(
        SetInterface $set,
        PathInterface $base_path,
        PathInterface $action_path,
        FullEditor $full_editor,
        bool $create
    ): State {
        // update or create
        $request = $this->request_parser->fetchRequest(true);
        $success = $full_editor->manipulateMD()->createOrUpdate(
            $set,
            $base_path,
            $action_path,
            $request
        );
        if (!$success) {
            $show = $full_editor->getAsyncContentForEdit($set, $base_path, $action_path, $request);
            echo($this->ui_renderer->renderAsync($show));
            exit;
        }

        // call listeners
        $this->observer_handler->callObserversByPath($action_path);

        // redirect back to the full editor
        $this->tpl->setOnScreenMessage(
            'success',
            $this->presenter->utilities()->txt(
                $create ?
                    'meta_add_element_success' :
                    'meta_edit_element_success'
            ),
            true
        );

        $link = $this->link_factory->standard(Command::SHOW_FULL)
                                   ->withParameter(Parameter::BASE_PATH, $base_path->toString())
                                   ->get();
        return $this->ui_factory->prompt()->state()->redirect($link);
    }

    protected function fullEditor(): void
    {
        $this->setTabsForFullEditor();

        // get the paths from the http request
        $base_path = $this->request_parser->fetchBasePath();

        // get and prepare the MD
        $set = $this->repository->getMD(
            $this->obj_id,
            $this->sub_id,
            $this->type
        );
        $editor = $this->full_editor_initiator->init();
        $set = $editor->manipulateMD()->prepare($set, $base_path);

        // add content for element
        $request = $this->request_parser->fetchRequest(false);
        $this->renderFullEditor($set, $base_path, $editor, $request);
    }

    protected function renderFullEditor(
        SetInterface $set,
        PathInterface $base_path,
        FullEditor $full_editor,
        RequestInterface $request
    ): void {
        // add slate with tree
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            self::SET_FOR_TREE,
            $set
        );
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            self::PATH_FOR_TREE,
            $base_path
        );

        // render toolbar, modals and main content
        $content = $full_editor->getContent($set, $base_path, $request);
        $template_content = [];
        foreach ($content as $type => $entity) {
            switch ($type) {
                case FullContentType::MAIN:
                    $template_content[] = $entity;
                    break;

                case FullContentType::MODAL:
                    if ($modal = $entity->getComponent()) {
                        $template_content[] = $modal;
                    }
                    break;

                case FullContentType::TOOLBAR:
                    $this->toolbar->addComponent($entity);
                    break;
            }
        }
        $this->tpl->setContent($this->ui_renderer->render($template_content));
    }

    protected function setTabsForFullEditor(): void
    {
        $this->tabs->clearSubTabs();
        foreach ($this->tabs->target as $tab) {
            if (($tab['id'] ?? null) !== $this->tabs->getActiveTab()) {
                $this->tabs->removeTab($tab['id']);
            }
        }
        $this->tabs->removeNonTabbedLinks();
        $this->tabs->setBackTarget(
            $this->presenter->utilities()->txt('back'),
            (string) $this->link_factory->standard(Command::SHOW_DIGEST)->get()
        );
    }

    protected function renderButtonToFullEditor(): string
    {
        $bulky = $this->ui_factory->button()->bulky(
            $this->ui_factory->symbol()->icon()->standard(
                'mds',
                $this->presenter->utilities()->txt('meta_button_to_full_editor_label'),
                'medium'
            ),
            $this->presenter->utilities()->txt('meta_button_to_full_editor_label'),
            (string) $this->link_factory->standard(Command::SHOW_FULL)->get()
        );
        if (DEVMODE) {
            $debug = $this->ui_factory->button()->bulky(
                $this->ui_factory->symbol()->icon()->standard(
                    'adm',
                    'Debug'
                ),
                'Debug',
                (string) $this->link_factory->standard(Command::DEBUG)->get()
            );
        }
        return  $this->ui_renderer->render($bulky) .
            (isset($debug) ? '</p>' . $this->ui_renderer->render($debug) : '');
    }

    protected function checkAccess(): void
    {
        // if there is no fixed parent (e.g. mob), then skip
        if ($this->obj_id === 0) {
            return;
        }
        $ref_ids = ilObject::_getAllReferences($this->obj_id);
        // if there are no references (e.g. in workspace), then skip
        if (empty($ref_ids)) {
            return;
        }
        foreach ($ref_ids as $ref_id) {
            if ($this->access->checkAccess(
                'write',
                '',
                $ref_id,
                '',
                $this->obj_id
            )) {
                return;
            }
        }
        throw new ilPermissionException($this->presenter->utilities()->txt('permission_denied'));
    }

    // Observer methods
    public function addObserver(object $a_class, string $a_method, string $a_element): void
    {
        $this->observer_handler->addObserver($a_class, $a_method, $a_element);
    }

    public function callListeners(string $a_element): void
    {
        $this->observer_handler->callObservers($a_element);
    }
}
