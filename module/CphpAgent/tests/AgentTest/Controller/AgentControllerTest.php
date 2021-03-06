<?php

namespace CphpAgentTest\Controller;

use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

use Agent\Controller\AgentController as Controller;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AgentControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var Controller $controller
     */
    protected $controller;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var bool
     */
    protected $traceError = true;


    protected function setUp()
    {
        $this->setApplicationConfig(
            include '../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {

        $deploymentTableMock = $this->getMockBuilder('CphpAgent\Model\DeploymentTable')
            ->disableOriginalConstructor()
//            ->setMethods(array('fetchAll'))
            ->getMock();

        $deploymentTableMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(array()));

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('CphpAgent\Model\DeploymentTable', $deploymentTableMock);
//        var_dump($serviceManager->get('CphpAgent\Model\DeploymentTable'));exit;

        $this->dispatch('/');
        $this->assertResponseStatusCode(200);
    }
}