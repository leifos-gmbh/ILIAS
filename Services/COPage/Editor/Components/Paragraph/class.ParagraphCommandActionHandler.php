<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Paragraph;

use ILIAS\DI\Exceptions\Exception;
use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ParagraphCommandActionHandler implements Server\CommandActionHandler
{
    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilPageObjectGUI
     */
    protected $page_gui;

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var Server\UIWrapper
     */
    protected $ui_wrapper;

    function __construct(\ilPageObjectGUI $page_gui)
    {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->page_gui = $page_gui;
        $this->user = $DIC->user();

        $this->ui_wrapper = new Server\UIWrapper($this->ui, $this->lng);
    }

    /**
     * @param $query
     * @param $body
     * @return Server\Response
     */
    public function handle($query, $body) : Server\Response
    {
        switch ($body["action"]) {
            case "insert":
                return $this->insertCommand($body);
                break;

            case "update":
                return $this->updateCommand($body);
                break;

            default:
                throw new Exception("Unknown action " . $body["action"]);
                break;
        }
    }

    /**
     * All command
     * @param $body
     * @return Server\Response
     */
    protected function insertCommand($body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $pcid = ":".$body["data"]["pcid"];
        $insert_id = "pg:";
        if (!in_array($body["data"]["after_pcid"], ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$body["data"]["after_pcid"]]);
            $insert_id = $hier_ids[$body["data"]["after_pcid"]].":".$body["data"]["after_pcid"];
        }

        $content = "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $body["characteristic"] . "'>" . $body["data"]["content"] . "</div>";

        $this->content_obj = new \ilPCParagraph($page);
        $this->updated = $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid),
            $insert_id
        );


        $data = new \stdClass();
        $data->renderedContent = "Test the rendered content";
        return new Server\Response($data);
    }

    /**
     * All command
     * @param $body
     * @return Server\Response
     */
    protected function updateCommand($body) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $hier_ids = $page->getHierIdsForPCIds([$body["data"]["pcid"]]);
        $pcid = $hier_ids[$body["data"]["pcid"]].":".$body["data"]["pcid"];

        $content = "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $body["data"]["characteristic"] . "'>" . $body["data"]["content"] . "</div>";

        $this->content_obj = new \ilPCParagraph($page);

        $this->updated = $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid)
        );


        $data = new \stdClass();
        $data->renderedContent = "Test the rendered content";
        return new Server\Response($data);
    }

}