<?php

namespace AgentUpdater\Controller;

class UpdaterController
{

    protected $request;
    protected $respone;
    protected $config;
    protected $db;

    public function __construct($config, $response, $request, $db)
    {
        $this->config = $config;
        $this->respone = $response;
        $this->request = $request;
        $this->db = $db;
    }

    public function execute()
    {
        $array = array();
        $content = "";
        if ($this->request->getAlnum("module")) {
            $module = $this->request->getAlnum("module");
        } else {
            $module = "md5checker";
        }

        switch ($module) {
            case "md5checker":
                if ($this->request->getInt("sent")) {
                    $md5CheckerController = new \AgentUpdater\Controller\Md5CheckerController($this->config, $this->request);
                    $array = $md5CheckerController->execute();
                }
                $md5checkerView = new \AgentUpdater\Views\Md5Checker($this->config);
                $content = $md5checkerView->render($array);
                break;

            case "systemupdater":
                if ($this->request->getInt("sent")) {
                    $systemUpdaterController = new \AgentUpdater\Controller\SystemUpdaterController($this->config, $this->request, $this->db);
                    $array = $systemUpdaterController->execute();
                }
                $systemUpdaterView = new \AgentUpdater\Views\SystemUpdater();
                $content = $systemUpdaterView->render($array);
                break;
        }

        $mainView = new \AgentUpdater\Views\MainContainer($this->config);
        $this->respone->setOutputByKey("AgentUpdater", $mainView->render($content, $module));
    }

}
