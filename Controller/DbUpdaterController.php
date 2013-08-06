<?php

namespace AgentUpdater\Controller;

class DbUpdaterController
{

    protected $request;
    protected $config;
    protected $db;

    public function __construct($config, $request, $db)
    {
        $this->config = $config;
        $this->request = $request;
        $this->db = $db;
        $this->debug = $request->getInt("debug");
    }

    public function execute()
    {
        $array = array();
        $xmlUploaded = false;
        
        $fileDataArray = $this->request->getFileData("inputXMLFile");
        if (!empty($fileDataArray["name"])) {
            $xmlString = file_get_contents($fileDataArray["tmp_name"]);
            $xmlUploaded = true;
        }
        else if ($this->request->getRaw("inputXML") != "") {
            $xmlString = $this->request->getRaw("inputXML");
            $xmlUploaded = true;
        }
        
        if($xmlUploaded){
            $array["sent"] = true;
            $array["xmlResults"] = $this->prepareXML($xmlString);
            $array["debug"] = $this->debug;
            $array["xmlString"] = $xmlString;
        }

        return $array;
    }
    
    private function prepareXML($xmlString)
    {
        $array = array();

        $xml = simplexml_load_string($xmlString);

        $i = 1;
        foreach($xml->table as $table){
            if(!array_key_exists("name", $table) || !array_key_exists("fieldname", $table) || !array_key_exists("type", $table)){
                die("XML Fehler : Im Tabellenupdate Nr. $i fehlt eines oder mehrere Plfichtfelder ( name, fieldname, type )");
            }
            $size = $null = false;
            $tableName = $this->db->getPrefix() . $table->name;
            
            if($this->config["lwdb"]["type"] == "mysql" || $this->config["lwdb"]["type"] == "mysqli"){
                $outputStatement = new \AgentUpdater\Classes\OutputSQLStatements($this->db);
            }else{
                $outputStatement = new \AgentUpdater\Classes\OutputOracleStatements($this->db);
            }
            
            if(array_key_exists("size", $table)){
                $size = $table->size;
            }
            
            if(array_key_exists("null", $table)){
                $null = $table->null;
            }

            $statement = $outputStatement->addField($tableName, $table->fieldname, $table->type, $size, $null);
            
            $array[$tableName][] = array(
                "statement" => $statement,
                "fieldname" => $table->fieldname,
                "type"      => $table->type." ( $size )"
            );
            
            if(!$this->debug){
                if(strtolower(substr($statement, 0 , strlen("ERROR"))) != "error"){                    
                    $this->db->setStatement($statement);
                    $this->db->pdbquery();
                }
            }
            
            
            $i++;
        }
        return $array;
    }

}