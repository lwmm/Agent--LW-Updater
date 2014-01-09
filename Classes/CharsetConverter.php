<?php

namespace AgentUpdater\Classes;

class CharsetConverter
{

    public function execute($targetCharset, $content)
    {
        if ($this->isUtf8($content)) {
            if ($targetCharset == "UTF-8") {
                return $content;
            } else {
                return mb_convert_encoding($content, "ISO-8859-15", "UTF-8");
            }
        } else {
            if ($targetCharset == "UTF-8") {
                return utf8_encode($content);
            } else {
                return mb_convert_encoding(utf8_encode($content), "ISO-8859-15", "UTF-8");
            }
        }
    }

    private function isUtf8($str)
    {
        $strlen = strlen($str);
        for ($i = 0; $i < $strlen; $i++) {
            $ord = ord($str[$i]);
            if ($ord < 0x80) { // 0bbbbbbb
                continue;
            } elseif (($ord & 0xE0) === 0xC0 && $ord > 0xC1) { // 110bbbbb (exkl C0-C1)
                $n = 1;
            } elseif (($ord & 0xF0) === 0xE0) { // 1110bbbb
                $n = 2;
            } elseif (($ord & 0xF8) === 0xF0 && $ord < 0xF5) { // 11110bbb (exkl F5-FF)
                $n = 3;
            } else { // ungültiges UTF-8-Zeichen
                return false;
            }

            for ($c = 0; $c < $n; $c++) { // $n Folgebytes? // 10bbbbbb
                if (++$i === $strlen || (ord($str[$i]) & 0xC0) !== 0x80) { // ungültiges UTF-8-Zeichen
                    return false;
                }
            }
        }
        return true; // kein ungültiges UTF-8-Zeichen gefunden
    }

}
