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

use ILIAS\BookingManager\InternalDomainService;
use ILIAS\BookingManager\InternalGUIService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookBulkCreationGUI
{
    protected ilObjBookingPool $pool;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui,
        ilObjBookingPool $pool
    ) {
        $this->pool = $pool;
        $this->domain = $domain;
        $this->gui = $gui;
        $lng = $domain->lng();
        $lng->loadLanguageModule("book");
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("showCreationForm");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "showCreationForm",
                    "showConfirmationScreen"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function modifyToolbar(ilToolbarGUI $toolbar) : void
    {
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $button = $this->gui->modal($lng->txt("book_bulk_creation"))
                            ->getAsyncTriggerButtonComponents(
                                $lng->txt("book_bulk_creation"),
                                $ctrl->getLinkTarget($this, "showCreationForm", "", true)
                            );
    }

    protected function showCreationForm() : void
    {
        $lng = $this->domain->lng();
        $this->gui->modal($lng->txt("book_bulk_creation"))
                  ->form($this->getCreationForm())
                  ->send();
    }

    protected function getCreationForm() : \ILIAS\Repository\Form\FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $schedule_manager = $this->domain->schedules($this->pool->getId());
        return $this->gui->form(self::class, "showConfirmationScreen")
                         ->section("creation", $lng->txt("book_bulk_data"))
                         ->textarea(
                             "data",
                             $lng->txt("book_title_description_nr"),
                             $lng->txt("book_title_description_nr_info"),
                         )
                         ->select(
                             "schedule",
                             $lng->txt("book_schedule"),
                             $schedule_manager->getScheduleList()
                         );
    }

    protected function showConfirmationScreen() : void
    {
        $form = $this->getCreationForm();
        $lng = $this->domain->lng();
        if (!$form->isValid()) {
            $this->gui->modal($lng->txt("book_bulk_creation"))
                      ->form($form)
                      ->send();
        }

        $this->gui->modal($lng->txt("book_bulk_creation"))
                  ->legacy($this->renderConfirmation($form->getData("data")))
                  ->send();
    }

    protected function renderConfirmation(string $data) : string
    {
        return "Confirmation";
    }

}
