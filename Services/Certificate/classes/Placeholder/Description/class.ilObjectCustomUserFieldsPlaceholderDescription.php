<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Marvin Barz <barz@leifos.de>
 */

class ilObjectCustomUserFieldsPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    const PLACEHOLDER_PRE_CHAR = '+';

    private $placeholder;

    /**
     * @param int $objectId
     */
    public function __construct($objectId)
    {
        $this->placeholder = array();

        $courseDefinedFields = ilCourseDefinedFieldDefinition::_getFields($objectId);

        foreach ($courseDefinedFields as $field) {
            $name = $field->getName();

            $placeholderText = self::PLACEHOLDER_PRE_CHAR . str_replace(' ', '_', ilStr::strToUpper($name));

            $this->placeholder[$placeholderText] = $name;
        }
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     *
     * @return array - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions()
    {
        return $this->placeholder;
    }

    /**
     * @return string - HTML that can used to be displayed in the GUI
     */
    public function createPlaceholderHtmlDescription()
    {
        $template = new ilTemplate(
            'tpl.common_desc.html',
            true,
            true,
            'Services/Certificate'
        );

        foreach ($this->getPlaceholderDescriptions() as $key => $field) {
            $template->setCurrentBlock('cert_field');
            $template->setVariable('PH', $key);
            $template->setVariable('PH_TXT', $field);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }
}
