<?php

namespace AgentUpdater\Classes;

class PrepareXML
{

    protected $config;
    protected $db;
    protected $debug = false;
    protected $request;
    protected $outputStatement;
    protected $errorOccuredUpdateNotAllowed;

    public function __construct($config, $db, $request)
    {
        $this->errorOccuredUpdateNotAllowed = false;
        $this->config = $config;
        $this->db = $db;
        $this->request = $request;

        if ($this->config["lwdb"]["type"] == "mysql" || $this->config["lwdb"]["type"] == "mysqli") {
            $this->outputStatement = new \AgentUpdater\Classes\OutputSQLStatements($this->db);
        } else {
            $this->outputStatement = new \AgentUpdater\Classes\OutputOracleStatements($this->db);
        }
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    public function getError()
    {
        return $this->errorOccuredUpdateNotAllowed;
    }

    public function fileUpdates($xmlFileUpdates)
    {
        $array = array();
        $notEveryPathIsWriteable = false;
        $notEveryMd5IsIdentical = false;

        foreach ($xmlFileUpdates->file as $file) {
            $pathPlaceholder = $this->getPathPlaceholderFromXmlPath(trim($file->path));
            $path = $this->replacePathPlaceholderWithConfigPathEntry(trim($file->path), $pathPlaceholder);
            $fileMd5 = $this->getMd5($path);

            $writeable = false;

            if (!is_file($path)) {
                $dir = substr($path, 0, strripos($path, "/") + 1);
                if (is_writable($dir)) {
                    $writeable = true;
                } else {
                    $this->errorOccuredUpdateNotAllowed = $notEveryPathIsWriteable = true;
                }
            } else {
                if (is_writable($path)) {
                    $writeable = true;
                } else {
                    $this->errorOccuredUpdateNotAllowed = $notEveryPathIsWriteable = true;
                }
            }

            $array[] = array(
                "md5" => (string) $file->md5,
                "fileMd5" => $fileMd5,
                "filePath" => $path,
                "content" => $file->content,
                "writeable" => $writeable,
                "pathPlaceholder" => $pathPlaceholder,
                "pathUsedInXml" => trim($file->path)
            );

            if ((string) $file->md5 != $fileMd5) {
                $this->errorOccuredUpdateNotAllowed = $notEveryMd5IsIdentical = true;
            }
        }

        return $array;
    }

    private function getPathPlaceholderFromXmlPath($path)
    {
        $placeholder = str_replace("[", "", str_replace("]", "", substr($path, 0, strpos($path, "]") + 1)));
        return $placeholder;
    }

    private function replacePathPlaceholderWithConfigPathEntry($path, $placeholder)
    {
        $preparedPath = str_replace("//", "", str_replace("..", "", str_replace("[" . $placeholder . "]", $this->config["path"][$placeholder], $path)));
        return $preparedPath;
    }

    private function getMd5($path)
    {
        $contentToMd5 = new \AgentUpdater\Classes\ContentToMd5();
        return $contentToMd5->getMd5FromFile($path, $this->request->getRaw("inputCharset"));
    }

    public function tableUpdates($xmlTableUpdates)
    {
        $array = array();
        $i = 1;
        foreach ($xmlTableUpdates->updateTable as $table) {
            if (!array_key_exists("name", $table) || !array_key_exists("fieldname", $table) || !array_key_exists("type", $table)) {
                die("XML Fehler : Im Tabellenupdate Nr. " . $i++ . " fehlt eines oder mehrere Plfichtfelder ( name, fieldname, type )");
            }
            $size = $null = false;
            $tableName = $this->db->getPrefix() . $table->name;


            if (array_key_exists("size", $table)) {
                $size = $table->size;
            }

            if (array_key_exists("null", $table)) {
                $null = $table->null;
                
                if($null == "false"){
                    $null = false;
                }else{
                    $null = true;
                }
            }

            $statement = $this->outputStatement->addField($tableName, $table->fieldname, $table->type, $size, $null);

            $array[$tableName][] = array(
                "statement" => $statement,
                "fieldname" => $table->fieldname,
                "type" => $table->type . " ( $size )"
            );

            if (strtolower(substr($statement, 0, strlen("ERROR"))) == "error") {
                $this->errorOccuredUpdateNotAllowed = true;
            }
        }
        return $array;
    }

    public function tableCreates($xmlTableUpdates)
    {
        $array = array();

        foreach ($xmlTableUpdates->createTable as $table) {
            $statement = $this->outputStatement->createTable($table->attributes()->name, $table->fields);
            $array[(string) $table->attributes()->name] = array("statement" => $statement);

            if (strtolower(substr($statement, 0, strlen("ERROR"))) == "error") {
                $this->errorOccuredUpdateNotAllowed = true;
            }
        }
        return $array;
    }

}
