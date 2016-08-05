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

namespace Opis\String;

use ArrayAccess;
use Exception;

class UTF8String implements ArrayAccess
{
    const CACHE_IS_LOWER = 0;

    const CACHE_IS_UPPER = 1;

    const CACHE_TO_LOWER = 2;

    const CACHE_TO_UPPER = 3;

    const CACHE_IS_ASCII = 4;

    const CACHE_TO_ASCII = 5;

    /** @var array  */
    protected $codes;

    /** @var array  */
    protected $chars;

    /** @var string|null */
    protected $string;

    /** @var int */
    protected $length;

    /** @var array */
    protected $cache = array();

    public function __construct(array $codes, array $chars)
    {
        $this->codes = $codes;
        $this->chars = $chars;
        $this->length = count($codes);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->codes[$offset]);
    }

    /**
     * @param mixed $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->chars[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception("Invalid operation");
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception("Invalid operation");
    }

    /**
     * @return array
     */
    public function chars()
    {
        return $this->chars;
    }

    /**
     * @return array
     */
    public function codePoints()
    {
        return $this->codes;
    }

    /**
     * @return int
     */
    public function length()
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->length === 0;
    }

    /**
     * @param string|UTF8String $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function equals($text, $ignoreCase = false)
    {
        $text = static::from($text);

        if($this->length !== $text->length){
            return false;
        }

        if($ignoreCase){
            return $this->toLower()->equals($text->toLower());
        }

        for($i = 0, $l = $this->length; $i < $l; $i++){
            if($this->codes[$i] !== $text->codes[$i]){
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|UTF8String $text
     * @param bool $ignoreCase
     * @return int
     */
    public function compareTo($text, $ignoreCase = false)
    {
        $text = static::from($text);

        if($this->length !== $text->length){
            return $this->length > $text->length ? 1 : -1;
        }

        if($ignoreCase){
            return $this->toLower()->compareTo($text->toLower());
        }

        for($i = 0, $l = $this->length; $i < $l; $i++){
            if($this->codes[$i] !== $text->codes[$i]){
                return $this->codes[$i] > $text->codes[$i] ? 1 : -1;
            }
        }

        return 0;
    }

    /**
     * @param string|UTF8String $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function contains($text, $ignoreCase = false)
    {
        return $this->indexOf($text, 0, $ignoreCase) !== false;
    }

    /**
     * @param string|UTF8String $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function startsWith($text, $ignoreCase = false)
    {
        return $this->indexOf($text, 0, $ignoreCase) === 0;
    }

    /**
     * @param string|UTF8String $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function endsWith($text, $ignoreCase = false)
    {
        $text = static::from($text);

        $offset = $this->length - $text->length;

        if($offset < 0){
            return false;
        }

        return $this->indexOf($text, $offset, $ignoreCase) === $offset;
    }

    /**
     * @param string|UTF8String $text
     * @param int $offset
     * @param bool $ignoreCase
     * @return int|false
     * @throws Exception
     */
    public function indexOf($text, $offset = 0, $ignoreCase = false)
    {
        $text = static::from($text);

        if($ignoreCase){
            return $this->toLower()->indexOf($text->toLower(), $offset);
        }

        if($offset < 0){
            $offset = 0;
        }

        $cp1 = $this->codes;
        $cp2 = $text->codes;

        for($i = $offset, $l = $this->length - $text->length; $i <= $l; $i++){
            $match = true;

            for($j = 0, $f = $text->length; $j < $f; $j++){
                if($cp1[$i + $j] != $cp2[$j]){
                    $match = false;
                    break;
                }
            }

            if($match){
                return $i;
            }
        }

        return false;
    }

    /**
     * @param UTF8String|string $text
     * @param bool $ignoreCase
     * @return false|int
     */
    public function lastIndexOf($text, $ignoreCase = false)
    {
        $text = static::from($text);

        if($ignoreCase){
            return $this->toLower()->lastIndexOf($text->toLower());
        }

        $index = false;
        $offset = 0;

        while(true){
            if(false === $offset = $this->indexOf($text, $offset)){
                break;
            }
            $index = $offset;
            $offset += $text->length;
        }

        return $index;
    }

    /**
     * @param UTF8String|string $text
     * @return UTF8String
     */
    public function append($text)
    {
        $text = static::from($text);
        $cp = array_merge($this->codes, $text->codes);
        $ch = array_merge($this->chars, $text->chars);

        return new static($cp, $ch);
    }

    /**
     * @param UTF8String|string $text
     * @return UTF8String
     */
    public function prepend($text)
    {
        $text = static::from($text);
        $cp = array_merge($text->codes, $this->codes);
        $ch = array_merge($text->chars, $this->chars);

        return new static($cp, $ch);
    }

    /**
     * @param string|UTF8String $character_mask
     * @return UTF8String
     */
    public function trim($character_mask = " \t\n\r\0\x0B")
    {
        return $this->doTrim($character_mask);
    }

    /**
     * @param string|UTF8String $character_mask
     * @return UTF8String
     */
    public function ltrim($character_mask = " \t\n\r\0\x0B")
    {
        return $this->doTrim($character_mask, true, false);
    }

    /**
     * @param string|UTF8String $character_mask
     * @return UTF8String
     */
    public function rtrim($character_mask = " \t\n\r\0\x0B")
    {
        return $this->doTrim($character_mask, false, true);
    }

    /**
     * @param string|UTF8String $subject
     * @param string|UTF8String $replace
     * @param int $offset
     * @return UTF8String
     * @throws Exception
     */
    public function replace($subject, $replace, $offset = 0)
    {
        $subject = static::from($subject);
        $replace = static::from($replace);

        if(false === $pos = $this->indexOf($subject, $offset)){
            return clone $this;
        }

        $cp1 = array_slice($this->codes, 0, $pos);
        $cp2 = array_slice($this->codes, $pos + $subject->length);
        $ch1 = array_slice($this->chars, 0, $pos);
        $ch2 = array_slice($this->chars, $pos + $subject->length);

        $cp = array_merge($cp1, $replace->codes, $cp2);
        $ch = array_merge($ch1, $replace->chars, $ch2);

        return new static($cp, $ch);
    }

    /**
     * @param string|UTF8String $subject
     * @param string|UTF8String $replace
     * @return UTF8String
     */
    public function replaceAll($subject, $replace)
    {
        $subject = static::from($subject);
        $replace = static::from($replace);

        if(false === $offset = $this->indexOf($subject) || $subject->isEmpty()){
            return clone $this;
        }

        $text = $this;

        do{
            $text = $text->replace($subject, $replace, $offset);
            $offset = $text->indexOf($subject, $offset + $replace->length);
        } while($offset !== false);

        return $text;
    }

    /**
     * @return UTF8String
     */
    public function reverse()
    {
        $cp = array_reverse($this->codes);
        $ch = array_reverse($this->chars);

        return new static($cp, $ch);
    }

    /**
     * @param string|UTF8String $char
     * @return array
     */
    public function split($char = ' ')
    {
        $char = static::from($char);
        $results = array();

        if($char->isEmpty()){
            for($i = 0, $l = $this->length; $i < $l; $i++){
                $results[] = new static(array($this->codes[$i]), array($this->chars[$i]));
            }
            return $results;
        }

        if(false === $offset = $this->indexOf($char)){
            return array(clone $this);
        }

        $start = 0;
        do{
            $cp = array_slice($this->codes, $start, $offset - $start);
            $ch = array_slice($this->chars, $start, $offset - $start);
            $results[] = new static($cp, $ch);
            $start = $offset + $char->length;
            $offset = $this->indexOf($char, $start);
        } while ($offset !== false);

        $cp = array_slice($this->codes, $start);
        $ch = array_slice($this->chars, $start);
        $results[] = new static($cp, $ch);
        return $results;
    }

    /**
     * @param int $start
     * @param int|null $length
     * @return UTF8String
     */
    public function substring($start, $length = null)
    {
        $cp = array_slice($this->codes, $start, $length);
        $ch = array_slice($this->chars, $start, $length);

        return new static($cp, $ch);
    }

    /**
     * @return bool
     */
    public function isLowerCase()
    {
        if(!isset($this->cache[static::CACHE_IS_LOWER])){
            $this->cache[static::CACHE_IS_LOWER] = $this->isCase($this->getLowerMap());
        }

        return $this->cache[static::CACHE_IS_LOWER];
    }

    /**
     * @return bool
     */
    public function isUpperCase()
    {
        if(!isset($this->cache[static::CACHE_IS_UPPER])){
            $this->cache[static::CACHE_IS_UPPER] = $this->isCase($this->getUpperMap());
        }

        return $this->cache[static::CACHE_IS_UPPER];
    }

    /**
     * @return bool
     */
    public function isAscii()
    {
        if(!isset($this->cache[static::CACHE_IS_ASCII])){
            foreach ($this->codes as $code){
                if($code >= 0x80){
                    return $this->cache[static::CACHE_IS_ASCII] = false;
                }
            }
            return $this->cache[static::CACHE_IS_ASCII] = true;
        }

        return $this->cache[static::CACHE_IS_ASCII];
    }

    /**
     * @return UTF8String
     */
    public function toAscii()
    {

        if(!isset($this->cache[static::CACHE_TO_ASCII])){
            if(isset($this->cache[static::CACHE_IS_ASCII]) && $this->cache[static::CACHE_IS_ASCII]){
                $this->cache[static::CACHE_TO_ASCII] = clone $this;
            } else {
                $ascii = $this->getAsciiMap();
                $char = $this->getCharMap();
                $ch = array();
                $cp = array();

                foreach ($this->codes as $code){
                    if(isset($ascii[$code])){
                        $cp[] = $c = $ascii[$code];
                        $ch[] = $char[$c];
                    }
                }

                $instance = new static($cp, $ch);
                $instance->cache[static::CACHE_IS_ASCII] = true;

                $keys = array(static::CACHE_IS_UPPER, static::CACHE_IS_LOWER);

                foreach ($keys as $key){
                    if(isset($this->cache[$key])){
                        $instance->cache[$key] = $this->cache[$key];
                    }
                }

                $this->cache[static::CACHE_TO_ASCII] = $instance;
            }
        }

        return $this->cache[static::CACHE_TO_ASCII];
    }

    /**
     * @return UTF8String
     */
    public function toLower()
    {
        if(!isset($this->cache[static::CACHE_TO_LOWER])){
            if(isset($this->cache[static::CACHE_IS_LOWER]) && $this->cache[static::CACHE_IS_LOWER]){
                $this->cache[static::CACHE_TO_LOWER] = clone $this;
            } else {
                $this->cache[static::CACHE_TO_LOWER] = $this->toCase($this->getLowerMap(), static::CACHE_IS_LOWER);
            }
        }

        return $this->cache[static::CACHE_TO_LOWER];
    }

    /**
     * @return UTF8String
     */
    public function toUpper()
    {
        if(!isset($this->cache[static::CACHE_TO_UPPER])){
            if(isset($this->cache[static::CACHE_IS_UPPER]) && $this->cache[static::CACHE_IS_UPPER]){
                $this->cache[static::CACHE_TO_UPPER] = clone $this;
            } else {
                $this->cache[static::CACHE_TO_UPPER] = $this->toCase($this->getUpperMap(), static::CACHE_IS_UPPER);
            }
        }

        return $this->cache[static::CACHE_TO_UPPER];
    }

    /**
     * @param int $offset
     * @return int
     */
    public function __invoke($offset)
    {
        return $this->codes[$offset];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if($this->string === null){
            $this->string = implode('', $this->chars);
        }

        return $this->string;
    }

    /**
     * @param $string
     * @return UTF8String
     * @throws Exception
     */
    public static function from($string)
    {
        static $ord, $chr;

        if($string instanceof self){
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

        return new static($codes, $chars);
    }

    /**
     * @param $character_mask
     * @param bool $left
     * @param bool $right
     * @return UTF8String
     * @throws Exception
     */
    protected function doTrim($character_mask, $left = true, $right = true)
    {
        $character_mask = static::from($character_mask);

        $cm = $character_mask->codes;
        $cp = $this->codes;
        $start = 0;
        $end = $this->length;

        if($left){
            for ($i = 0; $i < $this->length; $i++) {
                if (!in_array($cp[$i], $cm)) {
                    break;
                }
            }
            $start = $i;
        }

        if($right){
            for ($i = $this->length - 1; $i > $start; $i--) {
                if (!in_array($cp[$i], $cm)) {
                    break;
                }
            }
            $end = $i + 1;
        }

        $cp = array_slice($cp, $start, $end - $start);
        $ch = array_slice($this->chars, $start, $end - $start);

        return new static($cp, $ch);
    }

    /**
     * @param array $map
     * @param int $cacheKey
     * @return UTF8String
     */
    protected function toCase(array $map, $cacheKey)
    {
        $cp = $this->codes;
        $ch = $this->chars;
        $ocp = $och = array();

        for($i = 0, $l = $this->length; $i < $l; $i++){
            $p = $cp[$i];
            if(isset($map[$p])){
                $v = $map[$p];
                $ocp[] = $v[0];
                $och[] = $v[1];
            } else {
                $ocp[] = $p;
                $och[] = $ch[$i];
            }
        }

        $str = new static($ocp, $och);
        $str->cache[$cacheKey] = true;

        return $str;
    }

    /**
     * @param array $map
     * @return bool
     */
    protected function isCase(array $map)
    {
        foreach ($this->codes as $cp){
            if(isset($map[$cp])){
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getUpperMap()
    {
        static $upper;

        if($upper === null){
            $upper = require __DIR__ . '/../res/upper.php';
        }

        return $upper;
    }

    /**
     * @return array
     */
    protected function getLowerMap()
    {
        static $lower;

        if($lower === null){
            $lower = require __DIR__ . '/../res/lower.php';
        }

        return $lower;
    }

    /**
     * @return mixed
     */
    protected function getAsciiMap()
    {
        static $ascii;

        if($ascii === null){
            $ascii = require __DIR__ . '/../res/ascii.php';
        }

        return $ascii;
    }

    protected function getCharMap()
    {
        static $char;

        if($char === null){
            $char = require __DIR__ . '/../res/char.php';
        }

        return $char;
    }

}
