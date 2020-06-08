<?php

/**
 * Class ilSoapRequestHandler
 * Handles soap request in document/literal style
 */
class ilSoapRequestHandler
{
    const SOAP_PATH = './webservice/soap/classes/';

    const RESPONSE_NAMES = [
        'login' => 'sid',
        'loginCAS' => 'sid',
        'loginLDAP' => 'sid',
        'logout' => 'success',
        'lookupUser' => 'usr_id',
        'deleteUser' => 'success',
        'addCourse' => 'course_id',
        'deleteCourse' => 'success',
        'startBackgroundTask' => 'success',
        'excludeCourseMember' => 'success',
        'isAssignedToCourse' => 'role',
        'getCourseXML' => 'xml',
        'updateCourse' => 'success',
        'getObjIdByImportId' => 'obj_id',
        'getRefIdsByImportId' => 'ref_ids',
        'getObjectByReference' => 'object_xml',
        'getObjectsByTitle' => 'object_xml',
        'searchObjects' => 'object_xml',
        'getTreeChilds' => 'object_xml',
        'getXMLTree' => 'object_xml',
        'addObject' => 'ref_id',
        'updateObjects' => 'success',
        'addReference' => 'ref_id',
        'deleteObject' => 'success',
        'removeFromSystemByImportId' => 'success',
        'addUserRoleEntry' => 'success',
        'revokePermissions' => 'success',
        'grantPermissions' => 'success',
        'getLocalRoles' => 'role_xml',
        'getUserRoles' => 'role_xml',
        'addRole' => 'role_ids',
        'deleteRole' => 'success',
        'addRoleFromTemplate' => 'role_ids',
        'addGroup' => 'ref_id',
        'groupExists' => 'exists',
        'getGroup' => 'group_xml',
        'assignGroupMember' => 'success',
        'expludeGroupMember' => 'success',
        'isAssignedToGroup' => 'role',
        'sendMail' => 'status',
        'distributeMails' => 'status',
        'ilClone' => 'new_ref_id',
        'handleECSTasks' => 'success',
        'ilCloneDependencies' => 'success',
        'saveQuestionResult' => 'status',
        'saveQuestion' => 'status',
        'saveQuestionSolution' => 'status',
        'getQuestionSolution' => 'solution',
        'getTestUserData' => 'userdata',
        'getPositionOfQuestion' => 'position',
        'getPreviousReachedPoints' => 'position',
        'getNrOfQuestionsInPass' => 'count',
        'getStructureObjects' => 'xml',
        'importUsers' => 'protocol',
        'getRoles' => 'role_xml',
        'getUsersForContainer' => 'user_xml',
        'searchUser' => 'user_xml',
        'hasNewMail' => 'status',
        'getNIC' => 'xmlresultset',
        'getExerciseXML' => 'exercisexml',
        'addExercise' => 'ref_id',
        'updateExercise' => 'success',
        'getFileXML' => 'filexml',
        'addFile' => 'refid',
        'updateFile' => 'success',
        'getUserXML' => 'xml',
        'getObjIdsByRefIds' => 'obj_ids',
        'updateGroup' => 'success',
        'getIMSManifestXML' => 'xml',
        'hasSCORMCertificate' => 'success',
        'getSCORMCompletionStatus' => 'status',
        'copyObject' => 'xml',
        'moveObject' => 'result',
        'getTestResults' => 'xml',
        'removeTestResults' => 'success',
        'getCoursesForUser' => 'xml',
        'getGroupsForUser' => 'xml',
        'getPathForRefId' => 'xml',
        'searchRoles' => 'xml',
        'getInstallationInfoXML' => 'xml',
        'getClientInfoXML' => 'xml',
        'getSkillCompletionDateForTriggerRefId' => 'dates',
        'checkSkillUserCertificateForTriggerRefId' => 'have_certificates',
        'getSkillTriggerOfAllCertificates' => 'certificate_triggers',
        'getUserIdBySid' => 'user_id',
        'deleteExpiredDualOptInUserObjects' => 'status',
        'readWebLink' => 'weblinkxml',
        'createWebLink' => 'refid',
        'updateWebLink' => 'success',
        'getLearningProgressChanges' => 'lp_data',
        'deleteProgress' => 'status',
        'getProgressInfo' => 'user_result',
        'exportDataCollectionContent' => 'export_path',
        'processBackgroundTask' => 'status',
        'addDesktopItems' => 'num_added',
        'removeDesktopItems' => 'num_added'
    ];

