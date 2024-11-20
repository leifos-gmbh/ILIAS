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

declare(strict_types=0);

use ILIAS\DI\UIServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Tracking\View\FactoryInterface as ViewFactoryInterface;
use ILIAS\UI\Component\Symbol\Icon\Icon as UIIconIcon;
use ILIAS\UI\Component\Symbol\Icon\Standard as UIStandardIcon;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Tracking\View\Factory as ViewFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\Data\Factory as DataFactory;

/**
 * @ilCtrl_IsCalledBy ilLPPersonalGUI: ilDashboardGUI
 */
class ilLPPersonalGUI
{
    protected const PRESENTATION_OPTION_CURRENT = "current";
    protected const PRESENTATION_OPTION_FUTURE = "future";
    protected const PRESENTATION_OPTION_PAST = "past";
    protected const PRESENTATION_OPTION_ALL = "all";
    protected const URL_VAR_MODE = "viewcontrol_plp_mode";
    protected const URL_VAR_ACTION_MODE = "mode";
    protected const URL_NAMESPACE_PLP = "plp";
    protected const URL_NAMESPACE_VIEWCONTROL = "viewcontrol";
    protected const LNG_VAR_PRESENTATION_OPTION_CURRENT = "Current";
    protected const LNG_VAR_PRESENTATION_OPTION_FUTURE = "Future";
    protected const LNG_VAR_PRESENTATION_OPTION_PAST = "Past";
    protected const LNG_VAR_PRESENTATION_OPTION_ALL = "All";
    protected const LNG_VAR_PROPERTY_CRS_START = "Start";
    protected const LNG_VAR_PROPERTY_CRS_END = "End";
    protected const LNG_VAR_PROPERTY_CRS_ONLINE = "Online";
    protected const LNG_VAR_PROPERTY_CRS_ONLINE_YES = "Yes";
    protected const LNG_VAR_PROPERTY_CRS_ONLINE_NO = "No";
    protected const LNG_VAR_LISTING_TITLE = "Courses";
    protected HTTPServices $http;
    protected UIServices $ui;
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected RefineryFactory $refinery;
    protected DataFactory $data_factory;
    protected ViewFactoryInterface $tracking_view;

    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->data_factory = new DataFactory();
        $this->tracking_view = new ViewFactory();
    }

    public function executeCommand(): void
    {
        $this->listCourses();
    }

    protected function listCourses(): void
    {
        $filter = $this->tracking_view->dataRetrieval()->filter()
            ->withUserIds($this->user->getId())
            ->withObjectTypes("crs");
        $view_info = $this->tracking_view->dataRetrieval()->service()->retrieveViewInfo($filter);
        $items = [];
        foreach ($view_info->combinedInfoIterator() as $combinedInfo) {
            $obj_id = $combinedInfo->getObjectInfo()->getObjectId();
            $crs = new ilObjCourse($obj_id, false);
            $offline_str = $crs->getOfflineStatus()
                ? $this->lng->txt(self::LNG_VAR_PROPERTY_CRS_ONLINE_NO)
                : $this->lng->txt(self::LNG_VAR_PROPERTY_CRS_ONLINE_YES);
            $crs_start = ilDatePresentation::formatDate($crs->getCourseStart());
            $crs_end = ilDatePresentation::formatDate($crs->getCourseEnd());
            $properties = $this->tracking_view->propertyList()->builder()
                ->withProperty($this->lng->txt(self::LNG_VAR_PROPERTY_CRS_START), $crs_start)
                ->withProperty($this->lng->txt(self::LNG_VAR_PROPERTY_CRS_END), $crs_end)
                ->withProperty($this->lng->txt(self::LNG_VAR_PROPERTY_CRS_ONLINE), $offline_str)
                ->getList();
            $progress_chart = $this->tracking_view->renderer()->service()->standardProgressMeter($combinedInfo->getLPInfo());
            $item = $this->tracking_view->renderer()->service()->standardItem(
                $combinedInfo->getObjectInfo(),
                $properties
            )->withProgress(
                $progress_chart
            );
            $items[] = $item;
        }
        $current_presentation = self::PRESENTATION_OPTION_CURRENT;
        if ($this->http->wrapper()->query()->has(self::URL_VAR_MODE)) {
            $current_presentation = $this->http->wrapper()->query()->retrieve(
                self::URL_VAR_MODE,
                $this->refinery->kindlyTo()->string()
            );
        }
        $presentation_options = [
            self::PRESENTATION_OPTION_CURRENT => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_CURRENT),
            self::PRESENTATION_OPTION_FUTURE => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_FUTURE),
            self::PRESENTATION_OPTION_PAST => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_PAST),
            self::PRESENTATION_OPTION_ALL => $this->lng->txt(self::LNG_VAR_PRESENTATION_OPTION_ALL),
        ];
        $uri = $this->http->request()->getUri()->__toString();
        $url_builder = new URLBuilder($this->data_factory->uri($uri));
        list($url_builder, $action_parameter_token) =
            $url_builder->acquireParameters(
                [self::URL_NAMESPACE_VIEWCONTROL, self::URL_NAMESPACE_PLP],
                self::URL_VAR_ACTION_MODE
            );
        /** @var URLBuilder $url_builder */
        ;
        $modes = $this->ui->factory()->viewControl()->mode(
            [
                $presentation_options[self::PRESENTATION_OPTION_CURRENT] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_CURRENT)->buildURI(),
                $presentation_options[self::PRESENTATION_OPTION_FUTURE] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_FUTURE)->buildURI(),
                $presentation_options[self::PRESENTATION_OPTION_PAST] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_PAST)->buildURI(),
                $presentation_options[self::PRESENTATION_OPTION_ALL] => (string) $url_builder->withParameter($action_parameter_token, self::PRESENTATION_OPTION_ALL)->buildURI(),
            ],
            'Presentation Mode'
        )
            ->withActive($presentation_options[$current_presentation]);
        $view_controls = [
            $modes,
        ];
        switch ($current_presentation) {
            case self::PRESENTATION_OPTION_CURRENT:
            case self::PRESENTATION_OPTION_FUTURE:
            case self::PRESENTATION_OPTION_PAST:
            case self::PRESENTATION_OPTION_ALL:
            default:
                break;
        }
        $crs_item_group = $this->ui->factory()->item()->group("", $items);
        $ui_panel = $this->ui->factory()->panel()->listing()->standard(
            $this->lng->txt(self::LNG_VAR_LISTING_TITLE),
            [
                $crs_item_group
            ]
        )
            ->withViewControls($view_controls);
        $this->ui->mainTemplate()->setContent($this->ui->renderer()->render([$ui_panel]));
    }
}
