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

namespace ILIAS\CalDAV\Auth\Backend;

use Sabre\DAV\Auth\Backend\BackendInterface;
use Sabre\DAV;
use Sabre\HTTP\ResponseInterface;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\Auth\Digest;
use Sabre\HTTP\Auth\Basic;

class ilDAVAuthBackend implements BackendInterface
{
    protected string $realm = 'SabreDAV';

    /**
     * This is the prefix that will be used to generate principal urls.
     *
     * @var string
     */
    protected $principalPrefix = 'principals/';


    public function check(RequestInterface $request, ResponseInterface $response)
    {
        $basic = new Basic(
            $this->realm,
            $request,
            $response
        );

        $credentials = $basic->getCredentials();
        $username = $credentials[0] ?? '';
        $password = $credentials[1] ?? '';

        // No credentials given
        if (!$credentials) {
            return [false, "No 'Authorization: Digest' header found. Either the client didn't send one, or the server is misconfigured"];
        }

        $ok = $this->validateCredentials($this->realm, $username, $password);
        if (!$ok) {
            return [false, 'Username or password was incorrect'];
        }
        return [true, $this->principalPrefix . $username];
    }

    protected function validateCredentials(string $realm, string $username, string $password): bool
    {
        $user = \ilObjectFactory::getInstanceByObjId(\ilObjUser::_loginExists($username), false);
        if (!$user instanceof \ilObjUser) {
            return false;
        }
        return \ilUserPasswordManager::getInstance()->verifyPassword(
            $user,
            $password
        );
    }

    public function challenge(RequestInterface $request, ResponseInterface $response)
    {
        $basic = new Basic(
            $this->realm,
            $request,
            $response
        );

        $oldStatus = $response->getStatus() ?: 200;
        $basic->requireLogin();
        $response->setStatus($oldStatus);
    }
}
