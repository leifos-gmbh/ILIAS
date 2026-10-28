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

/**
 * The process state native gettext (PHP extension "gettext") needs to serve the compiled catalogs of the
 * migrated modules, and the one place that calls it.
 *
 * Native gettext only translates while LC_MESSAGES is not "C"/"POSIX" and finds a catalog as
 * `<bound directory>/<language>/LC_MESSAGES/<domain>.mo`. The catalog language is independent of the
 * active locale's language:
 * - on Linux the build ships its own locale (a copy of "C.utf8", see ShippedTranslationsBuild) named LOCALE_NAME;
 *   activate() loads it via LOCPATH and removes LOCPATH again right away (while it is set, setlocale()
 *   finds no system locale);
 * - on macOS a non-C system locale is used instead, with LC_ALL unset and LC_MESSAGES set in the
 *   environment for libintl; these environment changes are saved and restored along with the locale;
 * - LANGUAGE is CATALOG_LANGUAGE, so "<language>" is always "messages"; the language of a catalog is part
 *   of its domain ("<module>.<lang>", see MigratedLanguageFilePaths), as the C library caches per process
 *   and a later LANGUAGE change would not reliably switch catalogs;
 * - every domain is bound with codeset UTF-8, so LC_CTYPE never converts a value.
 *
 * Both settings are process-wide. PHP takes them back at the end of every request (putenv() variables,
 * setlocale() reset to "C"), so no shutdown function is needed (it would stop later shutdown functions
 * from translating). In a thread-safe (ZTS) PHP the end of another thread's request can take them back
 * mid-request: isActive() checks, reactivateIfLost() activates again (the administration shows a hint,
 * see isThreadSafe()). Child processes inherit LANGUAGE=messages, which is harmless.
 *
 * The C library never reloads a catalog file it has read once (also not a replaced one), so every new
 * state (a build, an overlay revision) gets a directory of its own, and binding the domain loads it.
 */
final class NativeGettext
{
    public const string LOCALE_NAME = 'ilias_messages';
    public const string SYSTEM_LOCALE_NAME = 'en_US.UTF-8';

    /** The value of LANGUAGE: the directory below a bound directory the catalogs are looked up in. */
    public const string CATALOG_LANGUAGE = 'messages';

    public const string CATALOG_DIRECTORY = self::CATALOG_LANGUAGE . '/LC_MESSAGES';

    private const string CODESET = 'UTF-8';

    /** Longer message ids are refused by PHP (ValueError), so they are never found. */
    private const int MAX_MESSAGE_LENGTH = 4096;

    private static ?string $locale_directory = null;

    private static ?string $active_locale_name = null;

    /**
     * LANGUAGE and LC_MESSAGES before the first activate() since the last restore() (`false`: not
     * set), see restore().
     *
     * @var array{language: string|false, messages: string|false}|null
     */
    private static ?array $previous = null;

    /** @var array<string, string|false>|null Environment overrides needed by macOS libintl only. */
    private static ?array $previous_system_environment = null;

    private static ?bool $thread_safe_for_tests = null;

    private static ?string $problem = null;

    public static function isExtensionLoaded(): bool
    {
        return extension_loaded('gettext');
    }

    /**
     * Whether PHP is thread-safe (ZTS): the settings of activate() then apply to every thread and are
     * not taken back.
     */
    public static function isThreadSafe(): bool
    {
        return self::$thread_safe_for_tests ?? (bool) PHP_ZTS;
    }

    /**
     * Makes isThreadSafe() return $thread_safe (`null`: PHP_ZTS again) - for tests only.
     *
     * @internal
     */
    public static function useThreadSafeForTests(?bool $thread_safe): void
    {
        self::$thread_safe_for_tests = $thread_safe;
    }

    /** Activates the copied locale LOCALE_NAME of $locale_directory for LC_MESSAGES and sets LANGUAGE. */
    public static function activate(string $locale_directory): bool
    {
        if (!self::isExtensionLoaded()) {
            self::$problem = 'the PHP extension "gettext" is not loaded in this PHP (the web server needs it like the CLI that ran the build)';
            return false;
        }
        if (self::$locale_directory === $locale_directory && self::isActive()) {
            return true;
        }
        if (!is_dir($locale_directory . '/' . self::LOCALE_NAME)) {
            self::$problem = sprintf('the locale "%s" is missing in "%s"', self::LOCALE_NAME, $locale_directory);
            return false;
        }
        return self::activateLocale($locale_directory, self::LOCALE_NAME, true);
    }

    /** Activates a non-C system locale on macOS, where glibc locale trees cannot be loaded. */
    public static function activateSystemLocale(): bool
    {
        if (PHP_OS_FAMILY !== 'Darwin') {
            self::$problem = 'the system locale fallback is only supported on macOS';
            return false;
        }
        return self::activateLocale('', self::SYSTEM_LOCALE_NAME, false);
    }

    public static function activateForBuild(string $locale_directory): bool
    {
        if (is_dir($locale_directory . '/' . self::LOCALE_NAME)) {
            return self::activate($locale_directory);
        }
        return PHP_OS_FAMILY === 'Darwin'
            ? self::activateSystemLocale()
            : self::activate($locale_directory);
    }

