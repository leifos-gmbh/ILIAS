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

use ILIAS\Tracking\DB\Factory as TrackingDBFactory;
use ILIAS\Tracking\DB\LPSettings\Element\LPSettings as TrackingDBLPSettings;

/**
 * Class ilLPObjSettings
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-tracking
 */
class ilLPObjSettings
{
    public const LP_MODE_DEACTIVATED = 0;
    public const LP_MODE_TLT = 1;
    public const LP_MODE_VISITS = 2;
    public const LP_MODE_MANUAL = 3;
    public const LP_MODE_OBJECTIVES = 4;
    public const LP_MODE_COLLECTION = 5;
    public const LP_MODE_SCORM = 6;
    public const LP_MODE_TEST_FINISHED = 7;
    public const LP_MODE_TEST_PASSED = 8;
    public const LP_MODE_EXERCISE_RETURNED = 9;
    public const LP_MODE_EVENT = 10;
    public const LP_MODE_MANUAL_BY_TUTOR = 11;
    public const LP_MODE_SCORM_PACKAGE = 12;
    public const LP_MODE_UNDEFINED = 13;
    public const LP_MODE_PLUGIN = 14;
    public const LP_MODE_COLLECTION_TLT = 15;
    public const LP_MODE_COLLECTION_MANUAL = 16;
    public const LP_MODE_QUESTIONS = 17;
    public const LP_MODE_SURVEY_FINISHED = 18;
    public const LP_MODE_VISITED_PAGES = 19;
    public const LP_MODE_CONTENT_VISITED = 20;
    public const LP_MODE_COLLECTION_MOBS = 21;
    public const LP_MODE_STUDY_PROGRAMME = 22;
    public const LP_MODE_INDIVIDUAL_ASSESSMENT = 23;
    public const LP_MODE_CMIX_COMPLETED = 24;
    public const LP_MODE_CMIX_COMPL_WITH_FAILED = 25;
    public const LP_MODE_CMIX_PASSED = 26;
    public const LP_MODE_CMIX_PASSED_WITH_FAILED = 27;
    public const LP_MODE_CMIX_COMPLETED_OR_PASSED = 28;
    public const LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED = 29;
    public const LP_DEFAULT_VISITS = 30;
    public const LP_MODE_LTI_OUTCOME = 31;
    public const LP_MODE_COURSE_REFERENCE = 32;
    public const LP_MODE_CONTRIBUTION_TO_DISCUSSION = 33;

