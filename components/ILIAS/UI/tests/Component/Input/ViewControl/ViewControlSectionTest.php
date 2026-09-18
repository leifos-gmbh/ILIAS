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

use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;

require_once('ViewControlTestBase.php');

class ViewControlSectionTest extends ViewControlTestBase
{
    public function testSectionAcceptsOrderedOptions(): void
    {
        $options = ['first' => 'First section', 'second' => 'Second section'];
        $section = $this->buildVCFactory()->section($options);

        $this->assertInstanceOf(Control\Section::class, $section);
        $this->assertSame($options, $section->getOptions());
        $this->assertSame('first', $section->getValue());
        $this->assertSame('second', $section->withValue('second')->getValue());
    }

    public function testSectionSelectRendering(): void
    {
        $section = $this->buildVCFactory()->section([
            'first' => 'First section',
            'second' => 'Second section',
        ]);

        $html = $this->getDefaultRenderer()->render($section);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('First section', $html);
        $this->assertStringContainsString('Second section', $html);
    }
}
