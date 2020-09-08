<?php

/**
 * Class ilAdvancedMDRecordTranslationGUI
 * @ilCtrl_isCalledBy ilAdvancedMDRecordTranslationGUI: ilAdvancedMDSettingsGUI
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordTranslationGUI extends ilAdvancedMDTranslationGUI
{

    /**
     * @inheritDoc
     */
    protected function translations()
    {
        $this->setTabs(self::CMD_DEFAULT);
        $this->tpl->setContent('Hallo');
    }
}