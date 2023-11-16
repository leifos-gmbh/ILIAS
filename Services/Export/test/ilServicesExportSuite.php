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

use PHPUnit\Framework\TestSuite;

class ilServicesExportSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilServicesExportSuite();

        include_once("./Services/Export/test/ilExportOptionsTest.php");
        $suite->addTestSuite(ilExportOptionsTest::class);

        ilServicesExportSuite::addImportHandlerTests($suite);

        return $suite;
    }

    public static function addImportHandlerTests(ilServicesExportSuite $suite): void
    {
        $base_path = __DIR__ . DIRECTORY_SEPARATOR . "ImportHandler";
        $dir_infos = [[array_diff(scandir($base_path), array('.', '..')), $base_path]];

        while (count($dir_infos) > 0) {
            $current_dir_info = array_shift($dir_infos);
            $dir_files = $current_dir_info[0];
            $dir_path = $current_dir_info[1];
            foreach ($dir_files as $dir_file) {
                $file_path = $dir_path . DIRECTORY_SEPARATOR . $dir_file;
                if (is_dir($file_path)) {
                    $new_dir_files = array_diff(scandir($file_path), array('.', '..'));
                    $dir_infos[] = [$new_dir_files, $file_path];
                    continue;
                }
                if (str_ends_with($dir_file, '.php')) {
                    include_once($dir_file);
                    $class_name = substr($dir_file, 0, count($dir_file) - 4);
                    $suite->addTestSuite($dir_file::class);
                }
            }
        }
    }
}
