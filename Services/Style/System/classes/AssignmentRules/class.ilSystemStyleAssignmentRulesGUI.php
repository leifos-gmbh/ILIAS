<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilSystemStyleAssignmentRulesGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 */
class ilSystemStyleAssignmentRulesGUI
{
    /**
     * @var ILIAS\DI\Container
     */
    protected $DIC;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var bool
     */
    protected $read_only = true;


    /**
     * ilSystemStyleAssignmentRulesGUI constructor.
     */
    public function __construct(bool $read_only)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();

        $this->ref_id = (int) ($this->request->getQueryParams()['ref_id'] ?? 0);
        $this->setReadOnly($read_only);
    }

    /**
     * @return bool
     */
    public function isReadOnly() : bool
    {
        return $this->read_only;
    }

    /**
     * @param bool $read_only
     */
    public function setReadOnly(bool $read_only) : void
    {
        $this->read_only = $read_only;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            default:
                $this->listAssignmentRules();
                return;
        }

    }

    protected function listAssignmentRules()
    {
        $this->tpl->setContent('Hallo');
    }


}