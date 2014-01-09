<?php

namespace AgentUpdater\Views;

class Md5Checker
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function render($array)
    {
        $view = new \lw_view(dirname(__FILE__) . '/Templates/Md5Checker.phtml');
        $view->actionUrl = $this->config["url"]["client"] . "admin.php?obj=updater&module=md5checker";

        $view->paths = $this->config["path"];
        $view->array = $array;

        return $view->render();
    }

}
