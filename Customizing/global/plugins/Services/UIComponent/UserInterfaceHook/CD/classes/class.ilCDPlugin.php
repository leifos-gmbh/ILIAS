<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");
 
/**
 * User interface hook for hsu
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilCDPlugin extends ilUserInterfaceHookPlugin
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        global $DIC;
        $this->includeClass("MainMenuProvider.php");
        $this->provider_collection->setMainBarProvider(new \leifos\CD\MainMenuProvider($DIC, $this));
    }

    public function getPluginName()
    {
        return "CD";
    }
    
    /**
     * Modify course toolbar
     *
     * @param object $a_ctb toolbar object
     */
    public function modifyCourseToolbar($a_ctb)
    {
        $this->includeClass("class.cdCourseExtGUI.php");
        cdCourseExtGUI::modifyCourseToolbar($a_ctb, $this);
    }
}
