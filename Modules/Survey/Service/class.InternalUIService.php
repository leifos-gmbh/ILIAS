<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey;
use ILIAS\Survey\Settings;
use ILIAS\Survey\Mode\ModeFactory;
use ILIAS\Survey\Mode\UIModifier;

/**
 * Survey internal ui service
 *
 * @author killing@leifos.de
 */
class InternalUIService
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilObjectServiceInterface
     */
    protected $object_service;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var ModeFactory
     */
    protected $mode_factory;

    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    /**
     * Constructor
     */
    public function __construct(
        \ilObjectServiceInterface $object_service,
        ModeFactory $mode_factory,
        InternalDomainService $domain_service
    )
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->object_service = $object_service;
        $this->mode_factory = $mode_factory;
        $this->domain_service = $domain_service;

        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->request = $DIC->http()->request();
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function surveySettings(\ilObjSurvey $survey) : Settings\UIFactory
    {
        return new Settings\UIFactory(
            $this,
            $this->object_service,
            $survey,
            $this->domain_service
        );
    }

    public function modeUIModifier(int $mode) : UIModifier
    {
        $mode_provider = $this->mode_factory->getModeById($mode);
        return $mode_provider->getUIModifier();
    }

    public function ctrl() : \ilCtrl
    {
        return $this->ctrl;
    }

    public function lng() : \ilLanguage
    {
        return $this->lng;
    }

}
