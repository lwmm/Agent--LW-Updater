<?php

namespace AgentUpdater\Views;

class DbUpdater
{
    protected $config;
    
    public function __construct($config)
    {
        $this->config = $config;
    }


    public function render($array)
    {
        $view = new \lw_view(dirname(__FILE__) . '/Templates/DbUpdater.phtml');
        $view->actionUrl = $this->config["url"]["client"] . "admin.php?obj=updater&module=dbupdater";
        
        $view->sent = $array["sent"];
        $view->xmlResults = $array["xmlResults"];
        $view->xmlString = $array["xmlString"];
        $view->debug = $array["debug"];
        
        return $view->render();
    }
}