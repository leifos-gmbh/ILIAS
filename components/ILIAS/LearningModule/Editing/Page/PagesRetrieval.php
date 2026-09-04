<?php

declare(strict_types=1);

namespace ILIAS\Modules\LearningModule\Editing\Page;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

class PagesRetrieval implements RetrievalInterface
{
    public function __construct(
        protected int $lm_id,
        protected object $parent_gui
    ) {
    }
    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = \ilLMPageObject::getPageList($this->lm_id);
        $lm_set = new \ilSetting("lm");
        $lm = \ilObjectFactory::getInstanceByObjId($this->lm_id);
        $time_scheduled = (bool) $lm_set->get("time_scheduled_page_activation");
        $header_page = $lm->getHeaderPage();
        $footer_page = $lm->getFooterPage();

        foreach ($data as $row) {
            $row_obj_id = (int) $row["obj_id"];
            $active = \ilLMPage::_lookupActive(
                $row_obj_id,
                "lm",
                $time_scheduled
            );

            $scheduled = ($time_scheduled &&
                \ilLMPage::_isScheduledActivation($row_obj_id, "lm"));

            $deactivated_elements = false;
            if ($active) {
                $deactivated_elements = (\ilLMPage::_lookupContainsDeactivatedElements(
                    $row_obj_id,
                    "lm"
                ));
            }

            $path_str = "";
            if ($lm->lm_tree->isInTree($row_obj_id)) {
                $path_str = $this->parent_gui->getContextPath($row_obj_id);
            } else {
                $path_str = "---";
            }

            $row["id"] = $row_obj_id;
            $row["active"] = $active;
            $row["scheduled"] = $scheduled;
            $row["deactivated_elements"] = $deactivated_elements;
            $row["context_path"] = $path_str;
            $row["is_header"] = ($row_obj_id == $header_page);
            $row["is_footer"] = ($row_obj_id == $footer_page);
            $row["layout"] = \ilLMObject::lookupLayout($row_obj_id);

            yield $row;
        }
    }


    public function count(array $filter = [], array $parameters = []): int
    {
        return count(\ilLMPageObject::getPageList($this->lm_id));
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }
}