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

namespace ILIAS\MetaData\Editor\Full\Services\Actions;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Editor\Full\Services\PropertiesFetcher;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\FormFactory;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionaryInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\Restriction;
use ILIAS\MetaData\Editor\Http\RequestInterface;
use ILIAS\UI\Component\Prompt\State\State;
use ILIAS\MetaData\Editor\Http\StandardAction;
use ILIAS\MetaData\Editor\Http\AsyncAction;
use ILIAS\MetaData\Editor\Full\Services\ConstraintHelper;

class ModalFactory
{
    use ConstraintHelper;

    public const MAX_LENGTH = 128;

    protected LinkProvider $link_provider;
    protected UIFactory $factory;
    protected PresenterInterface $presenter;
    protected PropertiesFetcher $properties_fetcher;
    protected FormFactory $form_factory;
    protected ConstraintDictionaryInterface $constraint_dictionary;

    public function __construct(
        LinkProvider $link_provider,
        UIFactory $factory,
        PresenterInterface $presenter,
        PropertiesFetcher $properties_fetcher,
        FormFactory $form_factory,
        ConstraintDictionaryInterface $constraint_dictionary
    ) {
        $this->link_provider = $link_provider;
        $this->factory = $factory;
        $this->presenter = $presenter;
        $this->properties_fetcher = $properties_fetcher;
        $this->form_factory = $form_factory;
        $this->constraint_dictionary = $constraint_dictionary;
    }

    public function deletePlaceholder(
        PathInterface $base_path,
        ElementInterface $to_be_deleted
    ): ?FlexibleModal {
        if (!$this->isDeletable($this->constraint_dictionary, $to_be_deleted)) {
            return null;
        }

        $async_url = $this->link_provider->async(
            $base_path,
            $to_be_deleted,
            AsyncAction::SHOW_DELETE
        );
        $modal = $this->factory->modal()->interruptive(
            '',
            '',
            ''
        )->withAsyncRenderUrl((string) $async_url);
        return new FlexibleModal($modal);
    }

    public function deleteContent(
        PathInterface $base_path,
        ElementInterface $to_be_deleted
    ): FlexibleModal {
        $action = $this->link_provider->standard(
            $base_path,
            $to_be_deleted,
            StandardAction::DELETE
        );

        $items = [];
        $index = 0;
        $content = $this->properties_fetcher->getPropertiesByData($to_be_deleted);
        foreach ($content as $key => $value) {
            $items[] = $this->factory->modal()->interruptiveItem()->keyValue(
                'md_delete_' . $index,
                $this->presenter->utilities()->shortenString($key, self::MAX_LENGTH),
                $this->presenter->utilities()->shortenString($value, self::MAX_LENGTH),
            );
            $index++;
        }

        $modal = $this->factory->modal()->interruptive(
            $this->getModalTitle(
                StandardAction::DELETE,
                $to_be_deleted
            ),
            $this->presenter->utilities()->txt('meta_delete_confirm'),
            (string) $action
        )->withAffectedItems($items);

        return new FlexibleModal($modal);
    }

    public function updatePlaceholder(
        PathInterface $base_path,
        ElementInterface $to_be_updated
    ): FlexibleModal {
        $async_url = $this->link_provider->async(
            $base_path,
            $to_be_updated,
            AsyncAction::SHOW_UPDATE
        );
        return new FlexibleModal($this->factory->prompt()->standard($async_url));
    }

    public function updateContent(
        PathInterface $base_path,
        ElementInterface $to_be_updated,
        RequestInterface $request
    ): State {
        $form = $this->form_factory->getUpdateForm(
            $base_path,
            $to_be_updated,
            true,
            false
        );
        if ($request->shouldBeAppliedToForms()) {
            $form = $request->applyRequestToForm($form);
        }
        $show_state = $this->factory->prompt()->state()->show($form)->withTitle(
            $this->getModalTitle(StandardAction::UPDATE, $to_be_updated)
        );

        return $show_state;
    }

    public function createPlaceholder(
        PathInterface $base_path,
        ElementInterface $to_be_created
    ): FlexibleModal {
        // TODO find a better way to do this, maybe pull out of here? or use prompts to redirect
        $form = $this->form_factory->getCreateForm(
            $base_path,
            $to_be_created,
            false
        );
        // if the form is empty, directly return the form action
        if (empty($form->getInputs())) {
            $link = $this->link_provider->standard(
                $base_path,
                $to_be_created,
                StandardAction::CREATE
            );
            return new FlexibleModal((string) $link);
        }

        $async_url = $this->link_provider->async(
            $base_path,
            $to_be_created,
            AsyncAction::SHOW_CREATE
        );
        return new FlexibleModal($this->factory->prompt()->standard($async_url));
    }

    public function createContent(
        PathInterface $base_path,
        ElementInterface $to_be_created,
        RequestInterface $request
    ): State {
        $form = $this->form_factory->getCreateForm(
            $base_path,
            $to_be_created,
            false
        );

        if ($request->shouldBeAppliedToForms()) {
            $form = $request->applyRequestToForm($form);
        }
        $show_state = $this->factory->prompt()->state()->show($form)->withTitle(
            $this->getModalTitle(StandardAction::CREATE, $to_be_created)
        );

        return $show_state;
    }

    protected function getModalTitle(
        StandardAction $action,
        ElementInterface $element
    ): string {
        switch ($action) {
            case StandardAction::UPDATE:
                $title_key = 'meta_edit_element';
                break;

            case StandardAction::CREATE:
                $title_key = 'meta_add_element';
                break;

            case StandardAction::DELETE:
                $title_key = 'meta_delete_element';
                break;

            default:
                throw new \ilMDEditorException(
                    'Invalid action: ' . $action->name
                );
        }
        return $this->presenter->utilities()->txtFill(
            $title_key,
            $this->presenter->elements()->nameWithParents(
                $element,
                null,
                false,
                true
            )
        );
    }
}
