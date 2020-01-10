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
        'ilSoapCourseAdministration',
        'ilSoapObjectAdministration'
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
                $this->logger->dump($arguments);
                foreach ((array) $arguments as $index => $argument_obj) {
                    $this->logger->dump($argument_obj);
                    foreach ((array) $argument_obj as $property => $value) {
                        $arguments_array[] = $value;
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