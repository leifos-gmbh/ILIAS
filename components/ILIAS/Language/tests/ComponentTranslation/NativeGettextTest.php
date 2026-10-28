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

namespace ILIAS\Language\ComponentTranslation;

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use MigratedPoFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * NativeGettext changes process-wide state (LC_MESSAGES, LANGUAGE, LOCPATH): what activate() leaves behind and
 * restore() gives back, losing and regaining the settings to other code, a build native gettext cannot use, lookups of
 * messages that cannot be looked up.
 *
 * The thread-safe (ZTS) branch is reached through NativeGettext::useThreadSafeForTests().
 */
class NativeGettextTest extends TestCase
{
    private const string DOMAIN = 'ntg.de';

    private string $root;
    private string $build_directory;
    private string $locale_directory;

    /**
     * @var array{language: string|false, locpath: string|false, messages: string|false, all_env: string|false, messages_env: string|false}
     */
    private array $process_state;

    protected function setUp(): void
    {
        MigratedPoFixture::resetRuntime();
        $this->process_state = [
            'language' => getenv('LANGUAGE'),
            'locpath' => getenv('LOCPATH'),
            'messages' => setlocale(LC_MESSAGES, '0'),
            'all_env' => getenv('LC_ALL'),
            'messages_env' => getenv('LC_MESSAGES'),
        ];
        $this->root = sys_get_temp_dir() . '/ilias_native_gettext_' . bin2hex(random_bytes(6));
        mkdir($this->root, 0775, true);

        $catalog = MigratedPoFixture::catalog('ntg', ['greeting' => 'Hallo']);
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $item = new TranslationEntry('ntg', 'item');
        $item->setPlural('items', ['Eintrag', 'Einträge']);
        $catalog->add($item);
        MigratedPoFixture::writePo($this->root . '/ntg/ntg_de.po', $catalog);
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), MigratedPoFixture::directory('ntg', 'ntg/'));
        $build = MigratedPoFixture::build($manager, $this->root)['build'];
        // the build checked itself through native gettext in this process, see ShippedLanguageFilesCompiledObjective
        NativeGettext::restore();
        $this->assertNotNull($build);
        $this->build_directory = MigratedLanguageFilePaths::buildDirectory(MigratedPoFixture::artifactDirectory(), $build);
        $this->locale_directory = MigratedLanguageFilePaths::buildLocaleDirectory($this->build_directory);
    }

    protected function tearDown(): void
    {
        NativeGettext::resetForTests();
        // whatever a test did to the process, the next one starts as this one did
        putenv($this->process_state['language'] === false ? 'LANGUAGE' : 'LANGUAGE=' . $this->process_state['language']);
        putenv($this->process_state['locpath'] === false ? 'LOCPATH' : 'LOCPATH=' . $this->process_state['locpath']);
        putenv($this->process_state['all_env'] === false ? 'LC_ALL' : 'LC_ALL=' . $this->process_state['all_env']);
        putenv($this->process_state['messages_env'] === false ? 'LC_MESSAGES' : 'LC_MESSAGES=' . $this->process_state['messages_env']);
        if ($this->process_state['messages'] !== false) {
            setlocale(LC_MESSAGES, $this->process_state['messages']);
        }
        MigratedPoFixture::removeDirectory($this->root);
        MigratedPoFixture::resetRuntime();
    }

    private function activateBuild(): void
    {
        $this->assertTrue(NativeGettext::activateForBuild($this->locale_directory), (string) NativeGettext::getProblem());
        $this->assertTrue(NativeGettext::bind(self::DOMAIN, $this->build_directory));
    }

    /**
     * @return iterable<string, array{string|false, string|false}>
     */
    public static function previousProcessSettings(): iterable
    {
        yield 'nothing set before' => [false, false];
        yield 'LANGUAGE set before' => ['de:fr', false];
        yield 'LOCPATH set before' => [false, '/nonexistent/locpath'];
        yield 'both set before' => ['en', '/nonexistent/locpath'];
    }

    #[DataProvider('previousProcessSettings')]
    public function testActivateServesTheBuildAndRestoreGivesTheProcessStateBack(string|false $language, string|false $locpath): void
    {
        putenv($language === false ? 'LANGUAGE' : 'LANGUAGE=' . $language);
        putenv($locpath === false ? 'LOCPATH' : 'LOCPATH=' . $locpath);
        $messages_before = setlocale(LC_MESSAGES, '0');

        $this->activateBuild();

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame(PHP_OS_FAMILY === 'Darwin' ? NativeGettext::SYSTEM_LOCALE_NAME : NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));
        $this->assertSame(NativeGettext::CATALOG_LANGUAGE, getenv('LANGUAGE'));
        $this->assertSame($locpath, getenv('LOCPATH'), 'LOCPATH only lives for the setlocale() call (no system locale is found while it is set)');
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));

        NativeGettext::restore();

        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame($messages_before, setlocale(LC_MESSAGES, '0'));
        $this->assertSame($language, getenv('LANGUAGE'));
        $this->assertSame($locpath, getenv('LOCPATH'));
        if (PHP_OS_FAMILY !== 'Darwin') {
            $this->assertSame('greeting', NativeGettext::translate(self::DOMAIN, 'greeting'), 'without the locale native gettext answers with the message id');
        }
    }

    /** @return iterable<string, array{string|false, string|false}> */
    public static function systemLocaleEnvironments(): iterable
    {
        yield 'C overrides a translated locale' => ['C', 'de_DE.UTF-8'];
        yield 'C.UTF-8 overrides C' => ['C.UTF-8', 'C'];
        yield 'POSIX' => ['POSIX', false];
        yield 'category only' => [false, 'C.UTF-8'];
        yield 'no overrides' => [false, false];
    }

    #[DataProvider('systemLocaleEnvironments')]
    public function testSystemLocaleTranslatesAndRestoresEnvironment(string|false $all, string|false $messages): void
    {
        if (PHP_OS_FAMILY !== 'Darwin') {
            $this->markTestSkipped('Only macOS needs environment overrides for the system locale.');
        }
        NativeGettext::resetForTests();
        setlocale(LC_MESSAGES, 'C');
        $time = setlocale(LC_TIME, '0');
        $numeric = setlocale(LC_NUMERIC, '0');
        putenv($all === false ? 'LC_ALL' : 'LC_ALL=' . $all);
        putenv($messages === false ? 'LC_MESSAGES' : 'LC_MESSAGES=' . $messages);
        putenv('LANGUAGE=de');

        $this->activateBuild();

        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
        $this->assertSame('Einträge', NativeGettext::translatePlural(self::DOMAIN, ShippedTranslations::PLURAL_CONTEXT . "\x04item", 2));
        $this->assertSame($time, setlocale(LC_TIME, '0'));
        $this->assertSame($numeric, setlocale(LC_NUMERIC, '0'));
        $this->assertFalse(getenv('LC_ALL'));
        $this->assertSame(NativeGettext::SYSTEM_LOCALE_NAME, getenv('LC_MESSAGES'));

        NativeGettext::restore();

        $this->assertSame('C', setlocale(LC_MESSAGES, '0'));
        $this->assertSame($all, getenv('LC_ALL'));
        $this->assertSame($messages, getenv('LC_MESSAGES'));
        $this->assertSame('de', getenv('LANGUAGE'));
    }

    public function testRestoreLeavesForeignSystemEnvironmentChangesAlone(): void
    {
        if (PHP_OS_FAMILY !== 'Darwin') {
            $this->markTestSkipped('Only macOS changes the system locale environment.');
        }
        $this->activateBuild();
        putenv('LC_ALL=fr_FR.UTF-8');
        putenv('LC_MESSAGES=de_DE.UTF-8');

        NativeGettext::restore();

        $this->assertSame('fr_FR.UTF-8', getenv('LC_ALL'));
        $this->assertSame('de_DE.UTF-8', getenv('LC_MESSAGES'));
    }

    public function testRestoreLeavesASettingOtherCodeChangedSinceAsItIs(): void
    {
        $this->activateBuild();
        setlocale(LC_MESSAGES, 'C');
        putenv('LANGUAGE=fr');

        NativeGettext::restore();

        $this->assertSame('C', setlocale(LC_MESSAGES, '0'));
        $this->assertSame('fr', getenv('LANGUAGE'));
    }

    public function testRestoreWithoutActivationChangesNothing(): void
    {
        putenv('LANGUAGE=de');
        $messages = setlocale(LC_MESSAGES, '0');

        NativeGettext::restore();

        $this->assertSame('de', getenv('LANGUAGE'));
        $this->assertSame($messages, setlocale(LC_MESSAGES, '0'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function directoriesWithoutAUsableLocale(): iterable
    {
        yield 'no locale directory' => ['none'];
        yield 'an empty locale directory' => ['empty'];
    }

    #[DataProvider('directoriesWithoutAUsableLocale')]
    public function testActivateFailsWithAProblemAndLeavesTheProcessAsItWas(string $kind): void
    {
        $directory = $this->root . '/locale-' . $kind;
        mkdir($directory . ($kind === 'empty' ? '/' . NativeGettext::LOCALE_NAME : ''), 0775, true);
        putenv('LANGUAGE=de');
        putenv('LOCPATH=/nonexistent/locpath');
        $messages = setlocale(LC_MESSAGES, '0');

        $this->assertFalse(NativeGettext::activate($directory));

        $this->assertStringContainsString(NativeGettext::LOCALE_NAME, (string) NativeGettext::getProblem());
        $this->assertFalse(NativeGettext::isActive());
        $this->assertSame($messages, setlocale(LC_MESSAGES, '0'));
        $this->assertSame('de', getenv('LANGUAGE'));
        $this->assertSame('/nonexistent/locpath', getenv('LOCPATH'));
    }

    /**
     * @return iterable<string, array{\Closure(): void}>
     */
    public static function foreignChanges(): iterable
    {
        yield 'LC_MESSAGES set to C' => [static function (): void {
            setlocale(LC_MESSAGES, 'C');
        }];
        yield 'LC_ALL set to C (what initLocale() does)' => [static function (): void {
            setlocale(LC_ALL, 'C');
        }];
        yield 'LANGUAGE changed' => [static function (): void {
            putenv('LANGUAGE=de');
        }];
        if (PHP_OS_FAMILY === 'Darwin') {
            yield 'LC_ALL environment reset to C' => [static function (): void {
                putenv('LC_ALL=C');
            }];
            yield 'LC_MESSAGES environment reset to C' => [static function (): void {
                putenv('LC_MESSAGES=C');
            }];
        }
        yield 'LANGUAGE removed' => [static function (): void {
            putenv('LANGUAGE');
        }];
    }

    #[DataProvider('foreignChanges')]
    public function testReactivateIfLostRepairsWhatOtherCodeChanged(\Closure $change): void
    {
        $this->activateBuild();
        $change();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');
        // macOS libintl may retain a cached translation after a foreign locale/environment change.
        if (PHP_OS_FAMILY !== 'Darwin') {
            $this->assertSame('greeting', NativeGettext::translate(self::DOMAIN, 'greeting'), 'precondition: nothing is translated now');
        }

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertTrue(NativeGettext::isActive());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    public function testReactivateIfLostDoesNothingWithoutAnActivationOrWhileActive(): void
    {
        // setUp()'s build activated native gettext - restore() keeps that locale for a reactivation
        NativeGettext::resetForTests();
        $this->assertFalse(NativeGettext::reactivateIfLost(), 'never activated');
        $this->assertNotSame(NativeGettext::LOCALE_NAME, setlocale(LC_MESSAGES, '0'));

        $this->activateBuild();
        $this->assertFalse(NativeGettext::reactivateIfLost(), 'still active');
    }

    public function testAfterRestoreReactivateIfLostActivatesTheLocaleAgain(): void
    {
        $this->activateBuild();
        NativeGettext::restore();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());
        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    public function testRestoreChangesNothingInAThreadSafePhp(): void
    {
        $this->activateBuild();
        NativeGettext::useThreadSafeForTests(true);

        NativeGettext::restore();

        $this->assertTrue(NativeGettext::isThreadSafe());
        $this->assertTrue(NativeGettext::isActive(), 'other threads may be using the settings');
    }

    /**
     * In a thread-safe PHP restore() does nothing, but another thread may reset what activate() set; the lookup must
     * repair that.
     */
    #[DataProvider('foreignChanges')]
    public function testReactivateIfLostRepairsAForeignResetInAThreadSafePhpToo(\Closure $change): void
    {
        $this->activateBuild();
        NativeGettext::useThreadSafeForTests(true);
        NativeGettext::restore();
        $this->assertTrue(NativeGettext::isActive(), 'precondition: nothing was taken back');
        $change();
        $this->assertFalse(NativeGettext::isActive(), 'precondition');

        $this->assertTrue(NativeGettext::reactivateIfLost());

        $this->assertSame('Hallo', NativeGettext::translate(self::DOMAIN, 'greeting'));
    }

    public function testReactivateIfLostFailsWithAProblemWhenTheLocaleIsGone(): void
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            $this->markTestSkipped('macOS uses a system locale, not a removable copy in the build.');
        }
        $this->activateBuild();
        setlocale(LC_MESSAGES, 'C');
        MigratedPoFixture::removeDirectory($this->locale_directory . '/' . NativeGettext::LOCALE_NAME);

        $this->assertFalse(NativeGettext::reactivateIfLost());

        $this->assertStringContainsString('is missing', (string) NativeGettext::getProblem());
        $this->assertFalse(NativeGettext::isActive());
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function catalogs(): iterable
    {
        yield 'the catalog of the build' => ['build', true];
        yield 'garbage instead of a catalog' => ['garbage', false];
        yield 'a catalog without header (not one of ours)' => ['no-header', false];
        yield 'a domain without file' => ['missing', false];
    }

    #[DataProvider('catalogs')]
    public function testIsLoadedTellsAReadableCatalogOfOurOwnByItsHeader(string $kind, bool $expected): void
    {
        $this->activateBuild();
        $directory = $this->build_directory;
        $domain = self::DOMAIN;
        if ($kind !== 'build') {
            $domain = 'other.' . $kind;
            $directory = $this->root . '/catalogs-' . $kind;
            $file = $directory . '/' . NativeGettext::CATALOG_DIRECTORY . '/' . $domain . '.mo';
            mkdir(dirname($file), 0775, true);
            if ($kind === 'garbage') {
                file_put_contents($file, "this is no .mo file\n");
            } elseif ($kind === 'no-header') {
                $catalog = new TranslationCatalog();
                $entry = new TranslationEntry(null, 'greeting');
                $entry->translate('Hallo');
                $catalog->add($entry);
                file_put_contents($file, $catalog->toMoString());
            }
        }
        $this->assertTrue(NativeGettext::bind($domain, $directory));

        $this->assertSame($expected, NativeGettext::isLoaded($domain));
    }

    public function testBindRefusesADirectoryThatDoesNotExist(): void
    {
        $this->assertFalse(NativeGettext::bind('ntg.xx', $this->root . '/does-not-exist'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function messageIdsThatCannotBeLookedUp(): iterable
    {
        yield 'empty (would answer with the header)' => [''];
        yield 'longer than PHP accepts' => [str_repeat('x', 4097)];
        yield 'unknown' => ['no_such_key'];
    }

    #[DataProvider('messageIdsThatCannotBeLookedUp')]
    public function testTranslateAnswersTheMessageIdItself(string $message_id): void
    {
        $this->activateBuild();

        $this->assertSame($message_id, NativeGettext::translate(self::DOMAIN, $message_id));
        $this->assertSame($message_id, NativeGettext::translatePlural(self::DOMAIN, $message_id, 2));
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function quantities(): iterable
    {
        yield 'one' => [1, 'Eintrag'];
        yield 'zero' => [0, 'Einträge'];
        yield 'minus one counts like one' => [-1, 'Eintrag'];
        yield 'minus five counts like five' => [-5, 'Einträge'];
        yield 'the largest integer' => [PHP_INT_MAX, 'Einträge'];
        yield 'the smallest integer (abs() would overflow)' => [PHP_INT_MIN, 'Einträge'];
    }

    #[DataProvider('quantities')]
    public function testTranslatePluralSelectsTheFormOfTheAbsoluteQuantity(int $n, string $expected): void
    {
        $this->activateBuild();

        $this->assertSame(
            $expected,
            NativeGettext::translatePlural(self::DOMAIN, ShippedTranslations::PLURAL_CONTEXT . "\x04item", $n)
        );
    }
}
