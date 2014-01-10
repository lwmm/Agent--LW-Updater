<?php

namespace AgentUpdater\Controller;

class SystemUpdaterController
{

    protected $request;
    protected $config;
    protected $debug;
    protected $db;

    public function __construct($config, $request, $db)
    {
        $this->config = $config;
        $this->request = $request;
        $this->debug = $request->getInt("debug");
        $this->db = $db;
    }

    public function execute()
    {
        $array = array();
        $xmlUploaded = false;

        $fileDataArray = $this->request->getFileData("inputXMLFile");
        if (!empty($fileDataArray["name"])) {
            $xmlString = file_get_contents($fileDataArray["tmp_name"]);
            $xmlUploaded = true;
        } else if ($this->request->getRaw("inputXML") != "") {
            $xmlString = $this->request->getRaw("inputXML");
            $xmlUploaded = true;
        }

        if ($xmlUploaded) {
            $array["sent"] = true;
            $array["xmlResults"] = $this->prepareXML($xmlString);
            $array["xmlString"] = $xmlString;
            $array["debug"] = $this->debug;
            $array["charset"] = $this->request->getRaw("inputCharset");
        }

        return $array;
    }

    private function prepareXML($xmlString)
    {
        $array = array();

        $xml = simplexml_load_string($xmlString);

        $prepareXMLClass = new \AgentUpdater\Classes\PrepareXML($this->config, $this->db, $this->request);
        $prepareXMLClass->setDebug($this->debug);

        $array["fileUpdates"] = $prepareXMLClass->fileUpdates($xml->FileUpdates);
        $array["tableCreates"] = $prepareXMLClass->tableCreates($xml->TableUpdates->create);
        $array["tableUpdates"] = $prepareXMLClass->tableUpdates($xml->TableUpdates->update);
        $array["error"] = $prepareXMLClass->getError();
        
        #print_r($array);die();

        if (!$this->debug && !$array["error"]) {
            $fileUpdater = new \AgentUpdater\Model\FileUpdates($this->config, $this->request);
            $fileUpdater->execute($array["fileUpdates"]);
            
            $tableUpdater = new \AgentUpdater\Model\TableUpdates($this->config, $this->db);
            $tableUpdater->tableUpdate($array["tableUpdates"]);
            $tableUpdater->tableCreate($array["tableCreates"]);
        }

        return $array;
    }

}