    protected static array $map = array(

        self::LP_MODE_DEACTIVATED => array('ilLPStatus',
                                           'trac_mode_deactivated',
                                           'trac_mode_deactivated_info_new'
        )

        ,
        self::LP_MODE_TLT => array('ilLPStatusTypicalLearningTime',
                                   'trac_mode_tlt',
                                   'trac_mode_tlt_info'
        ) // info has dynamic part!

        ,
        self::LP_MODE_VISITS => array('ilLPStatusVisits',
                                      'trac_mode_visits',
                                      'trac_mode_visits_info'
        )

        ,
        self::LP_MODE_MANUAL => array('ilLPStatusManual',
                                      'trac_mode_manual',
                                      'trac_mode_manual_info'
        )

        ,
        self::LP_MODE_OBJECTIVES => array('ilLPStatusObjectives',
                                          'trac_mode_objectives',
                                          'trac_mode_objectives_info'
        )

        ,
        self::LP_MODE_COLLECTION => array('ilLPStatusCollection',
                                          'trac_mode_collection',
                                          'trac_mode_collection_info'
        )

        ,
        self::LP_MODE_SCORM => array('ilLPStatusSCORM',
                                     'trac_mode_scorm',
                                     'trac_mode_scorm_info'
        )

        ,
        self::LP_MODE_TEST_FINISHED => array('ilLPStatusTestFinished',
                                             'trac_mode_test_finished',
                                             'trac_mode_test_finished_info'
        )

        ,
        self::LP_MODE_TEST_PASSED => array('ilLPStatusTestPassed',
                                           'trac_mode_test_passed',
                                           'trac_mode_test_passed_info'
        )

        ,
        self::LP_MODE_EXERCISE_RETURNED => array('ilLPStatusExerciseReturned',
                                                 'trac_mode_exercise_returned',
                                                 'trac_mode_exercise_returned_info'
        )

        ,
        self::LP_MODE_EVENT => array('ilLPStatusEvent',
                                     'trac_mode_event',
                                     'trac_mode_event_info'
        )

        ,
        self::LP_MODE_MANUAL_BY_TUTOR => array('ilLPStatusManualByTutor',
                                               'trac_mode_manual_by_tutor',
                                               'trac_mode_manual_by_tutor_info'
        )

        ,
        self::LP_MODE_SCORM_PACKAGE => array('ilLPStatusSCORMPackage',
                                             'trac_mode_scorm_package',
                                             'trac_mode_scorm_package_info'
        )

        ,
        self::LP_MODE_UNDEFINED => null

        ,
        self::LP_MODE_PLUGIN => array('ilLPStatusPlugin',
                                      'trac_mode_plugin',
                                      ''
        ) // no settings screen, so no info needed

        ,
        self::LP_MODE_COLLECTION_TLT => array('ilLPStatusCollectionTLT',
                                              'trac_mode_collection_tlt',
                                              'trac_mode_collection_tlt_info'
        )

        ,
        self::LP_MODE_COLLECTION_MANUAL => array('ilLPStatusCollectionManual',
                                                 'trac_mode_collection_manual',
                                                 'trac_mode_collection_manual_info'
        )

        ,
        self::LP_MODE_QUESTIONS => array('ilLPStatusQuestions',
                                         'trac_mode_questions',
                                         'trac_mode_questions_info'
        )

        ,
        self::LP_MODE_SURVEY_FINISHED => array('ilLPStatusSurveyFinished',
                                               'trac_mode_survey_finished',
                                               'trac_mode_survey_finished_info'
        )

        ,
        self::LP_MODE_VISITED_PAGES => array('ilLPStatusVisitedPages',
                                             'trac_mode_visited_pages',
                                             'trac_mode_visited_pages_info'
        )

        ,
        self::LP_MODE_CONTENT_VISITED => array('ilLPStatusContentVisited',
                                               'trac_mode_content_visited',
                                               'trac_mode_content_visited_info'
        )

        ,
        self::LP_MODE_COLLECTION_MOBS => array('ilLPStatusCollectionMobs',
                                               'trac_mode_collection_mobs',
                                               'trac_mode_collection_mobs_info'
        )

        ,
        self::LP_MODE_STUDY_PROGRAMME => array('ilLPStatusStudyProgramme',
                                               'trac_mode_study_programme',
                                               ''
        )

        ,
        self::LP_MODE_INDIVIDUAL_ASSESSMENT => array('ilLPStatusIndividualAssessment',
                                                     'trac_mode_individual_assessment',
                                                     'trac_mode_individual_assessment_info'
        )

        ,
        self::LP_MODE_CMIX_COMPLETED => array(ilLPStatusCmiXapiCompleted::class,
                                              'trac_mode_cmix_completed',
                                              'trac_mode_cmix_completed_info'
        )

        ,
        self::LP_MODE_CMIX_COMPL_WITH_FAILED => array(ilLPStatusCmiXapiCompletedWithFailed::class,
                                                      'trac_mode_cmix_compl_with_failed',
                                                      'trac_mode_cmix_compl_with_failed_info'
        )

        ,
        self::LP_MODE_CMIX_PASSED => array(ilLPStatusCmiXapiPassed::class,
                                           'trac_mode_cmix_passed',
                                           'trac_mode_cmix_passed_info'
        )

        ,
        self::LP_MODE_CMIX_PASSED_WITH_FAILED => array(ilLPStatusCmiXapiPassedWithFailed::class,
                                                       'trac_mode_cmix_passed_with_failed',
                                                       'trac_mode_cmix_passed_with_failed_info'
        )

        ,
        self::LP_MODE_CMIX_COMPLETED_OR_PASSED => array(ilLPStatusCmiXapiCompletedOrPassed::class,
                                                        'trac_mode_cmix_completed_or_passed',
                                                        'trac_mode_cmix_completed_or_passed_info'
        )

        ,
        self::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED => array(ilLPStatusCmiXapiCompletedOrPassedWithFailed::class,
                                                                'trac_mode_cmix_compl_or_passed_with_failed',
                                                                'trac_mode_cmix_compl_or_passed_with_failed_info'
        )

        ,
        self::LP_MODE_LTI_OUTCOME => array(ilLPStatusLtiOutcome::class,
                                           'trac_mode_lti_outcome',
                                           'trac_mode_lti_outcome_info'
        )

        ,
        self::LP_MODE_COURSE_REFERENCE => [
            'ilLPStatusCourseReference',
            'trac_mode_course_reference',
            'trac_mode_course_reference_info'
        ],

        self::LP_MODE_CONTRIBUTION_TO_DISCUSSION => [
            ilLPStatusContributionToDiscussion::class,
            'trac_mode_contribution_to_discussion',
            'trac_mode_contribution_to_discussion_info'
        ],
    );

    protected ilObjectDataCache $objectDataCache;
    protected TrackingDBFactory $tracking_db_factory;
    protected TrackingDBLPSettings $lp_settings;

    public function __construct(int $a_obj_id)
    {
        global $DIC;
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->tracking_db_factory = new TrackingDBFactory($DIC->database());
        $entry_exists = $this->tracking_db_factory->lpSettings()->repository()->isLPSettingsEntryInDB($a_obj_id);
        if (!$entry_exists) {
            $olp = ilObjectLP::getInstance($a_obj_id);
            $this->lp_settings = $this->tracking_db_factory->lpSettings()->element()->lpSettings()
                ->withObjectId($a_obj_id)
                ->withObjType($this->objectDataCache->lookupType($a_obj_id))
                ->withUMode($olp->getDefaultMode())
                ->withVisits(self::LP_DEFAULT_VISITS);
        }
        if ($entry_exists) {
            $this->lp_settings = $this->tracking_db_factory->lpSettings()->repository()->readLPSettings($a_obj_id);
        }
    }

