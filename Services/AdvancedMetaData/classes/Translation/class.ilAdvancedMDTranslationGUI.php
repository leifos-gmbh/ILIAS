<?php

use Psr\Http\Message\RequestInterface;

abstract class ilAdvancedMDTranslationGUI
{
    /**
     * Default command
     */
    protected const CMD_DEFAULT = 'translations';
    protected const CMD_ADD_TRANSLATION = 'addTranslations';

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilLanguage
     */
    protected $language;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var ilAdvancedMDRecord
     */
    protected $record;

    /**
     * ilAdvancedMDTranslationGUI constructor.
     * @param ilAdvancedMDRecord $record
     */
    public function __construct(ilAdvancedMDRecord $record)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->logger = $DIC->logger()->amet();
        $this->toolbar = $DIC->toolbar();
        $this->language = $DIC->language();
        $this->request = $DIC->http()->request();
        $this->record = $record;
    }

    /**
     * Execute command and save parameter record id
     */
    public function executeCommand()
    {
        $this->logger->info(strtolower(get_class($this)));
        $this->ctrl->setParameterByClass(
            strtolower(get_class($this)),
            'record_id',
            $this->record->getRecordId()
        );

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);
        switch ($next_class) {
            default:
                $this->$cmd();
        }
    }

    /**
     * @param string $active_tab
     */
    protected function setTabs(string $active_tab)
    {
        $this->tabs->activateTab($active_tab);
    }

    /**
     * @return void
     */
    abstract protected function translations();

}