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

use Sabre\CalDAV\Plugin;
use ILIAS\CalDAV\Backend\ilDAVBackend;
use ILIAS\CalDAV\Auth\Backend\ilDAVAuthBackend;

require_once("Services/Init/classes/class.ilInitialisation.php");

const IL_DAV_URI = '/9_dav/dav.php';

try {
    ilAuthFactory::setContext(ilAuthFactory::CONTEXT_HTTP);
    $_GET["client_id"] = 'default';
    $context = ilContext::CONTEXT_WEBDAV;
    ilContext::init($context);
    $post = $_POST;
    ilInitialisation::initILIAS();

    global $DIC;

    $_POST = $post;

    $pdo = new PDO('mysql:dbname=ilias_9_dav;host=localhost', 'ilias', 'Zuonuu6O');
    //$calendarBackend = new Sabre\CalDAV\Backend\PDO($pdo);
    $calendarBackend = new ilDAVBackend($pdo);
    $principalBackend = new \Sabre\DAVACL\PrincipalBackend\PDO($pdo);
    //$authBackend = new Sabre\DAV\Auth\Backend\PDO($pdo);
    $authBackend = new ilDAVAuthBackend();

    $tree = [
        new \Sabre\DAVACL\PrincipalCollection($principalBackend),
        new \Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend)
    ];

    // The object tree needs in turn to be passed to the server class
    $server = new \Sabre\DAV\Server($tree);

    // You are highly encouraged to set your WebDAV server base url. Without it,
    // SabreDAV will guess, but the guess is not always correct. Putting the
    // server on the root of the domain will improve compatibility.
    $server->setBaseUri(IL_DAV_URI);

    $caldavPlugin = new Plugin();
    $server->addPlugin($caldavPlugin);

    $authPlugin = new Sabre\DAV\Auth\Plugin($authBackend);
    $server->addPlugin($authPlugin);

    $browserPlugin = new \Sabre\DAV\Browser\Plugin();
    $server->addPlugin($browserPlugin);

    $server->start();


} catch (InvalidArgumentException $e) {
    ;
}
