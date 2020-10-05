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
     * @var ParagraphResponseFactory
     */
    protected $response_factory;

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

        $this->response_factory = new ParagraphResponseFactory();

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

            case "update.auto":
                return $this->autoUpdateCommand($body);
                break;

            case "insert.auto":
                return $this->autoInsertCommand($body);
                break;

            case "split":
                return $this->split($body);
                break;

            default:
                throw new Exception("Unknown action " . $body["action"]);
                break;
        }
    }

    /**
     * Insert command
     * @param $body
     * @return Server\Response
     */
    protected function insertCommand($body, $auto = false) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $pcid = ":" . $body["data"]["pcid"];
        $insert_id = "pg:";
        if (!in_array($body["data"]["after_pcid"], ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$body["data"]["after_pcid"]]);
            $insert_id = $hier_ids[$body["data"]["after_pcid"]] . ":" . $body["data"]["after_pcid"];
        }

        $content = "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $body["data"]["characteristic"] . "'>" . $body["data"]["content"] . "</div>";

        $this->content_obj = new \ilPCParagraph($page);
        $updated = $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid),
            $insert_id
        );

        return $this->response_factory->getResponseObject($this->page_gui, $updated, $body["data"]["pcid"]);
    }

    /**
     * Auto update
     * @param $body
     * @return Server\Response
     */
    protected function autoInsertCommand($body) : Server\Response
    {
        return $this->insertCommand($body, true);
    }

    /**
     * Update
     * @param $body
     * @return Server\Response
     */
    protected function updateCommand($body, $auto = false) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $hier_ids = $page->getHierIdsForPCIds([$body["data"]["pcid"]]);
        $pcid = $hier_ids[$body["data"]["pcid"]] . ":" . $body["data"]["pcid"];

        $content = "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $body["data"]["characteristic"] . "'>" . $body["data"]["content"] . "</div>";

        $this->content_obj = new \ilPCParagraph($page);

        $updated = $this->content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid)
        );

        return $this->response_factory->getResponseObject($this->page_gui, $updated, $body["data"]["pcid"]);
    }

    /**
     * Auto update
     * @param $body
     * @return Server\Response
     */
    protected function autoUpdateCommand($body) : Server\Response
    {
        return $this->updateCommand($body, true);
    }

    /**
     * Split command
     * @param $body
     * @return Server\Response
     */
    protected function split($body, $auto = false) : Server\Response
    {
        $page = $this->page_gui->getPageObject();

        $pcid = ":" . $body["data"]["pcid"];
        $insert_id = null;
        if ($body["data"]["insert_mode"]) {
            $insert_id = $this->getFullIdForPCId($page, $body["data"]["after_pcid"]);
        }

        $content = $this->getContentForSaving($pcid, $body["data"]["text"], $body["data"]["characteristic"]);

        $content_obj = new \ilPCParagraph($page);
        $updated = $content_obj->saveJS(
            $page,
            $content,
            \ilUtil::stripSlashes($body["data"]["characteristic"]),
            \ilUtil::stripSlashes($pcid),
            $insert_id
        );
        $current_after_id = $body["data"]["pcid"];
        $all_pc_ids[] = $current_after_id;

        foreach ($body["data"]["new_paragraphs"] as $p) {
            if ($updated === true) {
                $page->addHierIDs();
                $insert_id = $this->getFullIdForPCId($page, $current_after_id);
                $content = $this->getContentForSaving($p["pcid"], $p["model"]["text"], $p["model"]["characteristic"]);
                $content_obj = new \ilPCParagraph($page);
                $updated = $content_obj->saveJS(
                    $page,
                    $content,
                    \ilUtil::stripSlashes($p["model"]["characteristic"]),
                    ":".\ilUtil::stripSlashes($p["pcid"]),
                    $insert_id
                );
                $all_pc_ids[] = $p["pcid"];
                $current_after_id = $p["pcid"];
            }
        }

        return $this->response_factory->getResponseObjectMulti($this->page_gui, $updated, $all_pc_ids);
    }

    /**
     * Get full id for pc id
     * @param $page
     * @param $pc_id
     * @return string
     */
    protected function getFullIdForPCId($page, $pc_id)
    {
        $id = "pg:";
        if (!in_array($pc_id, ["", "pg"])) {
            $hier_ids = $page->getHierIdsForPCIds([$pc_id]);
            $id = $hier_ids[$pc_id] . ":" . $pc_id;
        }
        return $id;
    }

    /**
     * Get content for saving
     * @param
     * @return
     */
    protected function getContentForSaving($pcid, $content, $characteristic)
    {
        return "<div id='" .
            $pcid . "' class='ilc_text_block_" .
            $characteristic . "'>" . $content . "</div>";
    }

}