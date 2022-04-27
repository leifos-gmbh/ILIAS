<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\DI\HTTPServices as HTTPServices;

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';

/**
 * Auth prvider for ecs auth
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderECS extends ilAuthProvider implements ilAuthProviderInterface
{
    private HTTPServices $http;
    private Refinery $refinery;
    protected $mid = null;
    protected $abreviation = null;

    protected $currentServer = null;
    protected $servers = null;

    protected $lng;


    /**
     * Constructor
     * @param \ilAuthCredentials $credentials
     */
    public function __construct(\ilAuthCredentials $credentials)
    {
        global $DIC;

        parent::__construct($credentials);
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ecs');
        $this->initECSServices();

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }
    
    /**
     * get abbreviation
     *
     * @access public
     * @param
     *
     */
    public function getAbreviation()
    {
        return $this->abreviation;
    }
    
    /**
     * get mid
     *
     * @access public
     */
    public function getMID()
    {
        return $this->mid;
    }
    
    public function setMID($a_mid)
    {
        $this->mid = $a_mid;
    }

    /**
     * Set current server
     * @param ilECSSetting $server
     */
    public function setCurrentServer(ilECSSetting $server = null)
    {
        $this->currentServer = $server;
    }

    /**
     * Get current server
     * @return ilECSSetting
     */
    public function getCurrentServer()
    {
        return $this->currentServer;
    }

    /**
     * Get server settings
     * @return ilECSServerSettings
     */
    public function getServerSettings()
    {
        return $this->servers;
    }
    

    /**
     * Tra ecs authentication
     * @param \ilAuthStatus $status
     * @return boolean
     */
    public function doAuthentication(\ilAuthStatus $status)
    {
        $this->getLogger()->debug('Starting ECS authentication');
        if (!$this->getServerSettings()->activeServerExists()) {
            $this->getLogger()->warning('No active ecs server found. Aborting');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }
        
        // Iterate through all active ecs instances
        include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
        foreach ($this->getServerSettings()->getServers() as $server) {
            $this->setCurrentServer($server);
            if ($this->validateHash()) {
                return $this->handleLoginByAuthMode($status);

            }
        }
        $this->getLogger()->warning('Could not validate ecs hash for any active server.');
        $this->handleAuthenticationFail($status, 'err_wrong_login');
        return false;
    }

    /**
     * Redirects to shibboleth login; to standard login page for LDAP based authentication or authenticates/creates
     * a local account
     */
    protected function handleLoginByAuthMode(ilAuthStatus $status) : int
    {
        global $DIC;

        $auth_session = $DIC['ilAuthSession'];

        $is_external_account = (bool) ($this->http->request()->getQueryParams()['ecs_external_account'] ?? false);

        $part_settings = new ilECSParticipantSetting(
            $this->getCurrentServer()->getServerId(),
            $this->getMID()
        );
        if ($this->resumeCurrentSession()) {
            $this->getLogger()->debug('Continuing current user session');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            $status->setAuthenticatedUserId($DIC['ilAuthSession']->getUserId());
            return true;
        }
        if (
            $is_external_account &&
            $part_settings->getIncomingAuthType() === ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE
        ) {
            $this->getLogger()->info('ILIAS login page authentication required.');
            ilSession::set('success', $this->lng->txt('ecs_login_success_ilias'));
            $this->initRemoteUser();
            $DIC->ctrl()->redirectToURL('login.php?target=' . $_GET['target']);
            return false;
        }
        if (
            $is_external_account &&
            $part_settings->getIncomingAuthType() === ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH
        ) {
            $this->getLogger()->info('Redirect to shibboleth authentication');
            $this->initRemoteUser();
            $DIC->ctrl()->redirectToURL('shib_login.php?target=' . $_GET['target']);
        }
        if ($part_settings->areIncomingLocalAccountsSupported()) {
            // handle successful authentication
            $new_usr_id = $this->handleLogin();
            $this->getLogger()->info('ECS authentication successful.');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            $status->setAuthenticatedUserId($new_usr_id);
            return true;
        }
    }

    protected function resumeCurrentSession()
    {
        global $DIC;

        $admin = $DIC->rbac()->admin();

        $auth_session = $DIC['ilAuthSession'];
        $session_user_id = $auth_session->getUserId();
        if (!$session_user_id || $session_user_id == ANONYMOUS_USER_ID) {
            $this->getLogger()->debug('No valid session found');
            $auth_session->setAuthenticated(false, ANONYMOUS_USER_ID);
            return false;
        }
        $session_ext_account = ilObjUser::_lookupExternalAccount($session_user_id);
        $user = new ilECSUser($_GET);
        $this->getLogger()->debug('ECS user name: ' . $user->getLogin());
        $this->getLogger()->debug('Session external account: ' . $session_ext_account);
        if (!$session_ext_account || strcmp($user->getLogin(), $session_ext_account) !== 0) {
            $this->getLogger()->debug('No matching session found. Terminating current user session.');
            $auth_session->setAuthenticated(false, ANONYMOUS_USER_ID);
            return false;
        } else {
            // assign to ECS global role
            $admin->assignUser($this->getCurrentServer()->getGlobalRole(), $auth_session->getUserId());
        }
        return true;
    }
    
    
    /**
     * Called from base class after successful login
     *
     * @param string username
     */
    public function handleLogin()
    {
        include_once('./Services/WebServices/ECS/classes/class.ilECSUser.php');
        
        $user = new ilECSUser($_GET);
        
        if (!$usr_id = ilObject::_lookupObjIdByImportId($user->getImportId())) {
            $username = $this->createUser($user);
        } else {
            $username = $this->updateUser($user, $usr_id);
        }
        
        // set user imported
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $import = new ilECSImport($this->getCurrentServer()->getServerId(), $usr_id);
        $import->save();
        
        // Store remote user data
        include_once './Services/WebServices/ECS/classes/class.ilECSRemoteUser.php';
        $remote = new ilECSRemoteUser();
        $remote->setServerId($this->getCurrentServer()->getServerId());
        $remote->setMid($this->getMID());
        $remote->setRemoteUserId($user->getImportId());
        $remote->setUserId(ilObjUser::_lookupId($username));

        $this->getLogger()->info('Current user is: ' . $username);
        
        if (!$remote->exists()) {
            $remote->create();
        }
        return ilObjUser::_lookupId($username);
    }

    public function initRemoteUser() : void
    {
        $user = new ilECSUser($_GET);
        $remote = new ilECSRemoteUser();
        $remote->setServerId($this->getCurrentServer()->getServerId());
        $remote->setMid($this->getMID());
        $remote->setRemoteUserId($user->getLogin());
        $remote->create();
    }
    
    
    /**
     * Validate ECS hash
     *
     * @access public
     * @param string username
     * @param string pass
     *
     */
    public function validateHash()
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        // fetch hash
        if (isset($_GET['ecs_hash']) and strlen($_GET['ecs_hash'])) {
            $hash = $_GET['ecs_hash'];
        }
        if (isset($_GET['ecs_hash_url'])) {
            $hashurl = urldecode($_GET['ecs_hash_url']);
            $hash = basename(parse_url($hashurl, PHP_URL_PATH));
            //$hash = urldecode($_GET['ecs_hash_url']);
        }
        
        $this->getLogger()->info('Using ecs hash: ' . $hash);
        // Check if hash is valid ...
        try {
            include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
            $connector = new ilECSConnector($this->getCurrentServer());
            $res = $connector->getAuth($hash);
            $auths = $res->getResult();
            
            $this->getLogger()->dump($auths, ilLogLevel::DEBUG);

            if ($auths->pid) {
                try {
                    include_once './Services/WebServices/ECS/classes/class.ilECSCommunityReader.php';
                    $reader = ilECSCommunityReader::getInstanceByServerId($this->getCurrentServer()->getServerId());
                    foreach ($reader->getParticipantsByPid($auths->pid) as $participant) {
                        if ($participant->getOrganisation() instanceof \ilECSOrganisation) {
                            $this->abreviation = $participant->getOrganisation()->getAbbreviation();
                            break;
                        }
                    }
                    if (!$this->abreviation) {
                        $this->abreviation = $auths->abbr;
                    }
                } catch (Exception $e) {
                    $this->getLogger()->warning('Authentication failed with message: ' . $e->getMessage());
                    return false;
                }
            } else {
                $this->abreviation = $auths->abbr;
            }
            
            $this->getLogger()->debug('Got abbreviation: ' . $this->abreviation);
        } catch (ilECSConnectorException $e) {
            $this->getLogger()->warning('Authentication failed with message: ' . $e->getMessage());
            return false;
        }
        
        // read current mid
        try {
            include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
            $connector = new ilECSConnector($this->getCurrentServer());
            $details = $connector->getAuth($hash, true);
            
            $this->getLogger()->dump($details, ilLogLevel::DEBUG);
            $this->getLogger()->debug('Token create for mid: ' . $details->getFirstSender());
            
            $this->setMID($details->getFirstSender());
        } catch (ilECSConnectorException $e) {
            $this->getLogger()->warning('Receiving mid failed with message: ' . $e->getMessage());
            return false;
        }
        return true;
    }
    
    
    /**
     * Init ECS Services
     * @access private
     * @param
     *
     */
    private function initECSServices()
    {
        include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
        $this->servers = ilECSServerSettings::getInstance();
    }
    
    /**
     * create new user
     *
     * @access protected
     */
    protected function createUser(ilECSUser $user)
    {
        global $DIC;

        $ilClientIniFile = $DIC['ilClientIniFile'];
        $ilSetting = $DIC['ilSetting'];
        $rbacadmin = $DIC['rbacadmin'];
        $ilLog = $DIC['ilLog'];

        $userObj = new ilObjUser();
        $userObj->setOwner(SYSTEM_USER_ID);

        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
        $local_user = ilAuthUtils::_generateLogin($this->getAbreviation() . '_' . $user->getLogin());

        $newUser["login"] = $local_user;
        $newUser["firstname"] = $user->getFirstname();
        $newUser["lastname"] = $user->getLastname();
        $newUser['email'] = $user->getEmail();
        $newUser['institution'] = $user->getInstitution();

        // set "plain md5" password (= no valid password)
        $newUser["passwd"] = "";
        $newUser["passwd_type"] = IL_PASSWD_CRYPTED;

        $newUser["auth_mode"] = "ecs";
        $newUser["profile_incomplete"] = 0;

        // system data
        $userObj->assignData($newUser);
        $userObj->setTitle($userObj->getFullname());
        $userObj->setDescription($userObj->getEmail());

        // set user language to system language
        $userObj->setLanguage($ilSetting->get("language"));

        // Time limit
        $userObj->setTimeLimitOwner(7);
        $userObj->setTimeLimitUnlimited(0);
        $userObj->setTimeLimitFrom(time() - 5);
        $userObj->setTimeLimitUntil(time() + $ilClientIniFile->readVariable("session", "expire"));

        #$now = new ilDateTime(time(), IL_CAL_UNIX);
        #$userObj->setAgreeDate($now->get(IL_CAL_DATETIME));

        // Create user in DB
        $userObj->setOwner(6);
        $userObj->create();
        $userObj->setActive(1);
        $userObj->updateOwner();
        $userObj->saveAsNew();
        $userObj->writePrefs();

        if ($global_role = $this->getCurrentServer()->getGlobalRole()) {
            $rbacadmin->assignUser($this->getCurrentServer()->getGlobalRole(), $userObj->getId(), true);
        }
        ilObject::_writeImportId($userObj->getId(), $user->getImportId());

        $this->getLogger()->info('Created new remote user with usr_id: ' . $user->getImportId());

        // Send Mail
        #$this->sendNotification($userObj);
        $this->resetMailOptions($userObj->getId());
        
        return $userObj->getLogin();
    }
    
    /**
     * update existing user
     *
     * @access protected
     */
    protected function updateUser(ilECSUser $user, $a_local_user_id)
    {
        global $DIC;

        $ilClientIniFile = $DIC['ilClientIniFile'];
        $ilLog = $DIC['ilLog'];
        $rbacadmin = $DIC['rbacadmin'];
        
        $user_obj = new ilObjUser($a_local_user_id);
        $user_obj->setFirstname($user->getFirstname());
        $user_obj->setLastname($user->getLastname());
        $user_obj->setEmail($user->getEmail());
        $user_obj->setInstitution($user->getInstitution());
        $user_obj->setActive(true);
        
        $until = $user_obj->getTimeLimitUntil();

        if ($until < (time() + $ilClientIniFile->readVariable('session', 'expire'))) {
            $user_obj->setTimeLimitFrom(time() - 60);
            $user_obj->setTimeLimitUntil(time() + $ilClientIniFile->readVariable("session", "expire"));
        }
        $user_obj->update();
        $user_obj->refreshLogin();
        
        if ($global_role = $this->getCurrentServer()->getGlobalRole()) {
            $rbacadmin->assignUser(
                $this->getCurrentServer()->getGlobalRole(),
                $user_obj->getId(),
                true
            );
        }
        
        $this->resetMailOptions($a_local_user_id);

        $this->getLogger()->debug('Finished update of remote user with usr_id: ' . $user->getImportId());
        return $user_obj->getLogin();
    }
    
    /**
     * Reset mail options to "local only"
     *
     */
    protected function resetMailOptions($a_usr_id)
    {
        include_once './Services/Mail/classes/class.ilMailOptions.php';
        $options = new ilMailOptions($a_usr_id);
        $options->setIncomingType(ilMailOptions::INCOMING_LOCAL);
        $options->updateOptions();
    }
}
