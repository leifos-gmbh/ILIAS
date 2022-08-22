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

use \ILIAS\Filesystem\Stream\Streams;

/**
 * Booking process ui class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingProcessGUI
{
    protected \ILIAS\HTTP\Services $http;
    protected \ILIAS\BookingManager\InternalGUIService $gui;
    protected array $raw_post_data;
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected ilObjBookingPool $pool;
    protected int $booking_object_id;
    protected int $user_id_to_book;
    protected int $user_id_assigner;
    protected string $seed;
    protected string $sseed;
    protected ilBookingHelpAdapter $help;
    protected int $context_obj_id;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilTabsGUI $tabs_gui;
    protected ilObjUser $user;
    protected int $book_obj_id;
    protected array $rsv_ids = [];

    public function __construct(
        ilObjBookingPool $pool,
        int $booking_object_id,
        ilBookingHelpAdapter $help,
        string $seed = "",
        string $sseed = "",
        int $context_obj_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs_gui = $DIC->tabs();
        $this->user = $DIC->user();
        $this->help = $help;
        $this->http = $DIC->http();

        $this->context_obj_id = $context_obj_id;

        $this->book_obj_id = $booking_object_id;

        $this->pool = $pool;

        $this->seed = $seed;
        $this->sseed = $sseed;
        $this->book_request = $DIC->bookingManager()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->gui = $DIC->bookingManager()
            ->internal()
            ->gui();

        $this->rsv_ids = $this->book_request->getReservationIdsFromString();

        $this->raw_post_data = $DIC->http()->request()->getParsedBody();

        $this->user_id_assigner = $this->user->getId();
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        } else {
            $this->user_id_to_book = $this->user_id_assigner; // by default user books his own booking objects.
        }
        $this->ctrl->saveParameter($this, ["bkusr", "returnCmd"]);
        $this->ctrl->setParameter($this, "seed", $this->seed);
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");
        switch ($next_class) {
            default:
                if (in_array($cmd, array("book", "back", "week",
                    "assignParticipants",
                    "bookMultipleParticipants",
                    "saveMultipleBookings",
                    "confirmedBooking",
                    "confirmBookingNumbers",
                    "confirmedBookingNumbers",
                    "confirmedBookingNumbers2",
                    "displayPostInfo",
                    "deliverPostFile"
            ))) {
                    $this->$cmd();
                }
        }
    }

    // Back to parent
    protected function back() : void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function setHelpId(string $a_id) : void
    {
        $this->help->setHelpId($a_id);
    }

    protected function checkPermissionBool(string $a_perm) : bool
    {
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess($a_perm, "", $this->pool->getRefId())) {
            return false;
        }
        return true;
    }

    protected function checkPermission(string $a_perm) : void
    {
        if (!$this->checkPermissionBool($a_perm)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
            $this->back();
        }
    }

    //
    // Step 0 / week view
    //

    /**
     * First step in booking process
     */
    public function week() : void // ok
    {
        $tpl = $this->tpl;

        //$this->tabs_gui->clearTargets();
        //$this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'back'));

        $this->setHelpId("week");
        $this->ctrl->setParameter($this, 'returnCmd', "week");

        if ($this->user_id_to_book !== $this->user_id_assigner) {
            $this->ctrl->setParameter($this, 'bkusr', $this->user_id_to_book);
        }
        $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());

        $week_gui = new \ILIAS\BookingManager\BookingProcess\WeekGUI(
            $this,
            "week",
            ilBookingObject::getObjectsForPool($this->pool->getId()),
            $this->seed ?? $this->sseed,
            $user_settings->getWeekStart()
        );
        $tpl->setContent($week_gui->getHTML());

        $bar = $this->gui->toolbar();
        $list_link = $this->ctrl->getLinkTargetByClass("ilObjBookingPoolGUI", "render");
        $week_link = $this->ctrl->getLinkTargetByClass("ilBookingProcessGUI", "week");
        $mode_control = $this->gui->ui()->factory()->viewControl()->mode([
            $this->lng->txt("book_list") => $list_link,
            $this->lng->txt("book_week") => $week_link
        ], $this->lng->txt("book_view"))->withActive($this->lng->txt("book_week"));
        $bar->addComponent($mode_control);

        $list_gui = new \ILIAS\BookingManager\BookingProcess\ObjectSelectionListGUI($this->pool->getId());
        $tpl->setRightContent($list_gui->render());
    }

    //
    // Step 1
    //


    /**
     * First step in booking process
     */
    public function book() : void // ok
    {
        $tpl = $this->tpl;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'back'));

        $this->setHelpId("book");

        $obj = new ilBookingObject($this->book_obj_id);

        $this->lng->loadLanguageModule("dateplaner");
        $this->ctrl->setParameter($this, 'object_id', $obj->getId());
        $this->ctrl->setParameter($this, 'returnCmd', "book");

        if ($this->user_id_to_book !== $this->user_id_assigner) {
            $this->ctrl->setParameter($this, 'bkusr', $this->user_id_to_book);
        }

        $user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());

        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $week_gui = new \ILIAS\BookingManager\BookingProcess\WeekGUI(
                $this,
                "book",
                [$obj->getId()],
                $this->seed ?? $this->sseed,
                $user_settings->getWeekStart()
            );
            $tpl->setContent($week_gui->getHTML());
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setHeaderText($this->lng->txt("book_confirm_booking_no_schedule"));

            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setCancel($this->lng->txt("cancel"), "back");
            $cgui->setConfirm($this->lng->txt("confirm"), "confirmedBooking");

            $cgui->addItem("object_id", $obj->getId(), $obj->getTitle());

            $tpl->setContent($cgui->getHTML());
        }
    }



    //
    // Step 1a)
    //
    // Assign multiple participants (starting from participants screen) (no, from object screen!)
    //

    // Table to assign participants to an object.
    public function assignParticipants() : void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'back'));

        $table = new ilBookingAssignParticipantsTableGUI($this, 'assignParticipants', $this->pool->getRefId(), $this->pool->getId(), $this->book_obj_id);

        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Create reservations for a bunch of booking pool participants.
     */
    public function bookMultipleParticipants() : void
    {
        $participants = $this->book_request->getParticipants();
        if (count($participants) === 0) {
            $this->back();
            return;
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this, 'assignparticipants'));

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));

        //add user list as items.
        foreach ($participants as $id) {
            $name = ilObjUser::_lookupFullname($id);
            $conf->addItem("participants[]", $id, $name);
        }

        $available = ilBookingReservation::numAvailableFromObjectNoSchedule($this->book_obj_id);
        if (count($participants) > $available) {
            $obj = new ilBookingObject($this->book_obj_id);
            $conf->setHeaderText(
                sprintf(
                    $this->lng->txt('book_limit_objects_available'),
                    count($participants),
                    $obj->getTitle(),
                    $available
                )
            );
        } else {
            $conf->setHeaderText($this->lng->txt('book_confirm_booking_no_schedule'));
            $conf->addHiddenItem("object_id", $this->book_obj_id);
            $conf->setConfirm($this->lng->txt("assign"), "saveMultipleBookings");
        }

        $conf->setCancel($this->lng->txt("cancel"), 'redirectToList');
        $this->tpl->setContent($conf->getHTML());
    }

    public function redirectToList() : void
    {
        $this->ctrl->redirect($this, 'assignParticipants');
    }

    /**
     * Save multiple users reservations for one booking pool object.
     * @todo check if object/user exist in the DB,
     */
    public function saveMultipleBookings() : void
    {
        $participants = $this->book_request->getParticipants();
        $object_id = $this->book_request->getObjectId();
        if (count($participants) > 0 && $object_id > 0) {
            $this->book_obj_id = $object_id;
        } else {
            $this->back();
        }
        $rsv_ids = array();
        foreach ($participants as $id) {
            $this->user_id_to_book = $id;
            $rsv_ids[] = $this->processBooking($this->book_obj_id);
        }

        if (count($rsv_ids)) {
            $this->tpl->setOnScreenMessage('success', "booking_multiple_succesfully");
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed_overbooked'), true);
        }
        $this->back();
    }


    //
    // Step 2: Confirmation
    //

    // Book object - either of type or specific - for given dates
    public function confirmedBooking() : bool
    {
        $success = false;
        $rsv_ids = array();

        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            if ($this->book_obj_id > 0) {
                $object_id = $this->book_obj_id;
                if ($object_id) {
                    if (ilBookingReservation::isObjectAvailableNoSchedule($object_id) &&
                        count(ilBookingReservation::getObjectReservationForUser($object_id, $this->user_id_to_book)) === 0) { // #18304
                        $rsv_ids[] = $this->processBooking($object_id);
                        $success = $object_id;
                    } else {
                        // #11852
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed_overbooked'), true);
                        $this->ctrl->redirect($this, 'back');
                    }
                }
            }
        } else {
            $date = $this->book_request->getDate();

            // single object reservation(s)
            if ($this->book_obj_id > 0) {
                $confirm = array();
                $object_id = $this->book_obj_id;
                $group_id = null;

                $nr = ilBookingObject::getNrOfItemsForObjects(array($object_id));
                // needed for recurrence
                $f = new ilBookingReservationDBRepositoryFactory();
                $repo = $f->getRepo();
                $group_id = $repo->getNewGroupId();

                $fromto = explode('_', $date);
                $from = (int) $fromto[0];
                $to = (int) $fromto[1] - 1;

                $this->confirmBookingNumbers($from, $to, $group_id);
                return false;
            }
        }

        if ($success) {
            $this->saveParticipant();
            $this->handleBookingSuccess($success, $rsv_ids);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed'), true);
            $this->ctrl->redirect($this, 'book');
        }
        return true;
    }

    protected function getAvailableNr(
        int $object_id,
        int $from,
        int $to
    ) : int {
        $counter = ilBookingReservation::getAvailableObject(array($object_id), $from, $to, false, true);
        return (int) $counter[$object_id];
    }

    /**
     * save booking participant
     */
    protected function saveParticipant() : void
    {
        $participant = new ilBookingParticipant($this->user_id_to_book, $this->pool->getId());
    }

    /**
     * Book object for date
     * @return int reservation id
     * @throws ilDateTimeException
     */
    public function processBooking(
        int $a_object_id,
        int $a_from = null,
        int $a_to = null,
        int $a_group_id = null
    ) : int {
        // #11995
        $this->checkPermission('read');

        $reservation = new ilBookingReservation();
        $reservation->setObjectId($a_object_id);
        $reservation->setUserId($this->user_id_to_book);
        $reservation->setAssignerId($this->user_id_assigner);
        $reservation->setFrom((int) $a_from);
        $reservation->setTo((int) $a_to);
        $reservation->setGroupId((int) $a_group_id);
        $reservation->setContextObjId($this->context_obj_id);
        $reservation->save();

        if ($a_from) {
            $this->lng->loadLanguageModule('dateplaner');
            $def_cat = ilCalendarUtil::initDefaultCalendarByType(ilCalendarCategory::TYPE_BOOK, $this->user_id_to_book, $this->lng->txt('cal_ch_personal_book'), true);

            $object = new ilBookingObject($a_object_id);

            $entry = new ilCalendarEntry();
            $entry->setStart(new ilDateTime($a_from, IL_CAL_UNIX));
            $entry->setEnd(new ilDateTime($a_to, IL_CAL_UNIX));
            $entry->setTitle($this->lng->txt('book_cal_entry') . ' ' . $object->getTitle());
            $entry->setContextId($reservation->getId());
            $entry->save();

            $assignment = new ilCalendarCategoryAssignments($entry->getEntryId());
            if ($def_cat !== null) {
                $assignment->addAssignment($def_cat->getCategoryID());
            }
        }

        return $reservation->getId();
    }

    //
    // Confirm booking numbers
    //

    public function confirmBookingNumbers(
        int $from,
        int $to,
        int $group_id,
        \ILIAS\Repository\Form\FormAdapterGUI $form = null
    ) : void {
        $tpl = $this->tpl;

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'back'));

        if (!$form) {
            $form = $this->initBookingNumbersForm2($from, $to, $group_id);
        }
        $this->gui->modal($this->getBookgingObjectTitle())
            ->form($form)
            ->send();
    }

    protected function getBookgingObjectTitle() : string
    {
        return (new ilBookingObject($this->book_obj_id))->getTitle();
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function initBookingNumbersForm(
        array $a_objects_counter,
        int $a_group_id,
        bool $a_reload = false
    ) : ilPropertyFormGUI {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "confirmedBookingNumbers", "", $this->ctrl->isAsynch()));
        $form->setTitle($this->lng->txt("book_confirm_booking_schedule_number_of_objects"));
        $form->setDescription($this->lng->txt("book_confirm_booking_schedule_number_of_objects_info"));

        $section = false;
        $min_date = null;
        foreach ($a_objects_counter as $id => $counter) {
            $id = explode("_", $id);
            $book_id = $id[0] . "_" . $id[1] . "_" . $id[2] . "_" . $counter;

            $obj = new ilBookingObject($id[0]);

            if (!$section) {
                $section = new ilFormSectionHeaderGUI();
                $section->setTitle($obj->getTitle());
                $form->addItem($section);

                $section = true;
            }

            $period = /* $this->lng->txt("book_period").": ". */
                ilDatePresentation::formatPeriod(
                    new ilDateTime($id[1], IL_CAL_UNIX),
                    new ilDateTime($id[2], IL_CAL_UNIX)
                );

            $nr_field = new ilNumberInputGUI($period, "conf_nr__" . $book_id);
            $nr_field->setValue(1);
            $nr_field->setSize(3);
            $nr_field->setMaxValue($counter);
            $nr_field->setMinValue($counter ? 1 : 0);
            $nr_field->setRequired(true);
            $form->addItem($nr_field);

            if (!$min_date || $id[1] < $min_date) {
                $min_date = $id[1];
            }
        }

        // recurrence
        $this->lng->loadLanguageModule("dateplaner");
        $rec_mode = new ilSelectInputGUI($this->lng->txt("cal_recurrences"), "recm");
        $rec_mode->setRequired(true);
        $rec_mode->setOptions(array(
            "-1" => $this->lng->txt("cal_no_recurrence"),
            1 => $this->lng->txt("cal_weekly"),
            2 => $this->lng->txt("r_14"),
            4 => $this->lng->txt("r_4_weeks")
        ));
        $form->addItem($rec_mode);

        $rec_end = new ilDateTimeInputGUI($this->lng->txt("cal_repeat_until"), "rece");
        $rec_end->setRequired(true);
        $rec_mode->addSubItem($rec_end);

        if (!$a_reload) {
            // show date only if active recurrence
            $rec_mode->setHideSubForm(true, '>= 1');

            if ($min_date) {
                $rec_end->setDate(new ilDateTime($min_date, IL_CAL_UNIX));
            }
        } else {
            // recurrence may not be changed on reload
            $rec_mode->setDisabled(true);
            $rec_end->setDisabled(true);
        }

        if ($a_group_id) {
            $grp = new ilHiddenInputGUI("grp_id");
            $grp->setValue($a_group_id);
            $form->addItem($grp);
        }

        if ($this->user_id_assigner !== $this->user_id_to_book) {
            $usr = new ilHiddenInputGUI("bkusr");
            $usr->setValue($this->user_id_to_book);
            $form->addItem($usr);
        }

        $form->addCommandButton("confirmedBookingNumbers", $this->lng->txt("confirm"));
        $form->addCommandButton("back", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function initBookingNumbersForm2(
        int $from,
        int $to,
        int $a_group_id,
        bool $a_reload = false
    ) : \ILIAS\Repository\Form\FormAdapterGUI {
        if ($this->user_id_assigner !== $this->user_id_to_book) {
            $this->ctrl->setParameterByClass(self::class, "bkusr", $this->user_id_to_book);
        }

        $this->ctrl->setParameterByClass(self::class, "slot", $from . "_" . $to);
        $counter = $this->getAvailableNr($this->book_request->getObjectId(), $from, $to);

        $period = ilDatePresentation::formatPeriod(
            new ilDateTime($from, IL_CAL_UNIX),
            new ilDateTime($to, IL_CAL_UNIX)
        );

        $form = $this->gui->form([self::class], "confirmedBookingNumbers2")
            ->asyncModal()
            ->section(
                "props",
                $this->lng->txt("book_confirm_booking_schedule_number_of_objects"),
                $this->lng->txt("book_confirm_booking_schedule_number_of_objects_info")
            )
            ->number("nr", $period, "", 1, 1, $counter)
            ->radio("recurrence", $this->lng->txt("cal_recurrences"), "", "0")
            ->radioOption("0", $this->lng->txt("book_no_recurrence"))
            ->radioOption("1", $this->lng->txt("book_recurrence"));

        return $form;
    }

    protected function getRecurrenceForm(
        int $nr
    ) : \ILIAS\Repository\Form\FormAdapterGUI {
        if ($this->user_id_assigner !== $this->user_id_to_book) {
            $this->ctrl->setParameterByClass(self::class, "bkusr", $this->user_id_to_book);
        }
        $this->ctrl->setParameterByClass(self::class, "slot", $this->book_request->getSlot());
        $this->ctrl->setParameterByClass(self::class, "object_id", $this->book_request->getObjectId());
        $this->ctrl->setParameterByClass(self::class, "nr", $nr);

        $this->lng->loadLanguageModule("dateplaner");
        $today = new ilDate(time(), IL_CAL_UNIX);
        $form = $this->gui->form([self::class], "confirmedBookingNumbers2")
            ->section(
                "props",
                $this->lng->txt("book_confirm_booking_schedule_number_of_objects"),
                $this->lng->txt("book_confirm_booking_schedule_number_of_objects_info")
            )
            ->switch("recurrence", $this->lng->txt("cal_recurrences"), "", "1")
            ->group("1", $this->lng->txt("cal_weekly"))
            ->date("until1", $this->lng->txt("cal_repeat_until"), "", $today)
            ->group("2", $this->lng->txt("r_14"))
            ->date("until2", $this->lng->txt("cal_repeat_until"), "", $today)
            ->group("4", $this->lng->txt("r_4_weeks"))
            ->date("until3", $this->lng->txt("cal_repeat_until"), "", $today)
            ->end();

        return $form;
    }

    public function confirmedBookingNumbers2() : void
    {
        //get the user who will get the booking.
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        }
        $slot = $this->book_request->getSlot();
        $slot_arr = explode("_", $slot);
        $from = $slot_arr[0];
        $to = $slot_arr[1];
        $obj_id = $this->book_request->getObjectId();

        // form not valid -> show again
        $form = $this->initBookingNumbersForm2($from, $to, 0);
        if (!$form->isValid()) {
            $this->gui->modal($this->getBookgingObjectTitle())
                      ->form($form)
                      ->send();
        }

        // recurrence? -> show recurrence form
        $recurrence = $form->getData("recurrence");
        if ($recurrence === "1") {
            $form = $this->getRecurrenceForm((int) $form->getData("nr"));
            $this->gui->modal($this->getBookgingObjectTitle())
                      ->form($form)
                      ->send();
        }

        var_dump($recurrence);
        exit;
        return;

        // convert post data to initial form config
        $counter = array();
        $current_first = date("Y-m-d", $from);

        // recurrence

        // checkInput() has not been called yet, so we have to improvise
        $rece = $this->book_request->getRece();     // recurrence end
        $recm = $this->book_request->getRecm();     // recurrence mode
        $end = ilCalendarUtil::parseIncomingDate($rece, false);

        if ((int) $recm > 0 && $end && $current_first) {
            ksort($counter);
            $end = $end->get(IL_CAL_DATE);
            $cycle = (int) $recm * 7;
            $cut = 0;
            $org = $counter;
            while ($cut < 1000 && $this->addDaysDate($current_first, $cycle) <= $end) {
                $cut++;
                $current_first = null;
                foreach ($org as $item_id => $max) {
                    $parts = explode("_", $item_id);
                    $obj_id = $parts[0];

                    $from = $this->addDaysStamp($parts[1], $cycle * $cut);
                    $to = $this->addDaysStamp($parts[2], $cycle * $cut);

                    $new_item_id = $obj_id . "_" . $from . "_" . $to;

                    // form reload because of validation errors
                    if (!isset($counter[$new_item_id]) && date("Y-m-d", $to) <= $end) {
                        // get max available for added dates
                        $new_max = ilBookingReservation::getAvailableObject(array($obj_id), $from, $to - 1, false, true);
                        $new_max = (int) $new_max[$obj_id];

                        $counter[$new_item_id] = $new_max;

                        if (!$current_first) {
                            $current_first = date("Y-m-d", $from);
                        }

                        // clone input
                        throw new ilException("Booking process max invalid");
                        //$_POST["conf_nr__" . $new_item_id . "_" . $new_max] = $_POST["conf_nr__" . $item_id . "_" . $max];
                    }
                }
            }
        }

        $group_id = $this->book_request->getGroupId();

        $form = $this->initBookingNumbersForm($counter, $group_id, true);
        if ($form->checkInput()) {
            $success = false;
            $rsv_ids = array();
            foreach ($counter as $id => $all_nr) {
                $book_nr = $form->getInput("conf_nr__" . $id . "_" . $all_nr);
                $parts = explode("_", $id);
                $obj_id = $parts[0];
                $from = $parts[1];
                $to = $parts[2] - 1;

                // get currently available slots
                $counter = ilBookingReservation::getAvailableObject(array($obj_id), $from, $to, false, true);
                $counter = $counter[$obj_id];
                if ($counter) {
                    // we can only book what is left
                    $book_nr = min($book_nr, $counter);
                    for ($loop = 0; $loop < $book_nr; $loop++) {
                        $rsv_ids[] = $this->processBooking($obj_id, $from, $to, $group_id);
                        $success = $obj_id;
                    }
                }
            }
            if ($success) {
                $this->saveParticipant();
                $this->handleBookingSuccess($success, $rsv_ids);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed'), true);
                $this->back();
            }
        } else {
            // ilDateTimeInputGUI does NOT add hidden values on disabled!

            $rece_array = explode(".", $rece);

            $rece_day = str_pad($rece_array[0], 2, "0", STR_PAD_LEFT);
            $rece_month = str_pad($rece_array[1], 2, "0", STR_PAD_LEFT);
            $rece_year = $rece_array[2];

            // ilDateTimeInputGUI will choke on POST array format
            //$_POST["rece"] = null;

            $form->setValuesByPost();

            $rece_date = new ilDate($rece_year . "-" . $rece_month . "-" . $rece_day, IL_CAL_DATE);

            $rece = $form->getItemByPostVar("rece");
            if ($rece !== null) {
                $rece->setDate($rece_date);
            }
            $recm = $form->getItemByPostVar("recm");
            if ($recm !== null) {
                $recm->setHideSubForm($recm < 1);
            }

            $hidden_date = new ilHiddenInputGUI("rece");
            $hidden_date->setValue($rece_date);
            $form->addItem($hidden_date);

            $this->confirmBookingNumbers($counter, $group_id, $form);
        }
    }


    public function confirmedBookingNumbers() : void
    {

        //get the user who will get the booking.
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
        }

        // convert post data to initial form config
        $counter = array();
        $current_first = $obj_id = null;
        foreach (array_keys($this->raw_post_data) as $id) {
            // e.g.conf_nr__4_1660896000_1660899600_2
            if (str_starts_with($id, "conf_nr__")) {
                $id = explode("_", substr($id, 9));
                // e.g. $counter["4_1660896000_1660899600"] = 2;
                $counter[$id[0] . "_" . $id[1] . "_" . $id[2]] = (int) $id[3];
                if (!$current_first) {
                    $current_first = date("Y-m-d", $id[1]);
                }
            }
        }

        // recurrence

        // checkInput() has not been called yet, so we have to improvise
        $rece = $this->book_request->getRece();     // recurrence end
        $recm = $this->book_request->getRecm();     // recurrence mode
        $end = ilCalendarUtil::parseIncomingDate($rece, false);

        if ((int) $recm > 0 && $end && $current_first) {
            ksort($counter);
            $end = $end->get(IL_CAL_DATE);
            $cycle = (int) $recm * 7;
            $cut = 0;
            $org = $counter;
            while ($cut < 1000 && $this->addDaysDate($current_first, $cycle) <= $end) {
                $cut++;
                $current_first = null;
                foreach ($org as $item_id => $max) {
                    $parts = explode("_", $item_id);
                    $obj_id = $parts[0];

                    $from = $this->addDaysStamp($parts[1], $cycle * $cut);
                    $to = $this->addDaysStamp($parts[2], $cycle * $cut);

                    $new_item_id = $obj_id . "_" . $from . "_" . $to;

                    // form reload because of validation errors
                    if (!isset($counter[$new_item_id]) && date("Y-m-d", $to) <= $end) {
                        // get max available for added dates
                        $new_max = ilBookingReservation::getAvailableObject(array($obj_id), $from, $to - 1, false, true);
                        $new_max = (int) $new_max[$obj_id];

                        $counter[$new_item_id] = $new_max;

                        if (!$current_first) {
                            $current_first = date("Y-m-d", $from);
                        }

                        // clone input
                        throw new ilException("Booking process max invalid");
                        //$_POST["conf_nr__" . $new_item_id . "_" . $new_max] = $_POST["conf_nr__" . $item_id . "_" . $max];
                    }
                }
            }
        }

        $group_id = $this->book_request->getGroupId();

        $form = $this->initBookingNumbersForm($counter, $group_id, true);
        if ($form->checkInput()) {
            $success = false;
            $rsv_ids = array();
            foreach ($counter as $id => $all_nr) {
                $book_nr = $form->getInput("conf_nr__" . $id . "_" . $all_nr);
                $parts = explode("_", $id);
                $obj_id = $parts[0];
                $from = $parts[1];
                $to = $parts[2] - 1;

                // get currently available slots
                $counter = ilBookingReservation::getAvailableObject(array($obj_id), $from, $to, false, true);
                $counter = $counter[$obj_id];
                if ($counter) {
                    // we can only book what is left
                    $book_nr = min($book_nr, $counter);
                    for ($loop = 0; $loop < $book_nr; $loop++) {
                        $rsv_ids[] = $this->processBooking($obj_id, $from, $to, $group_id);
                        $success = $obj_id;
                    }
                }
            }
            if ($success) {
                $this->saveParticipant();
                $this->handleBookingSuccess($success, $rsv_ids);
            } else {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('book_reservation_failed'), true);
                $this->back();
            }
        } else {
            // ilDateTimeInputGUI does NOT add hidden values on disabled!

            $rece_array = explode(".", $rece);

            $rece_day = str_pad($rece_array[0], 2, "0", STR_PAD_LEFT);
            $rece_month = str_pad($rece_array[1], 2, "0", STR_PAD_LEFT);
            $rece_year = $rece_array[2];

            // ilDateTimeInputGUI will choke on POST array format
            //$_POST["rece"] = null;

            $form->setValuesByPost();

            $rece_date = new ilDate($rece_year . "-" . $rece_month . "-" . $rece_day, IL_CAL_DATE);

            $rece = $form->getItemByPostVar("rece");
            if ($rece !== null) {
                $rece->setDate($rece_date);
            }
            $recm = $form->getItemByPostVar("recm");
            if ($recm !== null) {
                $recm->setHideSubForm($recm < 1);
            }

            $hidden_date = new ilHiddenInputGUI("rece");
            $hidden_date->setValue($rece_date);
            $form->addItem($hidden_date);

            $this->confirmBookingNumbers($counter, $group_id, $form);
        }
    }

    protected function addDaysDate(
        string $a_date,
        int $a_days
    ) : string {
        $date = date_parse($a_date);
        $stamp = mktime(0, 0, 1, $date["month"], $date["day"] + $a_days, $date["year"]);
        return date("Y-m-d", $stamp);
    }

    protected function addDaysStamp(
        int $a_stamp,
        int $a_days
    ) : int {
        $date = getdate($a_stamp);
        return mktime(
            $date["hours"],
            $date["minutes"],
            $date["seconds"],
            $date["mon"],
            $date["mday"] + $a_days,
            $date["year"]
        );
    }

    //
    // Step 3: Display post success info
    //

    protected function handleBookingSuccess(
        int $a_obj_id,
        array $a_rsv_ids = null
    ) : void {
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('book_reservation_confirmed'), true);

        // show post booking information?
        $obj = new ilBookingObject($a_obj_id);
        $pfile = $obj->getPostFile();
        $ptext = $obj->getPostText();

        if (trim($ptext) || $pfile) {
            if (count($a_rsv_ids)) {
                $this->ctrl->setParameter($this, 'rsv_ids', implode(";", $a_rsv_ids));
            }
            $this->ctrl->redirect($this, 'displayPostInfo');
        } else {
            if ($this->ctrl->isAsynch()) {
                $this->gui->send("<script>window.location.href = '" . $this->ctrl->getLinkTarget($this, $this->book_request->getReturnCmd()) . "';</script>");
            } else {
                $this->back();
            }
        }
    }

    /**
     * Display post booking informations
     */
    public function displayPostInfo() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $id = $this->book_obj_id;
        if (!$id) {
            return;
        }

        // placeholder

        $book_ids = ilBookingReservation::getObjectReservationForUser($id, $this->user_id_assigner);
        $tmp = array();
        foreach ($book_ids as $book_id) {
            if (in_array($book_id, $this->rsv_ids)) {
                $obj = new ilBookingReservation($book_id);
                $from = $obj->getFrom();
                $to = $obj->getTo();
                if ($from > time()) {
                    $tmp[$from . "-" . $to]++;
                }
            }
        }

        $olddt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $period = array();
        ksort($tmp);
        foreach ($tmp as $time => $counter) {
            $time = explode("-", $time);
            $time = ilDatePresentation::formatPeriod(
                new ilDateTime($time[0], IL_CAL_UNIX),
                new ilDateTime($time[1], IL_CAL_UNIX)
            );
            if ($counter > 1) {
                $time .= " (" . $counter . ")";
            }
            $period[] = $time;
        }
        $book_id = array_shift($book_ids);

        ilDatePresentation::setUseRelativeDates($olddt);


        /*
        #23578 since Booking pool participants.
        $obj = new ilBookingReservation($book_id);
        if ($obj->getUserId() != $ilUser->getId())
        {
            return;
        }
        */

        $obj = new ilBookingObject($id);
        $pfile = $obj->getPostFile();
        $ptext = $obj->getPostText();

        $mytpl = new ilTemplate('tpl.booking_reservation_post.html', true, true, 'Modules/BookingManager/BookingProcess');
        $mytpl->setVariable("TITLE", $lng->txt('book_post_booking_information'));

        if ($ptext) {
            // placeholder
            $ptext = str_replace(
                ["[OBJECT]", "[PERIOD]"],
                [$obj->getTitle(), implode("<br />", $period)],
                $ptext
            );

            $mytpl->setVariable("POST_TEXT", nl2br($ptext));
        }

        if ($pfile) {
            $url = $ilCtrl->getLinkTarget($this, 'deliverPostFile');

            $mytpl->setVariable("DOWNLOAD", $lng->txt('download'));
            $mytpl->setVariable("URL_FILE", $url);
            $mytpl->setVariable("TXT_FILE", $pfile);
        }

        $mytpl->setVariable("TXT_SUBMIT", $lng->txt('ok'));
        $mytpl->setVariable("URL_SUBMIT", $ilCtrl->getLinkTarget($this, "back"));

        $tpl->setContent($mytpl->get());
    }

    /**
     * Deliver post booking file
     */
    public function deliverPostFile() : void
    {
        $id = $this->book_obj_id;
        if (!$id) {
            return;
        }

        $book_ids = ilBookingReservation::getObjectReservationForUser($id, $this->user_id_assigner);
        $book_id = current($book_ids);
        $obj = new ilBookingReservation($book_id);
        if ($obj->getUserId() !== $this->user_id_assigner) {
            return;
        }

        $obj = new ilBookingObject($id);
        $file = $obj->getPostFileFullPath();
        if ($file) {
            ilFileDelivery::deliverFileLegacy($file, $obj->getPostFile());
        }
    }
}
