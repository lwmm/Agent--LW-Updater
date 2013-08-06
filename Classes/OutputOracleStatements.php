<?php

namespace AgentUpdater\Classes;

class OutputOracleStatements
{

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function fieldExists($table, $name)
    {
        $this->db->setStatement("SELECT table_name, column_name FROM user_tab_columns WHERE UPPER(table_name) = UPPER(' :table ') AND UPPER(column_name) = UPPER(' :name ') ");
        $this->db->bindParameter("table", "s", $table);
        $this->db->bindParameter("name", "s", $name);
        $erg = $this->db->pselect();

        if ($erg["column_name"] == strtoupper($name) && strlen(trim($name)) > 0)
            return true;
        return false;
    }

    public function addField($table, $name, $type, $size = false, $null = false)
    {
        if ($this->db->tableExists($table)) {
            if (!$this->fieldExists($table, $name)) {
                $field = $this->setField($type, $size);

                if (!$field) {
                    return "ERROR: FIELD NOT AVAILABLE";
                }

                $sql = "ALTER TABLE " . $table . " ADD (" . strtoupper($name) . " " . $field;
                if ($null) {
                    $sql.= " NULL ";
                }
                else {
                    $sql.= " NOT NULL ";
                }
                $sql.=")";

                return $sql;
            }
            return "ERROR: FIELD EXISTING";
        }
        return "ERROR: TABLE NOT EXISTING";
    }

    private function setField($type, $size)
    {
        switch ($type) {
            case "number":
                return " NUMBER(" . $size . ") ";
                break;

            case "text":
                return " VARCHAR2(" . $size . ") ";
                break;

            case "clob":
                return " CLOB ";
                break;

            case "bool":
                return " NUMBER(1) ";
                break;

            default:
                return false;
        }
    }

}