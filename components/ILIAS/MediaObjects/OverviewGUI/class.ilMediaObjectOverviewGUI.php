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

use ILIAS\MediaObjects\OverviewGUI\SubObjectRetrieval;
use ILIAS\MediaObjects\OverviewGUI\Table\Builder as TableBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\MetaData\Services\ServicesInterface as LOM;

class ilMediaObjectOverviewGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected UIFactory $ui_factory;
    protected StaticURL $static_url;
    protected DataFactory $data_factory;
    protected ilAccess $access;
    protected LOM $lom;

    public function __construct(
        protected SubObjectRetrieval $sub_object_retrieval
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->static_url = $DIC['static_url'];
        $this->data_factory = new DataFactory();
        $this->access = $DIC->access();
        $this->lom = $DIC->learningObjectMetadata();

        $this->lng->loadLanguageModule('mob');
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            default:
                $cmd = $this->ctrl->getCmd('show');
                $this->$cmd();
                break;
        }
    }

    protected function show(): void
    {
        $table_builder = $this->getTableBuilder('show');
        $table = $table_builder->getTable();

        $this->tpl->setContent($table->render());
    }

    protected function getTableBuilder(string $cmd): TableBuilder
    {
        return new TableBuilder(
            $this,
            $cmd,
            $this->sub_object_retrieval,
            $this->lng,
            $this->ui_factory,
            $this->static_url,
            $this->data_factory,
            $this->access,
            $this->lom
        );
    }
}
