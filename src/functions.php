<?php
/* ===========================================================================
 * Opis Project
 * http://opis.io
 * ===========================================================================
 * Copyright 2016 Marius Sarca
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

if(!function_exists('wstring'))
{
    /**
     * @param $string
     * @return wstring
     * @throws Exception
     */
    function wstring($string)
    {
        static $ord, $chr;

        if($string instanceof wstring){
            return $string;
        }

        $codes = $chars = array();

        if(false === $text = json_encode((string) $string)) {
            throw new Exception("Invalid UTF-8 string");
        }

        if($ord === null || $chr === null){
            $ord = require __DIR__ .'/../res/ord.php';
            $chr = require __DIR__ . '/../res/char.php';
        }

        for($i = 1, $l = strlen($text) - 1; $i < $l; $i++) {
            $c = $text[$i];

            if($c === '\\'){
                if(isset($text[$i + 1])){

                    if($text[$i + 1] === 'u'){

                        $codes[] = $cp = hexdec(substr($text, $i, 6));

                        if ($cp < 0x80) {
                            $chars[] = $chr[$cp];
                        } elseif ($cp <= 0x7FF) {
                            $chars[] = $chr[($cp >> 6) + 0xC0] . $chr[($cp & 0x3F) + 0x80];
                        } elseif ($cp <= 0xFFFF) {
                            $chars[] = $chr[($cp >> 12) + 0xE0] . $chr[(($cp >> 6) & 0x3F) + 0x80] . $chr[($cp & 0x3F) + 0x80];
                        } elseif ($cp <= 0x10FFFF) {
                            $chars[] = $chr[($cp >> 18) + 0xF0] . $chr[(($cp >> 12) & 0x3F) + 0x80]
                                . $chr[(($cp >> 6) & 0x3F) + 0x80] . $chr[($cp & 0x3F) + 0x80];
                        } else {
                            throw new Exception("Invalid UTF-8");
                        }

                        $i += 5;
                        continue;

                    } else{

                        switch ($text[$i + 1]){
                            case '\\':
                                $c = "\\";
                                break;
                            case '\'':
                                $c = "'";
                                break;
                            case '"':
                                $c = '"';
                                break;
                            case 'n':
                                $c = "\n";
                                break;
                            case 'r':
                                $c = "\r";
                                break;
                            case 't':
                                $c = "\t";
                                break;
                            case 'b':
                                $c = "\b";
                                break;
                            case 'f':
                                $c = "\f";
                                break;
                            case 'v':
                                $c = "\v";
                                break;
                            case '0':
                                $c = "\0";
                                break;
                            default:
                                $c = $text[$i + 1];
                        }

                        $codes[] = $ord[$c];
                        $chars[] = $c;
                        $i++;
                        continue;
                    }
                }
            }

            $codes[] = $ord[$c];
            $chars[] = $c;
        }

        return new wstring($codes, $chars);
    }
}