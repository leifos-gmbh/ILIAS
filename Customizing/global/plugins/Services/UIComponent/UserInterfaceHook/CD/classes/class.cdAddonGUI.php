<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Addon GUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdAddonGUI
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_pl)
    {
        global $ilCtrl;
        $this->pl = $a_pl;
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl;

        $cmd = $ilCtrl->getCmd("showAddon");
        $this->$cmd();
    }

    /**
     * Show addon
     *
     * @param
     * @return
     */
    public function showAddon()
    {
        global $tpl, $ilCtrl, $ilUser;
        
        $atpl = $this->pl->getTemplate("tpl.addon.html");
        $atpl->setVariable("INFO", $this->pl->txt("addon_info"));
        $atpl->setVariable("ACTION", "http://embed.language-academy.org/?loginregister");
        $atpl->setVariable("CMD", "redirectAddon");
        $atpl->setVariable("CMD_TXT", $this->pl->txt("addon_start"));
        $atpl->setVariable("PW_ADDON", $ilUser->getPasswordAddon());
        $atpl->setVariable("USER_EMAIL", $ilUser->getEmail());
        $tpl->setContent($atpl->get());
    }
}
