<?php

/**
 * Class ilSoapRequestHandler
 * Handles soap request in document/literal style
 */
class ilSoapRequestHandler
{
    protected const SOAP_PATH = './webservice/soap/classes/';

    /**
     * @var string[]
     */
    protected const SOAP_CLASSES = [
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
     * Handle soap calls
     * @param $name
     * @param $arguments
     * @throws SoapFault
     */
    public function __call($name, $arguments)
    {
        $this->logger->debug('Incoming SOAP request for: ' . $name);
        $reflection_closure = $this->find($name);
        if($reflection_closure instanceof Closure) {

            $arguments_array = null;
            if(is_array($arguments)) {
                $arguments_array = null;
                foreach ($arguments as $idx => $argument) {
                    if (is_object($argument)) {
                        if (is_array($argument->value)) {
                            $arguments_array[] = $argument->value;
                        }
                        else {
                            $arguments_array[] = [$argument->value];
                        }
                    }
                    else {
                        $arguments_array[] = $argument;
                    }
                }
            }
            $this->logger->dump($arguments_array, \ilLogLevel::DEBUG);

            $return = call_user_func_array($reflection_closure, $arguments_array);

            $this->logger->debug('Return value is: ');
            $this->logger->dump($return , \ilLogLevel::DEBUG);

            return $return;
        }
        else {
            throw new SoapFault('SOAP-ENV:Server', 'Call to undefined SOAP method: ' . $name);
        }
    }

    /**
     * @param string $method_name
     */
    protected function find(string $method_name) :? Closure
    {
        foreach (self::SOAP_CLASSES as $soap_handler) {

            include_once self::SOAP_PATH . 'class.' . $soap_handler . '.php';
            $reflection = new ReflectionClass($soap_handler);
            foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $reflection_method) {

                if($reflection_method->getName() == $method_name) {

                    // init reflection object
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
            ilInitialisation::reinitILIAS();
            \ilContext::init(\ilContext::CONTEXT_SOAP);
        }
        catch(Exception $e) {

            global $DIC;
            $logger = $DIC->logger()->wsrv();
            $logger->error($e->getMessage());
        }
    }
}