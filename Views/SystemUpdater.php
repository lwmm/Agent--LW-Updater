<?php

namespace AgentUpdater\Views;

class SystemUpdater
{

    public function render($array)
    {
        $view = new \lw_view(dirname(__FILE__) . '/Templates/SystemUpdater.phtml');
        $view->actionUrl = $this->config["url"]["client"] . "admin.php?obj=updater&module=systemupdater";

        $view->sent = $array["sent"];
        $view->xmlResults = $array["xmlResults"];
        $view->xmlString = $array["xmlString"];
        $view->debug = $array["debug"];
        $view->charset = $array["charset"];

        return $view->render();
    }

}
