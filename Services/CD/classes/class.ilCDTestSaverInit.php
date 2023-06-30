<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Init/classes/class.ilInitialisation.php");

/**
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup
 */
class ilCDTestSaverInit extends ilInitialisation
{
    /**
     * Init
     */
    public function init()
    {
        $GLOBALS["DIC"] = new \ILIAS\DI\Container();
        $GLOBALS["DIC"]["ilLoggerFactory"] = function ($c) {
            return ilLoggerFactory::getInstance();
        };

        self::initCore();
        self::initHTTPServices($GLOBALS["DIC"]);
        self::initClient();

        //self::initSession();
        //self::initUser();
        //self::resumeUserSession();


        /*$this->initIliasIniFile();
        $this->determineClient();
        $this->initClientIniFile();
        $this->initDatabase();
        $this->initSettings();
        $this->buildHttpPath();*/
    }
}
