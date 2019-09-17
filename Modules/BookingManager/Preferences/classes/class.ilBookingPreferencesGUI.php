<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking preferences ui class
 *
 * @author killing@leifos.de
 */
class ilBookingPreferencesGUI
{
    /**
     * @var ilCtrl 
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $main_tpl;

    /**
     * @var ilBookingManagerInternalService
     */
    protected $service;

    /**
     * @var ilObjBookingPool
     */
    protected $pool;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilBookingPreferencesDBRepository
     */
    protected $repo;

    /**
     * Constructor
     */
    public function __construct(ilObjBookingPool $pool)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->request = $DIC->http()->request();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->service = $DIC->bookingManager()->internal();
        $this->pool = $pool;
        $this->repo = $this->service->repo()->getPreferencesRepo();
    }

    /**
     * Execute command
     */
    function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");
        if ($cmd == "render") {
            $cmd = "show";
        }
        switch ($next_class)
        {
            default:
                if (in_array($cmd, ["show", "savePreferences"]))
                {
                    $this->$cmd();
                }
        }
    }
    
    /**
     * Show
     */
    protected function show()
    {
        $preferences = $this->service->domain()->preferences($this->pool);

        if ($preferences->isGivingPreferencesPossible()) {
            $this->listPreferenceOptions();
        } else {
            $this->listBookingResults();
        }
    }
    
    /**
     * List preference options
     */
    protected function listPreferenceOptions()
    {
        $ui = $this->ui;
        $form = $this->initPreferenceForm();
        $this->main_tpl->setContent($ui->renderer()->render($form));
    }

    /**
     * Init preferences form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initPreferenceForm()
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $repo = $this->repo;

        $preferences = $repo->getPreferencesOfUser($this->pool->getId(), $this->user->getId());
        $preferences = $preferences->getPreferences();

        $this->renderBookingInfo();

        foreach (ilBookingObject::getList($this->pool->getId()) as $book_obj) {
            $checked = (is_array($preferences[$this->user->getId()]) &&
                in_array($book_obj["booking_object_id"], $preferences[$this->user->getId()]))
                ? "checked"
                : "";

            $fields["cb_".$book_obj["booking_object_id"]] =
                $f->input()->field()->checkbox($book_obj["title"], $book_obj["description"])->withValue($checked);
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("book_preferences"));

        $form_action = $ctrl->getLinkTarget($this, "savePreferences");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Save preferences
     */
    public function savePreferences()
    {
        $preferences = $this->service->domain()->preferences($this->pool);

        if (!$preferences->isGivingPreferencesPossible()) {
            return;
        }

        $request = $this->request;
        $form = $this->initPreferenceForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $repo = $this->repo;

        if ($request->getMethod() == "POST")
        {
            $form = $form->withRequest($request);
            $data = $form->getData();

            if (is_array($data["sec"]))
            {
                $obj_ids = [];
                foreach ($data["sec"] as $k => $v) {
                    if ($v === "checked") {
                        $id = explode("_", $k);
                        $obj_ids[] = (int) $id[1];
                    }
                }
                $preferences = $this->service->data()->preferencesFactory()->preferences(
                    [$this->user->getId() => $obj_ids]
                );
                $repo->savePreferencesOfUser($this->pool->getId(), $this->user->getId(), $preferences);
                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            }
        }
        $ctrl->redirect($this, "show");
    }

    /**
     * Render booking info
     */
    protected function renderBookingInfo()
    {
        $lng = $this->lng;
        $info = $lng->txt("book_preference_info");
        $info = str_replace("%1", $this->pool->getPreferenceNumber(), $info);
        $info = str_replace("%2", ilDatePresentation::formatDate(
            new ilDateTime($this->pool->getPreferenceDeadline(), IL_CAL_UNIX)), $info);
        ilUtil::sendInfo($info);
    }

    
    /**
     * List booking results
     */
    protected function listBookingResults()
    {
        $main_tpl = $this->main_tpl;
        $lng = $this->lng;
        $repo = $this->repo;

        $info_gui = new ilInfoScreenGUI($this);

        // bookings
        $bookings = $this->service->domain()->preferences($this->pool)->storeBookings(
            $this->repo->getPreferences($this->pool->getId()));
        $info_gui->addSection($lng->txt("book_your_bookings"));
        $cnt = 1;
        if (is_array($bookings[$this->user->getId()])) {
            foreach ($bookings[$this->user->getId()] as $book_obj_id) {
                $book_obj = new ilBookingObject($book_obj_id);
                $info_gui->addProperty((string)$cnt++, $book_obj->getTitle());
            }
        } else {
            $info_gui->addProperty("", $lng->txt("book_no_bookings_for_you"));
        }

        // preferences
        $info_gui->addSection($lng->txt("book_your_preferences"));
        $preferences = $repo->getPreferencesOfUser($this->pool->getId(), $this->user->getId());
        $preferences = $preferences->getPreferences();
        $cnt = 1;
        if (is_array($preferences[$this->user->getId()])) {
            foreach ($preferences[$this->user->getId()] as $book_obj_id) {
                $book_obj = new ilBookingObject($book_obj_id);
                $info_gui->addProperty((string)$cnt++, $book_obj->getTitle());
            }
        } else {
            $info_gui->addProperty("", $lng->txt("book_no_preferences_for_you"));
        }

        $main_tpl->setContent($info_gui->getHTML());
    }
    
}