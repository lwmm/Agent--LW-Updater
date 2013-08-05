<?php

namespace AgentUpdater\Views;

class MainContainer
{
    protected $config;
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function render($content, $module)
    {
        $view = new \lw_view(dirname(__FILE__) . '/Templates/MainContainer.phtml');
        $view->bootstrapCSS = $this->config["url"]["media"] . "bootstrap/css/bootstrap.min.css";
        $view->bootstrapJS = $this->config["url"]["media"] . "bootstrap/js/bootstrap.min.js";
        
        $view->content = $content;
        $view->module = $module;
               
        return $view->render();
    }
}