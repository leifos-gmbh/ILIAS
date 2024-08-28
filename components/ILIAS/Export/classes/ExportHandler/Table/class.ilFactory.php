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

namespace ILIAS\Export\ExportHandler\Table;

use ilCtrl;
use ILIAS\DI\UIServices as ilUIServices;
use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\DataRetrieval\ilHandlerInterface as ilExportHandlerTableDataRetrievalFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\ilFactoryInterface as ilExportHandlerTableFactoryInterface;
use ILIAS\Export\ExportHandler\I\Table\ilHandlerInterface as ilExportHandlerTableInterface;
use ILIAS\Export\ExportHandler\I\Table\RowId\ilFactoryInterface as ilExportHandlerTableRowIdFactoryInterface;
use ILIAS\Export\ExportHandler\Table\DataRetrieval\ilHandler as ilExportHandlerTableDataRetrievalFactory;
use ILIAS\Export\ExportHandler\Table\ilHandler as ilExportHandlerTable;
use ILIAS\Export\ExportHandler\Table\RowId\ilFactory as ilExportHandlerTableRowIdFactory;
use ILIAS\HTTP\Services as ilHTTPServices;
use ILIAS\Refinery\Factory as ilRefineryFactory;
use ilLanguage;
use ilObjUser;

class ilFactory implements ilExportHandlerTableFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilUIServices $ui_services;
    protected ilHTTPServices $http_services;
    protected ilRefineryFactory $refinery;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilUIServices $ui_services,
        ilHTTPServices $http_services,
        ilRefineryFactory $refinery,
        ilObjUser $user,
        ilLanguage $lng,
        ilCtrl $ctrl
    ) {
        $this->export_handler = $export_handler;
        $this->ui_services = $ui_services;
        $this->http_services = $http_services;
        $this->refinery = $refinery;
        $this->user = $user;
        $this->lng = $lng;
        $this->ctrl = $ctrl;
    }

    public function handler(): ilExportHandlerTableInterface
    {
        return new ilExportHandlerTable(
            $this->ui_services,
            $this->http_services,
            $this->refinery,
            $this->user,
            $this->lng,
            $this->ctrl,
            $this->export_handler
        );
    }

    public function rowId(): ilExportHandlerTableRowIdFactoryInterface
    {
        return new ilExportHandlerTableRowIdFactory($this->export_handler);
    }

    public function dataRetrieval(): ilExportHandlerTableDataRetrievalFactoryInterface
    {
        return new ilExportHandlerTableDataRetrievalFactory(
            $this->ui_services,
            $this->export_handler
        );
    }
}
