<?php

namespace AgentUpdater\Classes;

class PrepareXML
{
    protected $config;
    protected $db;
    protected $debug = false;


    public function __construct($config, $db)
    {
        $this->config = $config;
        $this->db = $db;
    }
    
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
    
    public function fileUpdates($xmlFileUpdates)
    {
        $array = array();
        
        foreach ($xmlFileUpdates->file as $file) {
            $path = $this->replacePathPlaceholderWithConfigPathEntry($file->path);
            $fileMd5 = $this->getMd5($path);
            $array[] = array(
                "md5"   => (string)$file->md5,
                "fileMd5"   => $fileMd5,
                "filePath"  => $path
            );
               
            if(!$this->debug){
                if($file->md5 == $fileMd5){
                    $charsetConverter = new \AgentUpdater\Classes\CharsetConverter();
                    $content = $charsetConverter->execute($this->request->getAlnum("inputCharset"), $file->content);
                    
                    $fileUpdater = new \AgentUpdater\Classes\UpdateFile();
                    $fileUpdater->execute($path, $content);
                }
            }
        }
        
        return $array;
    }
    
    public function tableUpdates($xmlTableUpdates)
    {
        $array = array();
        $i = 1;
        
        foreach($xmlTableUpdates->table as $table){
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
    
    
    private function replacePathPlaceholderWithConfigPathEntry($path)
    {
        $plaeholder = str_replace("[", "", str_replace("]", "", substr($path, 0, strpos($path, "]") + 1)));
        $preparedPath = str_replace("//", "", str_replace("..", "", str_replace("[" . $plaeholder . "]", $this->config["path"][$plaeholder], $path)));

        return $preparedPath;
    }

    private function getMd5($path)
    {
        $contentToMd5 = new \AgentUpdater\Classes\ContentToMd5();
        return $contentToMd5->getMd5FromFile($path);
    }
}