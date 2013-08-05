<?php

namespace AgentUpdater\Views;

class Navigation
{
    public function render()
    {
        $view = new \lw_view(dirname(__FILE__) . '/Templates/Navigation.phtml');
        return $view->render();
    }
}