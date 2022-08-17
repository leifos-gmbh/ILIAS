<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectSelectionListGUI
{
    protected \ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;
    protected string $form_action;

    public function __construct(
        int $pool_id
    ) {
        global $DIC;

        $this->ui = $DIC->ui();
        $this->pool_id = $pool_id;
        $this->lng = $DIC->language();
        $this->form_action = "#";
    }

    public function render() : string
    {
        $tpl = new \ilTemplate("tpl.obj_selection.html", true, true, "Modules/BookingManager/BookingProcess");

        foreach (\ilBookingObject::getObjectsForPool($this->pool_id) as $obj_id) {
            $obj = new \ilBookingObject($obj_id);
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ID", $obj->getId());
            $tpl->setVariable("TITLE", $obj->getTitle());
            $tpl->parseCurrentBlock();
        }

        $p = $this->ui->factory()->panel()->secondary()->legacy(
            $this->lng->txt("book_object_selection"),
            $this->ui->factory()->legacy($tpl->get())
        );
        return $this->ui->renderer()->render($p);
    }
}
