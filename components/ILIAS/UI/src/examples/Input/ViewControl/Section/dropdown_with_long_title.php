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

namespace ILIAS\UI\examples\Input\ViewControl\Section;

/**
 * ---
 * description: >
 *   Example of a Section View Control using a select with a long label to demonstrate responsive behavior.
 *
 * expected output: >
 *   ILIAS shows a previous control, a select with a long label and a next control. The current section is shown
 *   below the controls. Selecting an entry or using the navigation controls submits the view-control form.
 * ---
 */
function dropdown_with_long_title(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $long_title = "Second section with a very long title to check the responsive behaviour";
    $sections = [
        'first' => 'First Section',
        'second' => $long_title,
        'third' => 'Third Section',
    ];
    $section = $f->input()->viewControl()->section($sections);

    $container = $f->input()->container()->viewControl()->standard(
        [$section],
        $request->getUri()->getPath()
    )
        ->withRequest($request);
    $current_section = array_values($container->getData())[0] ?? array_key_first($sections);

    return $r->render([
        $f->legacy()->content('<p role="status">Current section: ' . $sections[$current_section] . '</p>'),
        $container
    ]);
}
