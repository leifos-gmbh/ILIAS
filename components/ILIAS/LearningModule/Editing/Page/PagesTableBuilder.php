<?php

declare(strict_types=1);

namespace ILIAS\Modules\LearningModule\Editing\Page;

use ILIAS\LearningModule\InternalDomainService;
use ILIAS\LearningModule\InternalGUIService;
use ILIAS\Repository\Table\TableAdapterGUI;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\RetrievalInterface;
use ilUtil;

class PagesTableBuilder extends CommonTableBuilder
{
    protected array $page_layouts;

    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected string $title,
        protected int $lm_id,
        object $parent_gui,
        string $parent_cmd
    ) {
        $this->page_layouts = \ilPageLayout::activeLayouts(
            \ilPageLayout::MODULE_LM
        );
        $this->title = $this->title;
        parent::__construct($parent_gui, $parent_cmd);
    }
    protected function getId(): string
    {
        return "pages";
    }

    protected function getTitle(): string
    {
        return $this->title;
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return new PagesRetrieval(
            $this->lm_id,
            $this->parent_gui
        );
    }

    protected function transformRow(array $data_row): array
    {
        $lng = $this->domain->lng();
        $f = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();

        $img_sc = $data_row["scheduled"]
            ? "_sc"
            : "";

        if (!$data_row["active"]) {
            $img = "standard/icon_pg_d" . $img_sc . ".svg";
            $alt = $lng->txt("cont_page_deactivated");
        } else {
            if ($data_row["deactivated_elements"]) {
                $img = "standard/icon_pg_del" . $img_sc . ".svg";
                $alt = $lng->txt("cont_page_deactivated_elements");
            } else {
                $img = "standard/icon_pg" . $img_sc . ".svg";
                $alt = $lng->txt("pg");
            }
        }

        $ctrl->setParameterByClass(\ilLMPageObjectGUI::class, "obj_id", $data_row["obj_id"]);
        $target = $ctrl->getLinkTargetByClass([
            \ilObjLearningModuleGUI::class,
            \ilLMPageObjectGUI::class
        ], "edit");

        $title = $f->link()->standard($data_row["title"], $target);


        $usage = $data_row["context_path"];
        if ($data_row["is_header"]) {
            $usage .= " <strong>(" . $lng->txt("cont_header") . ")</strong>";
        }
        if ($data_row["is_footer"]) {
            $usage .= " <strong>(" . $lng->txt("cont_footer") . ")</strong>";
        }

        $res = [
            "id" => $data_row["obj_id"],
            "type" => $this->gui->ui()->factory()->symbol()->icon()->custom(ilUtil::getImagePath($img), "active"),
            "title" => $title,
            "cont_usage" => $usage
        ];

        if ($data_row["layout"] !== "") {
            $res["cont_layout"] = $lng->txt("cont_layout_" . $data_row["layout"]);
        }

        return $res;
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $user = $this->domain->user();
        $transl = $this->gui->editing()->request()->getTranslation();
        $table = $table
            ->iconColumn("type", $lng->txt("type"))
            ->linkColumn("title", $lng->txt("title"))
            ->textColumn("cont_usage", $lng->txt("cont_usage"));

        $lm = \ilObjectFactory::getInstanceByObjId($this->lm_id);
        if ($lm->getLayoutPerPage()) {
            $table = $table->textColumn("cont_layout", $lng->txt("cont_layout"));
        }

        if (!in_array($transl, ["-", ""])) {
            $table = $table->textColumn("trans_title", $lng->txt("title") .
                " (" . $lng->txt("meta_l_" . $transl) . ")");
        }

        $table = $table->multiAction(
            "activatePages",
            $lng->txt("cont_de_activate")
        )->multiAction(
            "copyPage",
            $lng->txt("copyPage")
        )->multiAction(
            "movePage",
            $lng->txt("movePage")
        )->multiAction(
            "cutItems",
            $lng->txt("cut")
        )->multiAction(
            "delete",
            $lng->txt("delete")
        )->multiAction(
            "selectHeader",
            $lng->txt("selectHeader")
        )->multiAction(
            "selectFooter",
            $lng->txt("selectFooter")
        );

        if ($lm->getLayoutPerPage()) {
            $table = $table->multiAction(
                "setPageLayout",
                $lng->txt("cont_set_layout")
            );
        }

        return $table;
    }

}