    /**
     * Clone settings
     * @access public
     * @param int new obj id
     */
    public function cloneSettings(int $a_new_obj_id): bool
    {
        $this->tracking_db_factory->lpSettings()->repository()->writeLPSettings(
            $this->lp_settings
                ->withObjectId($a_new_obj_id)
        );
        return true;
    }

    public function getVisits(): int
    {
        return $this->lp_settings->getVisits();
    }

    public function getMode(): int
    {
        return $this->lp_settings->getUMode();
    }

    public function getObjId(): int
    {
        return $this->lp_settings->getObjectId();
    }

    public function getObjType(): string
    {
        return $this->lp_settings->getObjType();
    }

    public function setVisits(
        int $a_visits
    ): void {
        $this->lp_settings = $this->lp_settings
            ->withVisits($a_visits);
    }

    public function setMode(
        int $a_mode
    ): void {
        $this->lp_settings = $this->lp_settings
            ->withUMode($a_mode);
    }

    public function read(): bool
    {
        $new_lp_settings = $this->tracking_db_factory->lpSettings()->repository()->readLPSettings($this->lp_settings->getObjectId());
        if (is_null($new_lp_settings)) {
            return false;
        }
        $this->lp_settings = $new_lp_settings;
        return true;
    }

    public function update(
        bool $a_refresh_lp = true
    ): bool {
        return $this->insert($a_refresh_lp);
    }

    public function insert(
        bool $a_refresh_lp = true
    ): bool {
        $new_entry = $this->tracking_db_factory->lpSettings()->repository()->isLPSettingsEntryInDB($this->lp_settings->getObjectId());
        $this->tracking_db_factory->lpSettings()->repository()->writeLPSettings($this->lp_settings);
        $this->read();
        if ($a_refresh_lp || $new_entry) {
            $this->doLPRefresh();
        }
        return true;
    }

    protected function doLPRefresh(): void
    {
        // refresh learning progress
        ilLPStatusWrapper::_refreshStatus($this->getObjId());
    }

    public static function _delete(
        int $a_obj_id
    ): bool {
        global $DIC;
        $tracking_db_factory = new TrackingDBFactory($DIC->database());
        $tracking_db_factory->lpSettings()->repository()->deleteLPSettings($a_obj_id);
        return true;
    }

    public static function _lookupVisits(
        int $a_obj_id
    ): int {
        global $DIC;
        $tracking_db_factory = new TrackingDBFactory($DIC->database());
        $lp_settings = $tracking_db_factory->lpSettings()->repository()->readLPSettings($a_obj_id);
        return is_null($lp_settings)
            ? self::LP_DEFAULT_VISITS
            : $lp_settings->getVisits();
    }

    public static function _lookupDBModeForObjects(
        array $a_obj_ids
    ): array {
        global $DIC;
        $tracking_db_factory = new TrackingDBFactory($DIC->database());
        $lp_settings = $tracking_db_factory->lpSettings()->repository()->readLPSettingsCollection(...$a_obj_ids);
        $db_modes = [];
        if (is_null($lp_settings)) {
            return $db_modes;
        }
        foreach ($lp_settings as $lp_setting) {
            $db_modes[$lp_setting->getObjectId()] = $lp_setting->getUMode();
        }
        return $db_modes;
    }

    public static function _lookupDBMode(
        int $a_obj_id
    ): ?int {
        global $DIC;
        $tracking_db_factory = new TrackingDBFactory($DIC->database());
        $lp_settings = $tracking_db_factory->lpSettings()->repository()->readLPSettings($a_obj_id);
        return is_null($lp_settings)
            ? null
            : $lp_settings->getUMode();
    }

    public static function _mode2Text(
        int $a_mode
    ): string {
        global $DIC;

        $lng = $DIC->language();
        if (
            array_key_exists($a_mode, self::$map) &&
            is_array(self::$map[$a_mode])
        ) {
            return $lng->txt(self::$map[$a_mode][1]);
        }
        return '';
    }

    public static function _mode2InfoText(
        int $a_mode
    ): string {
        global $DIC;

        $lng = $DIC->language();
        if (
            array_key_exists($a_mode, self::$map) &&
            is_array(self::$map[$a_mode])
        ) {
            $info = $lng->txt(self::$map[$a_mode][2]);
            if ($a_mode === self::LP_MODE_TLT) {
                // dynamic content
                $info = sprintf($info, ilObjUserTracking::_getValidTimeSpan());
            }
            return $info;
        }
        return '';
    }

    public static function getClassMap(): array
    {
        $res = [];
        foreach (self::$map as $mode => $item) {
            if ($item) {
                $res[$mode] = $item[0];
            }
        }
        return $res;
    }

    public static function _deleteByObjId(
        int $a_obj_id
    ): void {
        # we are only removing settings for now
        # invalid ut_lp_collections-entries are filtered
        # ut_lp_marks is deemed private user data
        global $DIC;
        $tracking_db_factory = new TrackingDBFactory($DIC->database());
        $tracking_db_factory->lpSettings()->repository()->deleteLPSettings($a_obj_id);
    }
}
