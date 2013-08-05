<?php

namespace AgentUpdater\Views;

class Md5CheckerXml
{
    public function render($array)
    {
        $view = new \lw_view(dirname(__FILE__) . '/Templates/Md5CheckerXml.phtml');
        $view->actionUrl = $this->config["url"]["client"] . "admin.php?obj=updater&module=md5checkerxml";

        $view->sent = $array["sent"];
        $view->xmlResults = $array["xmlResults"];
        $view->xmlString = $array["xmlString"];
        $view->debug = $array["debug"];
        
        return $view->render();
    }
}