    /**
     * @var string[]
     */
    const SOAP_CLASSES = [
        ilSoapUserAdministration::class,
        ilSoapCourseAdministration::class,
        ilSoapObjectAdministration::class,
        ilSoapTestAdministration::class,
        ilSoapRBACAdministration::class,
        ilSoapGroupAdministration::class,
        ilSoapExerciseAdministration::class,
        ilSoapFileAdministration::class,
        ilSoapLearningProgressAdministration::class,
        ilSoapDataCollectionAdministration::class,
        ilSoapWebLinkAdministration::class,
        ilSoapStructureObjectAdministration::class,
        ilSoapSCORMAdministration::class
    ];

    /**
     * @var null | \ilLogger
     */
    private $logger = null;

    private $server = null;

    public function __construct(SoapServer $server)
    {
        $this->initIlias();

        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->logger->dump('Current context is: ' . \ilContext::getType());
        $this->server = $server;
    }

    /**
     * Parse Header and remove invalid header variables
     */
    public static function parseHeader()
    {
        global $DIC;

        $logger = $DIC->logger()->wsrv();
        $input = file_get_contents('php://input');

        $logger->debug('Original request: ' . $input);
        $input_lines = explode("\n", $input);
        $header_wrapped = [];
        $in_header = false;
        foreach ($input_lines as $input_line) {
            $logger->debug('Input line: ' . $input_line);
            if (stristr($input_line, '<soapenv:Header>') !== false) {
                $in_header = true;
            }
            elseif (stristr($input_line, '</soapenv:Header>') !== false) {
                $in_header = false;
            }
            elseif (!$in_header) {
                $header_wrapped[] = $input_line;
            }
        }
        $input_wrapped = implode('\n', $header_wrapped);
        $logger->debug('Body filtered: ' . $input_wrapped);
        return $input_wrapped;
    }

    /**
     * Handle soap calls
     * @param $name
     * @param $arguments
     * @throws SoapFault
     */
    public function __call($name, $arguments)
    {
        $this->logger->debug('Incoming SOAP request for: ' . $name);
        $this->logger->dump($arguments);
        $reflection_closure = $this->find($name);
        if($reflection_closure instanceof Closure) {

            $arguments_array = $this->findArguments($arguments);
            $this->logger->dump($arguments_array, \ilLogLevel::DEBUG);
            $return = call_user_func_array($reflection_closure, $arguments_array);
            $result = $this->generateResultMessage($name, $return);
            return $result;
        }
        else {
            throw new SoapFault('SOAP-ENV:Server', 'Call to undefined SOAP method: ' . $name);
        }
    }

    /**
     * @param string $method
     * @param mixed  $return
     * @return object
     * @throws SoapFault
     */
    protected function generateResultMessage(string $method, $return)
    {
        $response = new stdClass();

        if (!array_key_exists($method, self::RESPONSE_NAMES)) {
            throw new SoapFault(
                'SOAP-ENV:Server',
                'Cannot find return definition for method call: ' . $method
            );
        }
        $response_name = self::RESPONSE_NAMES[$method];
        $response->$response_name = $return;
        return $response;
    }

    /**
     * @return array
     */
    protected function findArguments(array $arguments) : array
    {
        $array_arguments = [];
        foreach ($arguments as $argument_obj) {

            if (!is_object($argument_obj)) {
                $this->logger->warning('No valid arguments for soap call');
                $this->logger->dump($argument_obj, \ilLogLevel::WARNING);
                break;
            }
            foreach ($argument_obj as $argument_name => $argument_value) {

                if (is_object($argument_value)) {
                    if (is_array($argument_value->value) && count($argument_value->value)) {
                        $array_arguments[] = $argument_value->value;
                    }
                    elseif (is_array($argument_value->value)) {
                        $array_arguments[] = [];
                    }
                    else {
                        $array_arguments[] = (array) $argument_value->value;
                    }
                }
                else {
                    $array_arguments[] = $argument_value;
                }
            }
        }
        return $array_arguments;
    }

    /**
     * @param string $method_name
     */
    protected function find(string $method_name)
    {
        foreach (self::SOAP_CLASSES as $soap_handler) {

            include_once self::SOAP_PATH . 'class.' . $soap_handler . '.php';
            $reflection = new ReflectionClass($soap_handler);
            foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $reflection_method) {
                if($reflection_method->getName() == $method_name) {
                    return $reflection_method->getClosure(new $soap_handler);
                }
            }
        }
        return null;
    }

    /**
     * Init ILIAS
     */
    protected function initIlias()
    {
        try {
            \ilContext::init(\ilContext::CONTEXT_SOAP_NO_AUTH);
            \ilInitialisation::reinitILIAS();
            \ilContext::init(\ilContext::CONTEXT_SOAP);
        }
        catch(Exception $e) {

            global $DIC;
            $logger = $DIC->logger()->wsrv();
            $logger->error($e->getMessage());
        }
    }
}