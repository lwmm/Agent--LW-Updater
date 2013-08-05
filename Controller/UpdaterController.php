<?php

namespace AgentUpdater\Controller;

class UpdaterController
{

    protected $request;
    protected $respone;
    protected $config;

    public function __construct($config, $response, $request)
    {
        $this->config = $config;
        $this->respone = $response;
        $this->request = $request;
    }

    public function execute()
    {
        $array = array();
        $content = "";
        if ($this->request->getAlnum("module")) {
            $module = $this->request->getAlnum("module");
        }
        else {
            $module = "md5checker";
        }

        switch ($module) {
            case "md5checker":
                if ($this->request->getInt("sent")) {
                    $md5CheckerController = new \AgentUpdater\Controller\Md5CheckerController($this->config, $this->request);
                    $array = $md5CheckerController->execute($array);
                }
                $md5checkerView = new \AgentUpdater\Views\Md5Checker($this->config);
                $content = $md5checkerView->render($array);
                break;
                
            case "md5checkerxml":
                if ($this->request->getInt("sent")) {
                    $md5CheckerXmlController = new \AgentUpdater\Controller\Md5CheckerXmlController($this->config, $this->request);
                    $array = $md5CheckerXmlController->execute($array);
                }
                $md5checkerView = new \AgentUpdater\Views\Md5CheckerXml();
                $content = $md5checkerView->render($array);
                break;
        }

        $mainView = new \AgentUpdater\Views\MainContainer($this->config);
        $this->respone->setOutputByKey("AgentUpdater", $mainView->render($content, $module));
    }

}