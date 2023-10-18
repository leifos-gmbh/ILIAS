<?php

namespace ImportStatus;

use ImportStatus\I\Content\ilFactoryInterface as ilImportStatusContentFactoryInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ImportStatus\Content\ilFactory as ilImportStatusContentFactory;
use ImportStatus\ilHandlerCollection as ilImportStatusHandlerCollection;
use ImportStatus\ilHandler as ilImportStatusHandler;

class ilFactory implements ilImportStatusFactoryInterface
{
    public function content(): ilImportStatusContentFactoryInterface
    {
        return new ilImportStatusContentFactory();
    }

    public function handler(): ilImportStatusHandlerInterface
    {
        return new ilImportStatusHandler();
    }

    public function handlerCollection(): ilImportStatusHandlerCollectionInterface
    {
        return new ilImportStatusHandlerCollection();
    }
}
