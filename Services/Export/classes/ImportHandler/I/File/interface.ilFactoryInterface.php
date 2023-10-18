<?php

namespace ImportHandler\I\File;

use ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ImportHandler\I\File\XML\ilFactoryInterface as ilXMLFileFactoryInterface;
use ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;

interface ilFactoryInterface
{
    public function xml(): ilXMLFileFactoryInterface;

    public function xsd(): ilXSDFileFactoryInterface;

    public function validation(): ilFileValidationFactoryInterface;
}
