<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
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
use RuntimeException;

class UnicodeString implements ArrayAccess
{
    const CACHE_IS_LOWER = 0;

    const CACHE_IS_UPPER = 1;

    const CACHE_TO_LOWER = 2;

    const CACHE_TO_UPPER = 3;

    const CACHE_IS_ASCII = 4;

    const CACHE_TO_ASCII = 5;

    /** @var array */
    protected $codes;

    /** @var array */
    protected $chars;

    /** @var string|null */
    protected $string;

    /** @var int */
    protected $length;

    /** @var array */
    protected $cache = [];

    /**
     * @param array $codes
     * @param array $chars
     */
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
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException("Invalid operation");
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw new RuntimeException("Invalid operation");
    }

    /**
     * @return string[]
     */
    public function chars(): array
    {
        return $this->chars;
    }

    /**
     * @return int[]
     */
    public function codePoints(): array
    {
        return $this->codes;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return $this->length;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->length === 0;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function equals($text, bool $ignoreCase = false): bool
    {
        $text = static::from($text);

        if ($ignoreCase) {
            return $this->toLower()->equals($text->toLower());
        }

        return $this->codes === $text->codes;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return int
     */
    public function compareTo($text, bool $ignoreCase = false): int
    {
        $text = static::from($text);

        if ($this->length !== $text->length) {
            return $this->length > $text->length ? 1 : -1;
        }

        if ($ignoreCase) {
            return $this->toLower()->compareTo($text->toLower());
        }

        for ($i = 0, $l = $this->length; $i < $l; $i++) {
            if ($this->codes[$i] !== $text->codes[$i]) {
                return $this->codes[$i] > $text->codes[$i] ? 1 : -1;
            }
        }

        return 0;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function contains($text, bool $ignoreCase = false): bool
    {
        return $this->indexOf($text, 0, $ignoreCase) !== false;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function startsWith($text, bool $ignoreCase = false): bool
    {
        return $this->indexOf($text, 0, $ignoreCase) === 0;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return bool
     */
    public function endsWith($text, bool $ignoreCase = false): bool
    {
        $text = static::from($text);

        $offset = $this->length - $text->length;

        if ($offset < 0) {
            return false;
        }

        return $this->indexOf($text, $offset, $ignoreCase) === $offset;
    }

    /**
     * @param string|UnicodeString $text
     * @param int $offset
     * @param bool $ignoreCase
     * @return int|false
     */
    public function indexOf($text, int $offset = 0, bool $ignoreCase = false)
    {
        $text = static::from($text);

        if ($this->length < $text->length) {
            return false;
        }

        if ($ignoreCase) {
            return $this->toLower()->indexOf($text->toLower(), $offset);
        }

        if ($offset < 0) {
            $offset = 0;
        }

        $cp1 = $this->codes;
        $cp2 = $text->codes;

        for ($i = $offset, $l = $this->length - $text->length; $i <= $l; $i++) {
            $match = true;

            for ($j = 0, $f = $text->length; $j < $f; $j++) {
                if ($cp1[$i + $j] != $cp2[$j]) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                return $i;
            }
        }

        return false;
    }

    /**
     * @param UnicodeString|string $text
     * @param bool $ignoreCase
     * @return false|int
     */
    public function lastIndexOf($text, bool $ignoreCase = false)
    {
        $text = static::from($text);

        if ($this->length < $text->length) {
            return false;
        }

        if ($ignoreCase) {
            return $this->toLower()->lastIndexOf($text->toLower());
        }

        $index = false;
        $offset = 0;

        while (true) {
            if (false === $offset = $this->indexOf($text, $offset)) {
                break;
            }
            $index = $offset;
            $offset += $text->length;
        }

        return $index;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return UnicodeString
     */
    public function ensurePrefix($text, bool $ignoreCase = false): self
    {
        $text = static::from($text);

        if (!$this->startsWith($text, $ignoreCase)) {
            $cp = array_merge($text->codes, $this->codes);
            $ch = array_merge($text->chars, $this->chars);

            return new static($cp, $ch);
        }

        return clone $this;
    }

    /**
     * @param string|UnicodeString $text
     * @param bool $ignoreCase
     * @return UnicodeString
     */
    public function ensureSuffix($text, bool $ignoreCase = false): self
    {
        $text = static::from($text);

        if (!$this->endsWith($text, $ignoreCase)) {
            $cp = array_merge($this->codes, $text->codes);
            $ch = array_merge($this->chars, $text->chars);

            return new static($cp, $ch);
        }

        return clone $this;
    }

    /**
     * @param UnicodeString|string $text
     * @return UnicodeString
     */
    public function append($text): self
    {
        $text = static::from($text);
        $cp = array_merge($this->codes, $text->codes);
        $ch = array_merge($this->chars, $text->chars);

        return new static($cp, $ch);
    }

    /**
     * @param UnicodeString|string $text
     * @return UnicodeString
     */
    public function prepend($text): self
    {
        $text = static::from($text);
        $cp = array_merge($text->codes, $this->codes);
        $ch = array_merge($text->chars, $this->chars);

        return new static($cp, $ch);
    }

    /**
     * @param string|UnicodeString $text
     * @param int $index
     * @return UnicodeString
     */
    public function insert($text, int $index): self
    {
        if ($index <= 0) {
            return $this->prepend($text);
        }

        if ($index >= $this->length) {
            return $this->append($text);
        }

        $text = static::from($text);

        $lcp = array_slice($this->codes, 0, $index);
        $lch = array_slice($this->chars, 0, $index);

        $rcp = array_slice($this->codes, $index);
        $rch = array_slice($this->chars, $index);

        $cp = array_merge($lcp, $text->codes, $rcp);
        $ch = array_merge($lch, $text->chars, $rch);

        return new static($cp, $ch);
    }

    /**
     * @param string|UnicodeString $character_mask
     * @return UnicodeString
     */
    public function trim($character_mask = " \t\n\r\0\x0B"): self
    {
        return $this->doTrim($character_mask);
    }

    /**
     * @param string|UnicodeString $character_mask
     * @return UnicodeString
     */
    public function trimLeft($character_mask = " \t\n\r\0\x0B"): self
    {
        return $this->doTrim($character_mask, true, false);
    }

    /**
     * @param string|UnicodeString $character_mask
     * @return UnicodeString
     */
    public function trimRight($character_mask = " \t\n\r\0\x0B"): self
    {
        return $this->doTrim($character_mask, false, true);
    }

    /**
     * @param string|UnicodeString $subject
     * @param string|UnicodeString $replace
     * @param int $offset
     * @return UnicodeString
     */
    public function replace($subject, $replace, int $offset = 0): self
    {
        $subject = static::from($subject);
        $replace = static::from($replace);

        if (false === $pos = $this->indexOf($subject, $offset)) {
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
     * @param string|UnicodeString $subject
     * @param string|UnicodeString $replace
     * @return UnicodeString
     */
    public function replaceAll($subject, $replace): self
    {
        $subject = static::from($subject);
        $replace = static::from($replace);

        if (false === $offset = $this->indexOf($subject) || $subject->isEmpty()) {
            return clone $this;
        }

        $text = $this;

        do {
            $text = $text->replace($subject, $replace, $offset);
            $offset = $text->indexOf($subject, $offset + $replace->length);
        } while ($offset !== false);

        return $text;
    }

    /**
     * @return UnicodeString
     */
    public function reverse(): self
    {
        $cp = array_reverse($this->codes);
        $ch = array_reverse($this->chars);

        return new static($cp, $ch);
    }


    /**
     * @param int $times
     * @return UnicodeString
     */
    public function repeat(int $times = 1): self
    {
        if ($times < 1) {
            $times = 1;
        }

        $cp = $this->codes;
        $ch = $this->chars;

        for ($i = 0; $i < $times; $i++) {
            $cp = array_merge($cp, $this->codes);
            $ch = array_merge($ch, $this->chars);
        }

        return new static($cp, $ch);
    }

    /**
     * @param int $index
     * @param int $length
     * @return UnicodeString
     */
    public function remove(int $index, int $length): self
    {
        if ($index < 0) {
            $index = 0;
        }

        if ($length < 0) {
            $length = 0;
        }

        $lcp = array_slice($this->codes, 0, $index);
        $lch = array_slice($this->chars, 0, $index);
        $rcp = array_slice($this->codes, $index + $length);
        $rch = array_slice($this->chars, $index + $length);

        $cp = array_merge($lcp, $rcp);
        $ch = array_merge($lch, $rch);

        return new static($cp, $ch);
    }

    /**
     * @param string|UnicodeString $char
     * @return UnicodeString[]
     */
    public function split($char = ''): array
    {
        $char = static::from($char);
        $results = [];

        if ($char->isEmpty()) {
            for ($i = 0, $l = $this->length; $i < $l; $i++) {
                $results[] = new static([$this->codes[$i]], [$this->chars[$i]]);
            }
            return $results;
        }

        if (false === $offset = $this->indexOf($char)) {
            return [clone $this];
        }

        $start = 0;
        do {
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
     * @return UnicodeString
     */
    public function substring(int $start, int $length = null): self
    {
        $cp = array_slice($this->codes, $start, $length);
        $ch = array_slice($this->chars, $start, $length);

        return new static($cp, $ch);
    }

    /**
     * @param int $length
     * @param string|UnicodeString $char
     * @return UnicodeString
     */
    public function padLeft(int $length, $char = ' '): self
    {
        return $this->doPad($length, $char, true);
    }

    /**
     * @param int $length
     * @param string|UnicodeString $char
     * @return UnicodeString
     */
    public function padRight($length, $char = ' '): self
    {
        return $this->doPad($length, $char, false);
    }

    /**
     * @return bool
     */
    public function isLowerCase(): bool
    {
        if (!isset($this->cache[static::CACHE_IS_LOWER])) {
            $this->cache[static::CACHE_IS_LOWER] = $this->isCase($this->getLowerMap());
        }

        return $this->cache[static::CACHE_IS_LOWER];
    }

    /**
     * @return bool
     */
    public function isUpperCase(): bool
    {
        if (!isset($this->cache[static::CACHE_IS_UPPER])) {
            $this->cache[static::CACHE_IS_UPPER] = $this->isCase($this->getUpperMap());
        }

        return $this->cache[static::CACHE_IS_UPPER];
    }

    /**
     * @return bool
     */
    public function isAscii(): bool
    {
        if (!isset($this->cache[static::CACHE_IS_ASCII])) {
            foreach ($this->codes as $code) {
                if ($code >= 0x80) {
                    return $this->cache[static::CACHE_IS_ASCII] = false;
                }
            }
            return $this->cache[static::CACHE_IS_ASCII] = true;
        }

        return $this->cache[static::CACHE_IS_ASCII];
    }

    /**
     * @return UnicodeString
     */
    public function toAscii(): self
    {

        if (!isset($this->cache[static::CACHE_TO_ASCII])) {
            if (isset($this->cache[static::CACHE_IS_ASCII]) && $this->cache[static::CACHE_IS_ASCII]) {
                $this->cache[static::CACHE_TO_ASCII] = clone $this;
            } else {
                $ascii = $this->getAsciiMap();
                $char = $this->getCharMap();
                $ch = [];
                $cp = [];

                foreach ($this->codes as $code) {
                    if (isset($ascii[$code])) {
                        $cp[] = $c = $ascii[$code];
                        $ch[] = $char[$c];
                    }
                }

                $instance = new static($cp, $ch);
                $instance->cache[static::CACHE_IS_ASCII] = true;

                $keys = [static::CACHE_IS_UPPER, static::CACHE_IS_LOWER];

                foreach ($keys as $key) {
                    if (isset($this->cache[$key])) {
                        $instance->cache[$key] = $this->cache[$key];
                    }
                }

                $this->cache[static::CACHE_TO_ASCII] = $instance;
            }
        }

        return $this->cache[static::CACHE_TO_ASCII];
    }

    /**
     * @return UnicodeString
     */
    public function toLower(): self
    {
        if (!isset($this->cache[static::CACHE_TO_LOWER])) {
            if (isset($this->cache[static::CACHE_IS_LOWER]) && $this->cache[static::CACHE_IS_LOWER]) {
                $this->cache[static::CACHE_TO_LOWER] = clone $this;
            } else {
                $this->cache[static::CACHE_TO_LOWER] = $this->toCase($this->getLowerMap(), static::CACHE_IS_LOWER);
            }
        }

        return $this->cache[static::CACHE_TO_LOWER];
    }

    /**
     * @return UnicodeString
     */
    public function toUpper(): self
    {
        if (!isset($this->cache[static::CACHE_TO_UPPER])) {
            if (isset($this->cache[static::CACHE_IS_UPPER]) && $this->cache[static::CACHE_IS_UPPER]) {
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
        if ($this->string === null) {
            $this->string = implode('', $this->chars);
        }

        return $this->string;
    }

    /**
     * @param $string
     * @param string $encoding
     * @return UnicodeString
     */
    public static function from($string, $encoding = 'UTF-8')
    {
        static $ord;

        if ($string instanceof self) {
            return $string;
        }

        if ($encoding !== 'UTF-8') {
            if (false === $string = @iconv($encoding, 'UTF-8', $string)) {
                throw new RuntimeException("Could not convert string from '$encoding' encoding to UTF-8 encoding");
            }
        }

        if ($ord === null) {
            $ord = require __DIR__ . '/../res/ord.php';
        }

        $codes = $chars = [];

        for ($i = 0, $l = strlen($string); $i < $l; $i++) {
            $c = $ord[$ch = $string[$i]];

            if (($c & 0x80) == 0) {
                $codes[] = $c;
                $chars[] = $ch;
            } elseif (($c & 0xE0) == 0xC0) {
                $c1 = $ord[$string[++$i]];
                $codes[] = (($c & 0x1F) << 6) | ($c1 & 0x3F);
                $chars[] = substr($string, $i - 1, 2);
            } elseif (($c & 0xF0) == 0xE0) {
                $c1 = $ord[$string[++$i]];
                $c2 = $ord[$string[++$i]];
                $codes[] = (($c & 0x0F) << 12) | (($c1 & 0x3F) << 6) | ($c2 & 0x3F);
                $chars[] = substr($string, $i - 2, 3);
            } elseif (($c & 0xF8) == 0xF0) {
                $c1 = $ord[$string[++$i]];
                $c2 = $ord[$string[++$i]];
                $c3 = $ord[$string[++$i]];
                $codes[] = (($c & 0x07) << 18) | (($c1 & 0x3F) << 12) | (($c2 & 0x3F) << 6) | ($c3 & 0x3F);
                $chars[] = substr($string, $i - 3, 4);
            } else {
                throw new RuntimeException('Invalid UTF-8 string');
            }
        }

        return new static($codes, $chars);
    }

    /**
     * @param string|UnicodeString $character_mask
     * @param bool $left
     * @param bool $right
     * @return UnicodeString
     */
    protected function doTrim($character_mask, bool $left = true, bool $right = true): self
    {
        $character_mask = static::from($character_mask);

        $cm = $character_mask->codes;
        $cp = $this->codes;
        $start = 0;
        $end = $this->length;

        if ($left) {
            for ($i = 0; $i < $this->length; $i++) {
                if (!in_array($cp[$i], $cm)) {
                    break;
                }
            }
            $start = $i;
        }

        if ($right) {
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
     * @param int $length
     * @param string|UnicodeString $pad
     * @param bool $left
     * @return UnicodeString
     */
    protected function doPad(int $length, $pad, bool $left = true): self
    {
        if ($length <= 0) {
            return new static([], []);
        }

        if ($length === $this->length) {
            return clone $this;
        }

        if ($length < $this->length) {
            if ($left) {
                return $this->substring($this->length - $length);
            } else {
                return $this->substring(0, $length);
            }
        }

        $pad = static::from($pad);

        if ($pad->isEmpty()) {
            $pad = static::from(' ');
        }

        $noch = $length - $this->length;
        $mod = $noch % $pad->length;
        $times = ($noch - $mod) / $pad->length;

        $padchars = [];
        $padcodes = [];

        for ($i = 0; $i < $times; $i++) {
            $padcodes = array_merge($padcodes, $pad->codes);
            $padchars = array_merge($padchars, $pad->chars);
        }

        if ($mod != 0) {
            $padcodes = array_merge($padcodes, array_slice($pad->codes, 0, $mod));
            $padchars = array_merge($padchars, array_slice($pad->chars, 0, $mod));
        }

        if ($left) {
            $cp = array_merge($padcodes, $this->codes);
            $ch = array_merge($padchars, $this->chars);
        } else {
            $cp = array_merge($this->codes, $padcodes);
            $ch = array_merge($this->chars, $padchars);
        }

        return new static($cp, $ch);
    }

    /**
     * @param array $map
     * @param int $cacheKey
     * @return UnicodeString
     */
    protected function toCase(array $map, int $cacheKey): self
    {
        $cp = $this->codes;
        $ch = $this->chars;
        $ocp = $och = [];

        for ($i = 0, $l = $this->length; $i < $l; $i++) {
            $p = $cp[$i];
            if (isset($map[$p])) {
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
    protected function isCase(array $map): bool
    {
        foreach ($this->codes as $cp) {
            if (isset($map[$cp])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getUpperMap(): array
    {
        static $upper;

        if ($upper === null) {
            $upper = require __DIR__ . '/../res/upper.php';
        }

        return $upper;
    }

    /**
     * @return array
     */
    protected function getLowerMap(): array
    {
        static $lower;

        if ($lower === null) {
            $lower = require __DIR__ . '/../res/lower.php';
        }

        return $lower;
    }

    /**
     * @return array
     */
    protected function getAsciiMap(): array
    {
        static $ascii;

        if ($ascii === null) {
            $ascii = require __DIR__ . '/../res/ascii.php';
        }

        return $ascii;
    }

    /**
     * @return array
     */
    protected function getCharMap(): array
    {
        static $char;

        if ($char === null) {
            $char = require __DIR__ . '/../res/char.php';
        }

        return $char;
    }

}
