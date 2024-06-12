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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Requests;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OAIPMH\Requests\Argument;
use ILIAS\Data\URI;

class RequestTest extends TestCase
{
    public function getURI(): URI
    {
        return $this->createMock(URI::class);
    }

    public function testVerbAndBaseURL(): void
    {
        $url = $this->getURI();
        $request = new Request($url, Verb::LIST_IDENTIFIERS);

        $this->assertSame($url, $request->baseURL());
        $this->assertSame(Verb::LIST_IDENTIFIERS, $request->verb());
    }

    public function testNoArgument(): void
    {
        $request = new Request($this->getURI(), Verb::NULL);

        foreach (Argument::cases() as $argument) {
            $this->assertFalse($request->hasArgument($argument));
            $this->assertSame('', $request->argumentValue($argument));
        }
    }

    public function testSingleArgument(): void
    {
        $request = new Request($this->getURI(), Verb::NULL);
        $request = $request->withArgument(Argument::FROM_DATE, 'today');

        foreach (Argument::cases() as $argument) {
            if ($argument === Argument::FROM_DATE) {
                continue;
            }
            $this->assertFalse($request->hasArgument($argument));
            $this->assertSame('', $request->argumentValue($argument));
        }

        $this->assertTrue($request->hasArgument(Argument::FROM_DATE));
        $this->assertSame('today', $request->argumentValue(Argument::FROM_DATE));
    }

    public function testMultipleDifferentArguments(): void
    {
        $request = new Request($this->getURI(), Verb::NULL);
        $request = $request->withArgument(Argument::FROM_DATE, 'today');
        $request = $request->withArgument(Argument::RESUMPTION_TOKEN, 'resume!');

        foreach (Argument::cases() as $argument) {
            if (
                $argument === Argument::FROM_DATE ||
                $argument === Argument::RESUMPTION_TOKEN
            ) {
                continue;
            }
            $this->assertFalse($request->hasArgument($argument));
            $this->assertSame('', $request->argumentValue($argument));
        }

        $this->assertTrue($request->hasArgument(Argument::FROM_DATE));
        $this->assertSame('today', $request->argumentValue(Argument::FROM_DATE));
        $this->assertTrue($request->hasArgument(Argument::RESUMPTION_TOKEN));
        $this->assertSame('resume!', $request->argumentValue(Argument::RESUMPTION_TOKEN));
    }

    public function testArgumentKeysNoArgument(): void
    {
        $request = new Request($this->getURI(), Verb::NULL);

        $this->assertNull($request->argumentKeys()->current());
    }

    public function testArgumentKeys(): void
    {
        $request = new Request($this->getURI(), Verb::NULL);

        $request = $request->withArgument(Argument::IDENTIFIER, 'some identifier');
        $request = $request->withArgument(Argument::UNTIL_DATE, 'some date');

        $argument_keys = iterator_to_array($request->argumentKeys());
        $this->assertCount(2, $argument_keys);
        $this->assertContains(Argument::IDENTIFIER, $argument_keys);
        $this->assertContains(Argument::UNTIL_DATE, $argument_keys);
    }
}
