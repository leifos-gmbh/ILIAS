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
        'ilSoapUserAdministration',
        'ilSoapCourseAdministration'
    ];

    /**
     * @var null | \ilLogger
     */
    private $logger = null;

    public function __construct()
    {
        $this->initIlias();

        global $DIC;

        $this->logger = $DIC->logger()->wsrc();
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
                foreach((array) $arguments[0] as $argument) {
                    $arguments_array[] = $argument;
                }
            }

            $this->logger->dump($arguments_array);
            #$return = call_user_func_array($reflection_closure, $arguments_array);

            $user_admin = new ilSoapUserAdministration();
            $return  = $user_admin->login($arguments_array[0],$arguments_array[1],$arguments_array[2]);


            $this->logger->dump($return);
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
            ilInitialisation::reinitILIAS();
        }
        catch(Exception $e) {
            // authentication failed
        }
    }
}