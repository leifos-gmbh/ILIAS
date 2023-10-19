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

namespace ImportHandler\File\XML;

use DOMDocument;
use ILIAS\DI\Exceptions\Exception;
use ilImportException;
use ImportHandler\File\ilHandler as ilFileHandler;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactory;
use ImportStatus\StatusType;
use SplFileInfo;

class ilHandler extends ilFileHandler implements ilXMLFileHandlerInterface
{
    protected ilImportStatusFactory $status;
    protected bool $strict_dom_doc_error_checking_enabled;
    protected int $options;

    public function __construct(
        ilImportStatusFactory $status
    ) {
        $this->strict_dom_doc_error_checking_enabled = false;
        $this->options = 0;
        $this->status = $status;
    }

    public function withFileInfo(SplFileInfo $file_info): ilHandler
    {
        $clone = clone $this;
        $clone->xml_file_info = $file_info;
        return $clone;
    }

    public function withDOMDocumentStrictErrorChecking(bool $enabled): ilXMLFileHandlerInterface
    {
        $clone = clone $this;
        $clone->strict_dom_doc_error_checking_enabled = $enabled;
        return $clone;
    }

    public function withOptions(int $options): ilXMLFileHandlerInterface
    {
        $clone = clone $this;
        $clone->options = $options;
        return $clone;
    }

    public function loadDomDocument(): DOMDocument
    {
        $old_val = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->strictErrorChecking = $this->strict_dom_doc_error_checking_enabled;
        $doc->load($this->getFilePath());
        $status_collection = $this->status->handlerCollection();
        foreach (libxml_get_errors() as $error) {
            $status_collection = $status_collection->withAddedStatus(
                $this->status->handler()->withType(StatusType::FAILED)->withContent(
                    $this->status->content()->builder()->string()->withString(
                        "Error loading dom document:" .
                        "<br>  XML: " . $this->getSubPathToDirBeginningAtPathEnd('temp') .
                        "<br>ERROR: " . $error->message
                    )
                )
            );
        }
        if ($status_collection->hasStatusType(StatusType::FAILED)) {
            throw new ilImportException($status_collection
                ->withNumberingEnabled(true)
                ->toString(StatusType::FAILED));
        }
        libxml_clear_errors();
        libxml_use_internal_errors($old_val);
        return $doc;
    }
}
