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

namespace ILIAS\MetaData\Settings\GUI\Copyright;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Component\Prompt\State\State as PromptState;
use ILIAS\MetaData\Presentation\UtilitiesInterface as Presentation;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\Data\URI;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\FileUpload\MimeType;
use ILIAS\MetaData\Settings\GUI\Copyright\ImageUpload\HandlerGUI;
use ILIAS\UI\Component\Prompt\Prompt;

class ComponentFactory
{
    public function __construct(
        protected Presentation $presentation,
        protected UIFactory $ui_factory,
        protected Refinery $refinery,
        protected DataFactory $data_factory
    ) {
    }

    public function showPromptState(
        Form $form
    ): PromptState {
        return $this->ui_factory->prompt()->state()->show($form);
    }

    public function redirectPromptState(
        string $action
    ): PromptState {
        return $this->ui_factory->prompt()->state()->redirect($this->URIFromTarget($action));
    }

    public function prompt(
        string $title,
        string $async_target
    ): Prompt {
        return $this->ui_factory->prompt()->standard($this->URIFromTarget($async_target))->withTitle($title);
    }

    public function copyrightEditForm(
        string $post_url,
        ?EntryInterface $entry = null
    ): Form {
        $inputs = [];
        $ff = $this->ui_factory->input()->field();

        $title = $ff
            ->text($this->presentation->txt('title'))
            ->withValue($entry?->title() ?? '')
            ->withRequired(true)
            ->withMaxLength(255);
        $inputs['title'] = $title;

        $des = $ff
            ->textarea($this->presentation->txt('description'))
            ->withValue($entry?->description() ?? '');
        $inputs['description'] = $des;

        $usage_value = 'featured';
        if ($entry?->isOutdated()) {
            $usage_value = 'outdated';
        } elseif ($entry !== null && !$entry->isFeatured()) {
            $usage_value = 'in_use';
        }
        $usage = $ff
            ->radio($this->presentation->txt('meta_copyright_usage'))
            ->withOption('featured', $this->presentation->txt('meta_copyright_featured'), $this->presentation->txt('meta_copyright_featured_info'))
            ->withOption('in_use', $this->presentation->txt('meta_copyright_in_use'), $this->presentation->txt('meta_copyright_in_use_info'))
            ->withOption('outdated', $this->presentation->txt('meta_copyright_outdated'), $this->presentation->txt('meta_copyright_outdated_info'))
            ->withValue($usage_value);
        $inputs['usage_status'] = $usage;

        $cp_data = $entry?->copyrightData();

        $full_name = $ff
            ->text($this->presentation->txt('md_copyright_full_name'))
            ->withValue($cp_data?->fullName() ?? '');

        $link = $ff
            ->url(
                $this->presentation->txt('md_copyright_link'),
                $this->presentation->txt('md_copyright_link_info')
            )
            ->withValue((string) $cp_data?->link())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn($v) => $v instanceof URI ? $v : null)
            );

        $image_link = $ff
            ->url($this->presentation->txt('md_copyright_image_link'))
            ->withValue((string) $cp_data?->imageLink())
            ->withAdditionalTransformation($this->refinery->custom()->transformation(
                fn($v) => $v instanceof URI ? $v : null
            ));

        $file_id = empty($cp_data?->imageFile()) ? '' : $cp_data?->imageFile();
        $image_file = $ff
            ->file(
                new HandlerGUI($file_id),
                $this->presentation->txt('md_copyright_image_file')
            )
            ->withMaxFiles(1)
            ->withAcceptedMimeTypes([MimeType::IMAGE__PNG, MimeType::IMAGE__JPEG]);
        if ($file_id !== '') {
            $image_file = $image_file->withValue([$file_id]);
        };

        $image_value = 'link_group';
        if (!is_null($cp_data) && !$cp_data->isImageLink()) {
            $image_value = 'file_group';
        }
        $image = $ff
            ->switchableGroup(
                [
                    'link_group' => $ff->group(
                        ['image_link' => $image_link],
                        $this->presentation->txt('md_copyright_image_is_link')
                    ),
                    'file_group' => $ff->group(
                        ['image_file' => $image_file],
                        $this->presentation->txt('md_copyright_image_is_file')
                    ),
                ],
                $this->presentation->txt('md_copyright_image')
            )
            ->withValue($image_value)
            ->withAdditionalTransformation($this->refinery->custom()->transformation(function ($vs) {
                if ($vs[0] === 'link_group') {
                    return empty($link = $vs[1]['image_link']) ? '' : $link;
                }
                if ($vs[0] === 'file_group') {
                    return $vs[1]['image_file'][0] ?? '';
                }
                return '';
            }));

        $alt_text = $ff
            ->text(
                $this->presentation->txt('md_copyright_alt_text'),
                $this->presentation->txt('md_copyright_alt_text_info')
            )
            ->withValue($cp_data?->altText() ?? '');

        $cop = $ff
            ->section(
                [
                    'full_name' => $full_name,
                    'link' => $link,
                    'image' => $image,
                    'alt_text' => $alt_text
                ],
                $this->presentation->txt('md_copyright_value')
            );
        $inputs['copyright'] = $cop;

        return $this->ui_factory->input()->container()->form()->standard(
            $post_url,
            $inputs
        );
    }

    protected function URIFromTarget(string $target): URI
    {
        return $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            ltrim($target, '/')
        );
    }
}