    private static function activateLocale(string $locale_directory, string $locale_name, bool $copied): bool
    {
        if (!self::isExtensionLoaded()) {
            self::$problem = 'the PHP extension "gettext" is not loaded in this PHP (the web server needs it like the CLI that ran the build)';
            return false;
        }
        if (self::$locale_directory === $locale_directory && self::isActive()) {
            return true;
        }
        self::$previous ??= ['language' => getenv('LANGUAGE'), 'messages' => setlocale(LC_MESSAGES, '0')];
        $locpath = getenv('LOCPATH');
        if ($copied) {
            putenv('LOCPATH=' . $locale_directory);
        }
        $locale = setlocale(LC_MESSAGES, $locale_name);
        if ($copied) {
            // as long as LOCPATH is set, setlocale() finds no locale of the system (e.g. for LC_TIME)
            putenv($locpath === false ? 'LOCPATH' : 'LOCPATH=' . $locpath);
        }
        if ($locale !== $locale_name) {
            self::$problem = $copied
                ? sprintf('the locale "%s" of "%s" cannot be activated', $locale_name, $locale_directory)
                : sprintf('the system locale "%s" cannot be activated', $locale_name);
            return false;
        }
        if (!$copied) {
            // macOS libintl can determine the locale from the environment rather than setlocale().
            // LC_ALL takes precedence over LC_MESSAGES; only change the environment, not other C categories.
            self::$previous_system_environment ??= [
                'LC_ALL' => getenv('LC_ALL'),
                'LC_MESSAGES' => getenv('LC_MESSAGES'),
            ];
            putenv('LC_ALL');
            putenv('LC_MESSAGES=' . $locale_name);
        }
        putenv('LANGUAGE=' . self::CATALOG_LANGUAGE);

        self::$locale_directory = $locale_directory;
        self::$active_locale_name = $locale;
        self::$problem = null;

        return true;
    }

    /**
     * Whether activate() succeeded and its settings are still in place (other code, e.g.
     * ilInitialisation::initLocale() with LC_ALL, may have changed LC_MESSAGES since).
     */
    public static function isActive(): bool
    {
        return self::$locale_directory !== null
            && setlocale(LC_MESSAGES, '0') === self::$active_locale_name
            && getenv('LANGUAGE') === self::CATALOG_LANGUAGE
            && (self::$locale_directory !== ''
                || (getenv('LC_ALL') === false && getenv('LC_MESSAGES') === self::SYSTEM_LOCALE_NAME));
    }

    /**
     * Activates the locale again if it was activated before and the settings changed since.
     *
     * @return bool whether it had to be (and could be) activated again - a lookup that found
     *         nothing is worth repeating then
     */
    public static function reactivateIfLost(): bool
    {
        if (self::$locale_directory === null || self::isActive()) {
            return false;
        }

        return self::$locale_directory === '' ? self::activateSystemLocale() : self::activate(self::$locale_directory);
    }

    /**
     * Takes LANGUAGE, LC_MESSAGES and the macOS environment overrides back to the state before the first
     * activate(); settings changed by other code since stay. Only for long-running processes (e.g.
     * Setup's build after its check), a web
     * request needs no call. No-op in a thread-safe PHP, where other threads may use them. The activated
     * directory is kept, so reactivateIfLost() can activate it again.
     */
    public static function restore(): void
    {
        if (self::$previous === null || self::isThreadSafe()) {
            return;
        }
        if (self::$previous['messages'] !== false && setlocale(LC_MESSAGES, '0') === self::$active_locale_name) {
            setlocale(LC_MESSAGES, self::$previous['messages']);
        }
        if (getenv('LANGUAGE') === self::CATALOG_LANGUAGE) {
            putenv(self::$previous['language'] === false ? 'LANGUAGE' : 'LANGUAGE=' . self::$previous['language']);
        }
        if (self::$previous_system_environment !== null) {
            foreach (self::$previous_system_environment as $name => $value) {
                $active = $name === 'LC_ALL' ? false : self::SYSTEM_LOCALE_NAME;
                if (getenv($name) === $active) {
                    putenv($value === false ? $name : $name . '=' . $value);
                }
            }
            self::$previous_system_environment = null;
        }
        self::$previous = null;
    }

    public static function getProblem(): ?string
    {
        return self::$problem;
    }

    /**
     * Binds $domain to $directory with the codeset UTF-8.
     *
     * @return bool `false` if the extension is missing or $directory does not exist
     */
    public static function bind(string $domain, string $directory): bool
    {
        return self::isExtensionLoaded()
            && @bindtextdomain($domain, $directory) !== false
            && @bind_textdomain_codeset($domain, self::CODESET) !== false;
    }

    /**
     * Whether the catalog of the bound $domain is readable: its header (the translation of the empty
     * message id) is not empty.
     */
    public static function isLoaded(string $domain): bool
    {
        return self::isExtensionLoaded() && dgettext($domain, '') !== '';
    }

    /**
     * The translation of $message_id in $domain, or $message_id itself if there is none (as the C
     * library answers), also for an empty or too long $message_id.
     */
    public static function translate(string $domain, string $message_id): string
    {
        if (!self::canLookUp($message_id)) {
            return $message_id;
        }

        return dgettext($domain, $message_id);
    }

    /**
     * The form of the plural message $message_id the "Plural-Forms" rule of the catalog selects for $n
     * (a negative $n counts as its absolute value), or $message_id itself if there is none.
     */
    public static function translatePlural(string $domain, string $message_id, int $n): string
    {
        if (!self::canLookUp($message_id)) {
            return $message_id;
        }
        $n = $n === PHP_INT_MIN ? PHP_INT_MAX : abs($n);

        return dngettext($domain, $message_id, $message_id, $n);
    }

    private static function canLookUp(string $message_id): bool
    {
        return $message_id !== '' && strlen($message_id) <= self::MAX_MESSAGE_LENGTH && self::isExtensionLoaded();
    }

    /**
     * Forgets the activation (without taking the settings back) - for tests only.
     *
     * @internal
     */
    public static function resetForTests(): void
    {
        self::$thread_safe_for_tests = null;
        self::restore();
        self::$locale_directory = null;
        self::$active_locale_name = null;
        self::$previous_system_environment = null;
        self::$previous = null;
        self::$problem = null;
    }
}
