<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSServerSettings.php';

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipantSettingsGUI
{
    private $server_id = 0;
    private $mid = 0;
    
    private $participant = null;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_renderer;
    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_factory;

    protected $tpl;
    protected $lng;
    protected $ctrl;
    protected $tabs;
    protected $toolbar;
    protected $global_settings;



    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_server_id, $a_mid)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];

        $this->server_id = $a_server_id;
        $this->mid = $a_mid;
        
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule('ecs');
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->global_settings = $DIC->settings();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->initSettings();
        $this->initParticipant();
    }
    
    /**
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->server_id;
    }
    
    /**
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }
    
    /**
     *
     * @return ilTemplate
     */
    public function getTemplate()
    {
        return $this->tpl;
    }
    
    /**
     *
     * @return ilCtrl
     */
    public function getCtrl()
    {
        return $this->ctrl;
    }
    
    /**
     * return ilLanguage
     */
    public function getLang()
    {
        return $this->lng;
    }
    
    /**
     *
     * @return ilECSParticipantSetting
     */
    public function getParticipant()
    {
        return $this->participant;
    }


    /**
     * Execute command
     *
     * @access public
     * @param
     *
     */
    public function executeCommand()
    {
        $this->getCtrl()->saveParameter($this, 'server_id');
        $this->getCtrl()->saveParameter($this, 'mid');

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd('settings');

        $this->setTabs();
        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
        
        
        return true;
    }
    
    /**
     * Abort editing
     */
    protected function abort()
    {
        $this->getCtrl()->returnToParent($this);
    }


    /**
     * Settings
     * @param ilPropertyFormGUI $form
     */
    protected function settings(ilPropertyFormGUI $form = null)
    {
        $this->renderConsentToolbar();

        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormSettings();
        }
        $this->getTemplate()->setContent($form->getHTML());
    }

    protected function renderConsentToolbar() : void
    {
        $consents = new ilECSParticipantConsents($this->getServerId(), $this->getMid());
        if (!$consents->hasConsents()) {
            return;
        }

        $confirm = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('ecs_consent_reset_confirm_title'),
            $this->lng->txt('ecs_consent_reset_confirm_title_info'),
            $this->ctrl->getLinkTarget($this, 'resetConsents')
        );
        $this->toolbar->addComponent($confirm);

        $confirmation_trigger = $this->ui_factory->button()->standard(
            $this->lng->txt('ecs_consent_reset_confirm_title'),
            ''
        )->withOnClick($confirm->getShowSignal());
        $this->toolbar->addComponent($confirmation_trigger);
    }

    protected function resetConsents()
    {
        $consents = new ilECSParticipantConsents($this->getServerId(), $this->getMid());
        $consents->delete();
        ilUtil::sendSuccess($this->lng->txt('ecs_user_consents_deleted'), true);
        $this->ctrl->redirect($this, 'settings');
    }
    
    /**
     * Save settings
     */
    protected function saveSettings()
    {
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->getParticipant()->enableToken($form->getInput('token'));
            $this->getParticipant()->enableDeprecatedToken($form->getInput('dtoken'));
            $this->getParticipant()->enableExport($form->getInput('export'));
            $this->getParticipant()->setExportTypes($form->getInput('export_types'));
            $this->getParticipant()->enableImport($form->getInput('import'));
            $this->getParticipant()->setImportTypes($form->getInput('import_types'));
            $this->getParticipant()->enableIncomingLocalAccounts((bool) $form->getInput('incoming_local_accounts'));
            $this->getParticipant()->setIncomingAuthType((int) $form->getInput('incoming_auth_type'));
            $this->getParticipant()->setOutgoingAuthModes((array) $form->getInput('outgoing_auth_modes'));

            // placeholders
            $placeholders = [];
            foreach ($this->parseAvailableAuthModes() as $authmode_name => $authmode_text) {
                $placeholders[$authmode_name] = $form->getInput(
                    'username_placeholder_' . $authmode_name
                );
            }
            $this->getParticipant()->setOutgoingUsernamePlaceholders($placeholders);

            // additional validation
            $error_code = $this->getParticipant()->validate();
            switch ($error_code) {
                case ilECSParticipantSetting::ERR_MISSING_USERNAME_PLACEHOLDER:
                    $form->getItemByPostVar('outgoing_auth_modes')->setAlert(
                        $this->lng->txt('ecs_username_place_holder_err_mssing_placeholder')
                    );
                    break;
                default:
                    $this->getParticipant()->update();
                    ilUtil::sendSuccess($this->getLang()->txt('settings_saved'), true);
                    $this->getCtrl()->redirect($this, 'settings');
                    return true;

            }

        }
        $form->setValuesByPost();
        ilUtil::sendFailure($this->getLang()->txt('err_check_input'));
        $this->settings($form);
    }
    
    /**
     * Init settings form
     */
    protected function initFormSettings()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->getCtrl()->getFormAction($this));
        $form->setTitle($this->getLang()->txt('ecs_part_settings') . ' ' . $this->getParticipant()->getTitle());
        
        
        $token = new ilCheckboxInputGUI($this->getLang()->txt('ecs_token_mechanism'), 'token');
        $token->setInfo($this->getLang()->txt('ecs_token_mechanism_info'));
        $token->setValue(1);
        $token->setChecked($this->getParticipant()->isTokenEnabled());
        $form->addItem($token);
        
        $dtoken = new ilCheckboxInputGUI($this->getLang()->txt('ecs_deprecated_token'), 'dtoken');
        $dtoken->setInfo($this->getLang()->txt('ecs_deprecated_token_info'));
        $dtoken->setValue(1);
        $dtoken->setChecked($this->getParticipant()->isDeprecatedTokenEnabled());
        $form->addItem($dtoken);
        
        // Export options
        $export = new ilCheckboxInputGUI($this->getLang()->txt('ecs_tbl_export'), 'export');
        $export->setValue(1);
        $export->setChecked($this->getParticipant()->isExportEnabled());
        $form->addItem($export);

        $auth_types = new ilCheckboxInputGUI(
            $this->lng->txt('ecs_export_local_account'),
            'incoming_local_accounts'
        );
        $auth_types->setInfo($this->lng->txt('ecs_export_local_account_info'));
        $auth_types->setChecked($this->getParticipant()->areIncomingLocalAccountsSupported());
        $export->addSubItem($auth_types);

        // radio group with login page and (optional) shibboleth option
        $external_auth_type = new ilRadioGroupInputGUI(
            $this->lng->txt('ecs_export_auth_type'),
            'incoming_auth_type'
        );
        $external_auth_type->setInfo($this->lng->txt('ecs_export_auth_type_info'));
        $external_auth_type->setValue($this->getParticipant()->getIncomingAuthType());
        $external_auth_type->addOption(
            new ilRadioOption(
                $this->lng->txt('ecs_export_auth_type_ilias'),
                ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE
            )
        );
        if ($this->isShibbolethActive()) {
            $external_auth_type->addOption(
                new ilRadioOption(
                    $this->lng->txt('ecs_export_auth_type_shib'),
                    ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH
                )
            );
        }
        $external_auth_type->addOption(
            new ilRadioOption(
                $this->lng->txt('ecs_export_auth_type_none'),
                ilECSParticipantSetting::INCOMING_AUTH_TYPE_INACTIVE
            )
        );
        $export->addSubItem($external_auth_type);

        // Export types
        $obj_types = new ilCheckboxGroupInputGUI($this->getLang()->txt('ecs_export_types'), 'export_types');
        include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
        foreach (ilECSUtils::getPossibleReleaseTypes(true) as $type => $trans) {
            $obj_types->addOption(new ilCheckboxOption($trans, $type));
        }
        $export->addSubItem($obj_types);

        // Import
        $import = new ilCheckboxInputGUI($this->getLang()->txt('ecs_tbl_import'), 'import');
        $import->setValue(1);
        $import->setChecked($this->getParticipant()->isImportEnabled());
        $form->addItem($import);

        // user credentials by auth mode
        $user_credentials = new ilCheckboxGroupInputGUI(
            $this->lng->txt('ecs_import_user_credentials_by_auth_mode'),
            'outgoing_auth_modes'
        );
        $user_credentials->setInfo($this->lng->txt('ecs_import_user_credentials_by_auth_mode_info'));
        $user_credentials->setValue($this->getParticipant()->getOutgoingAuthModes());
        $import->addSubItem($user_credentials);
        foreach ($this->parseAvailableAuthModes() as $option_name => $option_text)
        {
            $option = new ilCheckboxOption(
                $this->lng->txt('ecs_import_auth_mode') . ' ' . $option_text,
                $option_name
            );
            $user_credentials->addOption($option);
            $username_placeholder = new ilTextInputGUI(
                $this->getLang()->txt('ecs_outgoing_user_credentials'),
                'username_placeholder_' . $option_name
            );
            $username_placeholder->setRequired(false);
            $username_placeholder->setInfo($this->lng->txt('ecs_outgoing_user_credentials_info'));
            $username_placeholder->setValue(
                $this->getParticipant()->getOutgoingUsernamePlaceholderByAuthMode(
                    $option_name
                )
            );
            $option->addSubItem($username_placeholder);
        }
        $option = new ilCheckboxOption(
            $this->lng->txt('ecs_import_auth_type_default'),
            'default'
        );
        $user_credentials->addOption($option);

        // import types
        $imp_types = new ilCheckboxGroupInputGUI($this->getLang()->txt('ecs_import_types'), 'import_types');
        $imp_types->setValue($this->getParticipant()->getImportTypes());
        
        
        include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
        foreach (ilECSUtils::getPossibleRemoteTypes(true) as $type => $trans) {
            $imp_types->addOption(new ilCheckboxOption($trans, $type));
        }
        $import->addSubItem($imp_types);

        $form->addCommandButton('saveSettings', $this->getLang()->txt('save'));
        $form->addCommandButton('abort', $this->getLang()->txt('cancel'));
        return $form;
    }

    protected function parseAvailableAuthModes($a_mode_incoming = true) : array
    {
        $options = [];
        if ($this->isShibbolethActive()) {
            $options['shibboleth'] = $this->getLang()->txt('auth_shib');
        }
        foreach (ilLDAPServer::getServerIds() as $server_id) {
            $server = ilLDAPServer::getInstanceByServerId($server_id);
            $options['ldap_' . $server->getServerId()] = $server->getName();
        }
        return $options;
    }

    protected function isShibbolethActive() : bool
    {
        return (bool) $this->global_settings->get('shib_active', '0');
    }

    
    /**
     * Set tabs
     */
    protected function setTabs()
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getParentReturn($this)
        );
    }

    /**
     * Init settings
     *
     * @access protected
     */
    protected function initSettings()
    {
        include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
        $this->settings = ilECSSetting::getInstanceByServerId($this->getServerId());
    }
    
    /**
     * init participant
     */
    protected function initParticipant()
    {
        include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
        $this->participant = new ilECSParticipantSetting($this->getServerId(), $this->getMid());
    }
}
