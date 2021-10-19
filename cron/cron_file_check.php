<?php
chdir(dirname(__FILE__));
chdir('..');

class ilFileCheck
{
    const FACTOR = 100;
    const MAX_EXPONENT = 3;

    protected $logger;
    protected $db;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->file();
        $this->db = $DIC->database();
    }

    public function runValidator()
    {
        $query = 'select file_id, file_name, version from file_data';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->validateFileRow((int) $row['file_id'], (string) $row['file_name'], (int) $row['version']);
        }
    }

    protected function validateFileRow($file_id, $file_name, $version)
    {
        $path = ilUtil::getDataDir();
        $path .= '/ilFile/';
        $path .= self::_createPathFromId($file_id, 'file');
        $path .= '/';
        $this->logger->debug('Validating file system path: ' . $path);

        $version_dir = $path . str_pad((string) (int) $version, 3, '0', STR_PAD_LEFT);
        $this->logger->debug('Validating file version path: ' .  $version_dir);

        if (!is_dir($version_dir)) {
            if ($version == 0) {
                $this->logger->info('Ignoring empty file');
                return;
            }
            $this->logger->error('Cannot find version directory: ' . $version_dir);
            $version--;
            $old_version_dir = $path . str_pad((string) (int) $version, 3, '0', STR_PAD_LEFT);
            if (is_dir($old_version_dir)) {
                $this->logger->warning('Would rename: ' . $old_version_dir . ' ->  ' . $version_dir);
                rename($old_version_dir, $version_dir);
            } else {
                $this->logger->error('Cannot find version directory: ' . $old_version_dir);
                return;
            }
        }
        $file = $version_dir . '/' . $file_name;
        if (!is_file($file)) {
            $this->logger->error('Cannot find file: ' . $file);
            return;
        }
    }

    /**
     * Create a path from an id: e.g 12345 will be converted to 12/34/<name>_5
     *
     * @access public
     * @static
     *
     * @param int container id
     * @param string name
     */
    public static function _createPathFromId($a_container_id, $a_name)
    {
        $path = array();
        $found = false;
        $num = $a_container_id;
        for ($i = self::MAX_EXPONENT; $i > 0;$i--) {
            $factor = pow(self::FACTOR, $i);
            if (($tmp = (int) ($num / $factor)) or $found) {
                $path[] = $tmp;
                $num = $num % $factor;
                $found = true;
            }
        }

        if (count($path)) {
            $path_string = (implode('/', $path) . '/');
        }
        return $path_string . $a_name . '_' . $a_container_id;
    }

}



include_once './Services/Cron/classes/class.ilCronStartUp.php';

if ($_SERVER['argc'] < 4) {
    echo "Usage: cron.php username password client\n";
    exit(1);
}

$client = $_SERVER['argv'][3];
$login = $_SERVER['argv'][1];
$password = $_SERVER['argv'][2];

$cron = new ilCronStartUp(
    $client,
    $login,
    $password
);

try {
    $cron->authenticate();

    $files = new ilFileCheck();
    $files->runValidator();
    $cron->logout();
} catch (Exception $e) {
    $cron->logout();

    echo $e->getMessage() . "\n";
    exit(1);
}
