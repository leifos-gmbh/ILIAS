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

use ILIAS\Notes\Note;

/**
 * Comment GUI
 */
class ilCommentGUI extends ilNoteGUI
{
    protected int $note_type = Note::PUBLIC;

    public function __construct(
        $rep_obj_id = 0,
        int $obj_id = 0,
        string $obj_type = "",
        bool $include_subobjects = false,
        int $news_id = 0,
        bool $ajax = true,
        string $search_text = ""
    ) {
        parent::__construct(
            $rep_obj_id,
            $obj_id ,
            $obj_type,
            $include_subobjects,
            $news_id,
            $ajax,
            $search_text
        );
        $this->enablePrivateNotes(false);
        $this->enablePublicNotes(true);
    }

    public function getNotesHTML(): string
    {
        throw new ilException("Call to getNotesHTML is deprecated");
    }

    public function getCommentsHTML(): string
    {
        throw new ilException("Call to getCommentsHTML is deprecated");
    }

    protected function getNoEntriesText(bool $search) : string
    {
        if (!$search) {
            $mess_txt = $this->lng->txt("notes_no_comments");
        } else {
            $mess_txt = $this->lng->txt("notes_no_comments_found");
        }
        return $mess_txt;
    }

    protected function getItemGroupTitle(int $obj_id = 0): string
    {
        if (!$this->show_header) {
            return "";
        }
        return $this->lng->txt("notes_comments");
    }

    protected function getItemTitle(Note $note) : string
    {
        $avatar = ilObjUser::_getAvatar($note->getAuthor());
        return ilUserUtil::getNamePresentation($note->getAuthor(), false, false);
    }

    protected function addItemProperties(Note $note, array &$properties) : void
    {
        $creation_date = ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME));
        $properties[$this->lng->txt("create_date")] = $creation_date;
    }

    protected function getFormLabelKey() : string
    {
        return "comment";
    }

    protected function getDeleteText() : string
    {
        return $this->lng->txt("notes_delete_comment");
    }

    public function getHTML(): string
    {
        $this->gui->initJavascript();
        return $this->getCommentsWidget();
    }

    public function getListHTML(bool $a_init_form = true): string
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        if ($this->ajax) {
            $this->gui->initJavascript();
        }
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "notes_type", $this->note_type);

        $content = "";
        // #15948 - public enabled vs. comments_settings
        $active = true;
        // news items (in timeline) do not check, if the object has news activated!
        if (!is_array($this->rep_obj_id) && $this->news_id === 0) {

            // if common object settings are used, we check for activation
            if ($this->comments_settings) {
                $active = $this->manager->commentsActive($this->rep_obj_id);
            }
        }
        if ($active) {
            $content = $this->getNoteListHTML($a_init_form);
        }

        // Comments Settings
        if (!is_array($this->rep_obj_id) && $this->comments_settings && $ilUser->getId() !== ANONYMOUS_USER_ID) {
            $active = $this->manager->commentsActive($this->rep_obj_id);
            if ($active) {
                if ($this->news_id === 0) {
                    $content .= $this->renderComponents([$this->getShyButton(
                        "comments_settings",
                        $lng->txt("notes_deactivate_comments"),
                        "deactivateComments",
                        ""
                    )
                    ]);
                }
            } else {
                $content .= $this->renderComponents([$this->getShyButton(
                    "comments_settings",
                    $lng->txt("notes_activate_comments"),
                    "activateComments",
                    ""
                )
                ]);
                /*
                if ($this->ajax && !$comments_col) {
                    $ntpl->setVariable(
                        "COMMENTS_MESS",
                        ilUtil::getSystemMessageHTML($lng->txt("comments_feature_currently_not_activated_for_object"), "info")
                    );
                }*/
            }
        }
        return $this->renderContent($content);
    }


    protected function getCommentsWidget(): string
    {
        $f = $this->ui->factory();
        $r = $this->ui->renderer();
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $ctrl->setParameter($this, "news_id", $this->news_id);
        $hash = ilCommonActionDispatcherGUI::buildAjaxHash(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            null,
            ilObject::_lookupType($this->rep_obj_id),
            $this->rep_obj_id,
            $this->obj_type,
            $this->obj_id,
            $this->news_id
        );

        $context = $this->data->context(
            $this->rep_obj_id,
            $this->obj_id,
            $this->obj_type,
            $this->news_id
        );

        $cnt[$this->rep_obj_id][Note::PUBLIC] = $this->manager->getNrOfNotesForContext($context, Note::PUBLIC);
        $cnt = $cnt[$this->rep_obj_id][Note::PUBLIC] ?? 0;

        $tpl = new ilTemplate("tpl.note_widget_header.html", true, true, "Services/Notes");
        $widget_el_id = "notew_" . str_replace(";", "_", $hash);
        $ctrl->setParameter($this, "hash", $hash);
        $update_url = $ctrl->getLinkTarget($this, "updateWidget", "", true, false);
        $comps = array();
        if ($cnt > 0) {
            $c = $f->counter()->status((int) $cnt);
            $comps[] = $f->symbol()->glyph()->comment()->withCounter($c)->withAdditionalOnLoadCode(function ($id) use ($hash, $update_url, $widget_el_id) {
                return "$(\"#$id\").click(function(event) { " . self::getListCommentsJSCall($hash, "ilNotes.updateWidget(\"" . $widget_el_id . "\",\"" . $update_url . "\");") . "});";
            });
            $comps[] = $f->divider()->vertical();
            $tpl->setVariable("GLYPH", $r->render($comps));
            $tpl->setVariable("TXT_LATEST", $lng->txt("notes_latest_comment"));
        }

        $b = $f->button()->shy($lng->txt("notes_add_edit_comment"), "#")->withAdditionalOnLoadCode(function ($id) use ($hash, $update_url, $widget_el_id) {
            return "$(\"#$id\").click(function(event) { " . self::getListCommentsJSCall($hash, "ilNotes.updateWidget(\"" . $widget_el_id . "\",\"" . $update_url . "\");") . "});";
        });
        if ($ctrl->isAsynch()) {
            $tpl->setVariable("SHY_BUTTON", $r->renderAsync($b));
        } else {
            $tpl->setVariable("SHY_BUTTON", $r->render($b));
        }

        $this->widget_header = $tpl->get();

        $this->hide_new_form = true;
        $this->only_latest = true;
        $this->no_actions = true;
        $html = "<div id='" . $widget_el_id . "'>" . $this->getNoteListHTML() . "</div>";
        $ctrl->setParameter($this, "news_id", $this->requested_news_id);
        return $html;
    }

    protected function updateWidget(): void
    {
        echo $this->getCommentsWidget();
        exit;
    }

    protected function getListTitle() : string
    {
        return $this->lng->txt("notes_public_comments");
    }

    protected function getAddText() : string
    {
        return $this->lng->txt("note_add_comment");
    }

    protected function getDeletedMultipleText() : string
    {
        return $this->lng->txt("notes_comments_deleted");
    }

    protected function getDeletedSingleText() : string
    {
        return $this->lng->txt("notes_comment_deleted");
    }

}