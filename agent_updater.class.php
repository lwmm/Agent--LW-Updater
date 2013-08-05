<?php

class agent_updater extends lw_agent
{

    protected $config;
    protected $request;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->config = $this->conf;
        $this->className = "agent_updater";
        $this->adminSurfacePath = $this->config['path']['agents'] . "adminSurface/templates/";

        $usage = new lw_usage($this->className, "0");
        $this->secondaryUser = $usage->executeUsage();

        include_once(dirname(__FILE__) . '/Services/Autoloader.php');
        $autoloader = new \AgentUpdater\Services\Autoloader();
    }

    protected function showEdit()
    {
        $response = new \AgentUpdater\Services\Response();
        $controller = new \AgentUpdater\Controller\UpdaterController($this->config, $response, $this->request);
        $controller->execute();
        return $response->getOutputByKey("AgentUpdater");
    }

    protected function buildNav()
    {
        $view = new \AgentUpdater\Views\Navigation();
        return $view->render();
    }

    protected function deleteAllowed()
    {
        return true;
    }

}