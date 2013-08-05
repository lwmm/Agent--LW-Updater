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
        #$xml = simplexml_load_file($this->config["path"]["resource"] . "test.xml");
        #die($xml->Kategorie[0]->Name);
        #die($xml->name[0]["id"]);
        #die($xml->test[0]->name->attributes()->id."");
        #die($xml->test[0]->content);
        #die($xml->file[0]->md5);
        #foreach($xml->file as $file){
        #    $str.= $file->path."<br>";
        #}
        #die($str);
        #print_r($array);
        #die("HIER");
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