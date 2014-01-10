<?php

namespace AgentUpdater\Classes;

class OutputSQLStatements
{

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function getTableStructure($table)
    {
        $this->db->setStatement("SHOW FULL FIELDS FROM $table ");
        return $this->db->pselect();
    }

    private function fieldExists($table, $name)
    {
        $erg = $this->getTableStructure($table);
        foreach ($erg as $field) {
            if ($field["Field"] == $name) {
                return true;
            }
        }
        return false;
    }

    public function addField($table, $name, $type, $size = false, $null = false)
    {
        if ($this->isTableExisting($table)) {
            if (!$this->fieldExists($table, $name)) {
                $field = $this->setField($type, $size);

                if (!$field) {
                    return "ERROR: FIELD NOT AVAILABLE";
                } else {
                    $sql = "ALTER TABLE " . $table . " ADD COLUMN " . $name . " " . $field;
                    if ($null) {
                        $sql.= " NULL ";
                    } else {
                        $sql.= " NOT NULL ";
                    }
                    return $sql;
                }
            }
            return "ERROR: FIELD EXISTING";
        }
        return "ERROR: TABLE NOT EXISTING";
    }

    private function setField($type, $size)
    {
        switch ($type) {
            case "number":
                if ($size > 11) {
                    return " bigint(" . $size . ") ";
                } else {
                    return " int(" . $size . ") ";
                }
                break;

            case "text":
                if ($size > 255) {
                    return " text ";
                } else {
                    return " varchar(" . $size . ") ";
                }
                break;

            case "clob":
                return " text ";
                break;

            case "bool":
                return " int(1) ";
                break;

            default:
                return false;
        }
    }

    private function isTableExisting($table)
    {
        $this->db->setStatement("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = :table ");

        $this->db->bindParameter("table", "s", $table);

        $result = $this->db->pselect1();

        if ($result["COUNT(*)"]) {
            return true;
        }
        return false;
    }

    public function createTable($tablename, $fields)
    {
        if ($this->isTableExisting($tablename)) {
            return "ERROR: TABLE EXISTING";
        }

        $main = "";
        $foot = "";

        $head = "CREATE TABLE IF NOT EXISTS " . $this->db->getPrefix() . $tablename . " ( ";

        foreach ($fields->field as $field) {
            $main.= $this->_buildField($field);
        }


        if ($fields->pk && strlen($fields->pk) > 0) {
            $foot = " PRIMARY KEY (" . $fields->pk . ") ";
        } else {
            $main = substr($main, 0, -2) . " ";
        }
        $foot.= ") ";

        return ($head . $main . $foot);
    }

    private function _buildField($field)
    {
        $out = "";
        $out.= ' ' . $field->attributes()->name;
        switch ($field->attributes()->type) {
            case "number":
                if ($field->attributes()->size > 11) {
                    $out.=" bigint(" . $field->attributes()->size . ") ";
                } else {
                    $out.= " int(" . $field->attributes()->size . ") ";
                }
                break;

            case "text":
                if ($field->attributes()->size > 255) {
                    $out.= " text ";
                } else {
                    $out.= " varchar(" . $field->attributes()->size . ") ";
                }
                break;

            case "clob":
                $out.= " longtext ";
                break;

            case "bool":
                $out.= " int(1) ";
                break;

            default:
                die("field not available");
        }
        if ($field->attributes()->special == 'auto_increment') {
            $out.=' auto_increment ';
        }
        return $out . ",";
    }

}
