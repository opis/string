<?php
/* ===========================================================================
 * Copyright 2018-2020 Zindex Software
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

namespace Opis\String\Test;

use RuntimeException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Opis\String\UnicodeString as wstr;
use Opis\String\Exception\{
    UnicodeException,
    InvalidStringException
};

class UnicodeStringTest extends TestCase
{

    public function testFrom()
    {
        $this->assertEquals("aBcDe", wstr::from("aBcDe"));
        $this->assertEquals("abcde", wstr::from("aBcDe", null, wstr::LOWER_CASE));
        $this->assertEquals("ABCDE", wstr::from("aBcDe", null, wstr::UPPER_CASE));

        $this->assertEquals("ăĂâÂîÎșȘțȚ", wstr::from("ăĂâÂîÎșȘțȚ"));
        $this->assertEquals("ăăââîîșșțț", wstr::from("ăĂâÂîÎșȘțȚ", null, wstr::LOWER_CASE));
        $this->assertEquals("ĂĂÂÂÎÎȘȘȚȚ", wstr::from("ăĂâÂîÎșȘțȚ", null, wstr::UPPER_CASE));
    }

    public function testFromInvalidEncoding()
    {
        $this->expectException(UnicodeException::class);
        $this->expectExceptionMessage("Could not convert string from 'error_encoding' encoding to UTF-8 encoding");

        wstr::from('abcd', 'error_encoding');
    }

    /**
     * @dataProvider invalidStringOffsetDataProvider
     */
    public function testFromInvalidString(string $str, int $offset)
    {
        $this->expectException(InvalidStringException::class);
        $this->expectExceptionMessage("Invalid UTF-8 string at offset {$offset}");

        wstr::from($str);
    }

    public function invalidStringOffsetDataProvider()
    {
        return [
            ["\x80", 0],
            ["\xC1", 0],
            ["\xF5", 0],

            ["\xC2\x7F", 1],
            ["\xC2\xC0", 1],

            ["\xE0\x9F\x80", 1],
            ["\xE0\xC0\x80", 1],
            ["\xE0\xA0\x7F", 2],
            ["\xE0\xA0\xC0", 2],
            ["\xE1\x80\xC0", 2],

            ["\xF4\x90\x80\x80", 1],
            ["\xF4\x80\x7F\x80", 2],
            ["\xF4\x80\x80\xC0", 3],
        ];
    }

    /**
     * @dataProvider codePointsDataProvider
     */
    public function testCodePoints(string $str, array $codes)
    {
        $this->assertEquals($codes, wstr::from($str)->codePoints());
    }

    public function codePointsDataProvider()
    {
        return [
            ["\u{61}\u{41}\u{103}\u{102}", [0x61, 0x41, 0x103, 0x102]],
            ["ăĂâÂîÎșȘțȚ", [0x103, 0x102, 0xE2, 0xC2, 0xEE, 0xCE, 0x219, 0x218, 0x21B, 0x21A]],
        ];
    }

    public function testChars()
    {
        $this->assertEquals(['ă', 'Ă', 'â', 'Â', 'î', 'Î', 'ș', 'Ș', 'ț', 'Ț'], wstr::from("ăĂâÂîÎșȘțȚ")->chars());
    }

    public function testLength()
    {
        $this->assertEquals(0, wstr::from('')->length());
        $this->assertEquals(1, wstr::from(' ')->length());
        $this->assertEquals(5, wstr::from('abcde')->length());
        $this->assertEquals(10, wstr::from('ăĂâÂîÎșȘțȚ')->length());
        $this->assertEquals(15, wstr::from('abcdeăĂâÂîÎșȘțȚ')->length());
    }

    public function testIsEmpty()
    {
        $this->assertTrue(wstr::from('')->isEmpty());

        $this->assertFalse(wstr::from(' ')->isEmpty());
        $this->assertFalse(wstr::from('0')->isEmpty());
        $this->assertFalse(wstr::from("\x00")->isEmpty());
    }

    public function testEquals()
    {
        $this->assertTrue(wstr::from("abc")->equals("abc"));
        $this->assertTrue(wstr::from("abc")->equals("ABC", true));
        $this->assertTrue(wstr::from("ăĂâÂîÎșȘțȚ")->equals("ăĂâÂîÎșȘțȚ"));
        $this->assertTrue(wstr::from("ăâîșț")->equals("ĂÂÎȘȚ", true));

        $this->assertFalse(wstr::from("abc")->equals("ABC"));
        $this->assertFalse(wstr::from("ăâîșț")->equals("ĂÂÎȘȚ"));
    }

    /**
     * @dataProvider compareToDataProvider
     */
    public function testCompareTo(string $str, string $to, int $result, bool $ignoreCase = false)
    {
        $this->assertEquals($result, wstr::from($str)->compareTo($to, $ignoreCase));
    }

    public function compareToDataProvider()
    {
        return [
            ['abc', 'abc', 0],
            ['abc', 'ABC', 0, true],
            ['abc', 'ABC', 1],
            ['ABC', 'abc', -1],
            ["abc", "ab", 1],
            ["ab", "abc", -1],

            ["ĂÂÎȘȚ", "ĂÂÎȘȚ", 0],
            ["ăâîșț", "ĂÂÎȘȚ", 1],
            ["ĂÂÎȘȚ", "ăâîșț", -1],
            ["ĂÂÎȘȚ", "ăâîșț", 0, true],
        ];
    }

    public function testContains()
    {
        $this->assertTrue(wstr::from("abcde")->contains("bc"));
        $this->assertFalse(wstr::from("abcde")->contains("bf"));

        $this->assertFalse(wstr::from("abcde")->contains("Bc"));
        $this->assertTrue(wstr::from("abcde")->contains("Bc", true));

        $this->assertFalse(wstr::from("ĂÂÎȘȚ")->contains("ăâîșț"));
        $this->assertTrue(wstr::from("ĂÂÎȘȚ")->contains("ăâîșț", true));

        $this->assertFalse(wstr::from("abc")->contains(''));
    }

    /**
     * @dataProvider startsWithDataProvider
     */
    public function testStartsWith(string $str, string $start, bool $result, bool $ignoreCase = false)
    {
        $this->assertEquals($result, wstr::from($str)->startsWith($start, $ignoreCase));
    }

    public function startsWithDataProvider()
    {
        return [
            ["abc", "a", true],
            ["abc", "ab", true],
            ["abc", "abc", true],

            ["abc", "abcd", false],
            ["abc", "b", false],

            ["abc", "A", false],
            ["abc", "A", true, true],
            ["aBc", "Ab", true, true],

            ["abc", "", false],

            ["dabc", "abc", false],
            ["Abc", "ab", false],

            ["ĂÂÎȘȚ", "A", false],
            ["ĂÂÎȘȚ", "A", false, true],
            ["ĂÂÎȘȚ", "Ă", true],
            ["ĂÂÎȘȚ", "ă", false],
            ["ĂÂÎȘȚ", "ăâ", true, true],
        ];
    }

    /**
     * @dataProvider endsWithDataProvider
     */
    public function testEndsWith(string $str, string $end, bool $result, bool $ignoreCase = false)
    {
        $this->assertEquals($result, wstr::from($str)->endsWith($end, $ignoreCase));
    }

    public function endsWithDataProvider()
    {
        return [
            ["abc", "c", true],
            ["abc", "bc", true],
            ["abc", "abc", true],
            ["abc", "C", false],
            ["abc", "Bc", false],
            ["abC", "bc", false],
            ["abc", "", false],

            ["ĂÂÎȘȚ", "T", false],
            ["ĂÂÎȘȚ", "T", false, true],
            ["ĂÂÎȘȚ", "Ț", true],
            ["ĂÂÎȘȚ", "ț", false],
            ["ĂÂÎȘȚ", "șț", true, true],
        ];
    }

    /**
     * @dataProvider indexOfProvider
     */
    public function testIndexOf(string $str, string $needle, int $result, int $offset = 0, bool $ignoreCase = false)
    {
        $this->assertEquals($result, wstr::from($str)->indexOf($needle, $offset, $ignoreCase));
    }

    public function indexOfProvider()
    {
        return [
            ["abc-abc", "a", 0],
            ["abc-abc", "a", 4, 1],
            ["abc-abc", "a", -1, 5],
            ["abc-abc", "A", -1],
            ["abc-abc", "A", 0, 0, true],
            ["abc-abc", "A", 4, 1, true],
            ["abc-abc", "A", -1, 5, true],

            ["Abc-Cba", "b", 5, -2],
            ["Abc-Cba", "b", 5, -5],
            ["Abc-Cba", "b", 1, -6],
            ["Abc-Cba", "b", -1, -100],

            ["abcAbcabCABC", "Ab", 3],
            ["abcAbcabCABC", "Ab", 0, 0, true],
            ["abcAbcabCABC", "Ab", 3, 1, true],

            ["ĂÂÎȘȚ", "ÎȘ", 2],
            ["ĂÂÎȘȚ", "îș", 2, 0, true],
        ];
    }

    /**
     * @dataProvider lastIndexOfDataProvider
     */
    public function testLastIndexOf(string $str, string $needle, int $result, int $offset = 0, bool $ignoreCase = false)
    {
        $this->assertEquals($result, wstr::from($str)->lastIndexOf($needle, $offset, $ignoreCase));
    }

    public function lastIndexOfDataProvider()
    {
        return [
            ["abacad", "a", 4],
            ["abacad", "A", -1],
            ["abc", "a", -1, 1],
            ["abAbabABaB", "Ab", 8, 0, true],

            // strrpos() like tests
            ["0123456789a123456789b123456789c", "0", 0],
            ["0123456789a123456789b123456789c", "0", -1, 1],
            ["0123456789a123456789b123456789c", "7", 27, 20],
            ["0123456789a123456789b123456789c", "7", -1, 28],
            ["0123456789a123456789b123456789c", "7", 17, -5],
            ["0123456789a123456789b123456789c", "c", -1, -2],
            ["0123456789a123456789b123456789c", "9c", 29, -2],
        ];
    }

    /**
     * @dataProvider ensurePrefixDataProvider
     */
    public function testEnsurePrefix(string $str, string $prefix, string $result, bool $ignoreCase = false, bool $allow = true)
    {
        $this->assertEquals($result, (string)wstr::from($str)->ensurePrefix($prefix, $ignoreCase, $allow));
    }

    public function ensurePrefixDataProvider()
    {
        return [
            ["abc", "id_", "id_abc"], // needs "id_" prefix
            ["_abc", "id_", "id_abc"], // needs only "id" prefix
            ["d_abc", "id_", "id_abc"], // needs only "i" prefix
            ["id_abc", "id_", "id_abc"], // already prefixed

            ["ID_abc", "id_", "id_ID_abc"], // needs "id_" prefix ("ID_" != "id_")
            ["D_abc", "id_", "iD_abc", true], // needs only "i" prefix (case insensitive)

            ["i", "id_", "id_"], // needs only "d_" suffix so the resulting string will still be prefixed with "id_"
            ["id_", "id_", "id_", false, true], // the result can be just the prefix
            ["id_", "id_", "id_id_", false, false], // result cannot be only the prefix
            ["id_abc", "id_", "id_abc", false, false], // the resulted string is not just the prefix

            // others
            ["xxx", "xxxabc", "xxxabc"],
            ["abcdef", "xyz_", "xyz_abcdef"],
        ];
    }

    /**
     * @dataProvider ensureSuffixDataProvider
     */
    public function testEnsureSuffix(string $str, string $suffix, string $result, bool $ignoreCase = false, bool $allow = true)
    {
        $this->assertEquals($result, (string)wstr::from($str)->ensureSuffix($suffix, $ignoreCase, $allow));
    }

    public function ensureSuffixDataProvider()
    {
        return [
            ["abc", "_id", "abc_id"], // needs "_id" suffix
            ["abc_", "_id", "abc_id"], // needs only "id" suffix
            ["abc_i", "_id", "abc_id"], // needs only "d" suffix
            ["abc_id", "_id", "abc_id"], // already suffixed

            ["abc_ID", "_id", "abc_ID_id"], // needs "_id" suffix ("_ID" != "_id")
            ["abc_I", "_id", "abc_Id", true], // needs only "d" suffix (case insensitive)

            ["d", "_id", "_id"], // needs only "_i" prefix so the resulting string will still be suffixed with "_id"
            ["_id", "_id", "_id", false, true], // the result can be just the suffix
            ["_id", "_id", "_id_id", false, false], // result cannot be only the suffix
            ["abc_id", "_id", "abc_id", false, false], // the resulted string is not just the suffix

            // others

            ["abc", "xxxabc", "xxxabc"],
            ["abcdef", "_xyz", "abcdef_xyz"],
        ];
    }

    public function testAppend()
    {
        $this->assertEquals("abcDEF", (string)wstr::from("abc")->append("DEF"));
        $this->assertEquals("AbCdef", (string)wstr::from("AbC")->append("DeF", wstr::LOWER_CASE));
        $this->assertEquals("AbCDEF", (string)wstr::from("AbC")->append("dEf", wstr::UPPER_CASE));
    }

    public function testPrepend()
    {
        $this->assertEquals("DEFabc", (string)wstr::from("abc")->prepend("DEF"));
        $this->assertEquals("defabc", (string)wstr::from("abc")->prepend("DeF", wstr::LOWER_CASE));
        $this->assertEquals("DEFabc", (string)wstr::from("abc")->prepend("dEf", wstr::UPPER_CASE));
    }

    /**
     * @dataProvider insertDataProvider
     */
    public function testInsert(string $str, string $add, int $offset, string $result, int $case = wstr::KEEP_CASE)
    {
        $this->assertEquals($result, (string)wstr::from($str)->insert($add, $offset, $case));
    }

    public function insertDataProvider()
    {
        return [
            ["012345", "abc", 0, "abc012345"],
            ["012345", "abc", 1, "0abc12345"],
            ["012345", "abc", -2, "0123abc45"],
            ["012345", "ABC", 0, "abc012345", wstr::LOWER_CASE],
            ["012345", "abc", 0, "ABC012345", wstr::UPPER_CASE],
        ];
    }

    /**
     * @dataProvider removeDataProvider
     */
    public function testRemove(string $str, int $offset, $length, string $result)
    {
        $this->assertEquals($result, (string)wstr::from($str)->remove($offset, $length));
    }

    public function removeDataProvider()
    {
        return [
            ["0123456789", 0, 3, "3456789"],
            ["0123456789", 2, 1, "013456789"],
            ["0123456789", 5, null, "01234"],
            ["0123456789", -3, null, "0123456"],
            ["0123456789", -3, 2, "01234569"],
        ];
    }

    /**
     * @dataProvider trimDataProvider
     */
    public function testTrim(string $str, $chars, string $result)
    {
        $this->assertEquals($result, (string)wstr::from($str)->trim($chars ?? " \t\n\r\0\x0B"));
    }

    public function trimDataProvider()
    {
        return array_map(function (array $v) {
            return [$v[0], $v[1], $v[2]];
        }, $this->trimData());
    }

    /**
     * @dataProvider trimLeftDataProvider
     */
    public function testTrimLeft(string $str, $chars, string $result)
    {
        $this->assertEquals($result, (string)wstr::from($str)->trimLeft($chars ?? " \t\n\r\0\x0B"));
    }

    public function trimLeftDataProvider()
    {
        return array_map(function (array $v) {
            return [$v[0], $v[1], $v[3]];
        }, $this->trimData());
    }

    /**
     * @dataProvider trimRightDataProvider
     */
    public function testTrimRight(string $str, $chars, string $result)
    {
        $this->assertEquals($result, (string)wstr::from($str)->trimRight($chars ?? " \t\n\r\0\x0B"));
    }

    public function trimRightDataProvider()
    {
        return array_map(function (array $v) {
            return [$v[0], $v[1], $v[4]];
        }, $this->trimData());
    }

    public function trimData()
    {
        return [
            //["str", "chars", "trim", "ltrim", "rtrim"]
            ["  abc  ", null, "abc", "abc  ", "  abc"],
            ["abcxd", "xy", "abcxd", "abcxd", "abcxd"],
            ["yyabcxdxyxyxy", "xy", "abcxd", "abcxdxyxyxy", "yyabcxd"],
            ["ĂAAAĂAAAĂ", "ĂA", "", "", ""],
            ["ĂAAAxĂyAAAĂ", "ĂA", "xĂy", "xĂyAAAĂ", "ĂAAAxĂy"],
        ];
    }

    public function testReverse()
    {
        $this->assertEquals("ZzȚțȘșÎîÂâĂă", (string)wstr::from("ăĂâÂîÎșȘțȚzZ")->reverse());
    }

    public function testRepeat()
    {
        $this->assertEquals("abcabcabc", (string)wstr::from("abc")->repeat(3));
    }

    /**
     * @dataProvider replaceDataProvider
     */
    public function testReplace(string $str, string $subject, string $replace, string $result, int $offset = 0, bool $ignoreCase = false)
    {
        $this->assertEquals($result, (string)wstr::from($str)->replace($subject, $replace, $offset, $ignoreCase));
    }

    public function replaceDataProvider()
    {
        return [
            ["abcdabcd", "bc", "", "adabcd"],
            ["abcdabcd", "bc", "x", "axdabcd"],
            ["abcdabcd", "bc", "xy", "axydabcd"],
            ["abcdabcd", "bc", "xyz", "axyzdabcd"],
            ["abcdabcd", "bc", "XYZ", "abcdaXYZd", 3],
            ["abcdabcd", "bc", "XYZ", "abcdaXYZd", -4],
            ["abcdabcd", "bc", "XYZ", "abcdabcd", 6],

            ["aBcAbC", "bC", "XX", "aBcAXX"],
            ["aBcAbC", "bC", "XX", "aXXAbC", 0, true],
            ["aBcAbC", "bC", "XX", "aBcAXX", 2, true],
            ["aBcAbC", "bC", "XX", "aBcAXX", -2, true],
            ["aBcAbC", "bC", "XX", "aBcAbC", -1, true],

            ["ăĂâÂîÎșȘțȚzZ", "Î", "X", "ăĂâÂîXșȘțȚzZ"],
            ["ăĂâÂîÎșȘțȚzZ", "Î", "X", "ăĂâÂXÎșȘțȚzZ", 0, true],
        ];
    }

    /**
     * @dataProvider replaceAllDataProvider
     */
    public function testReplaceAll(string $str, string $subject, string $replace, string $result, bool $ignoreCase = false, int $offset = 0)
    {
        $this->assertEquals($result, (string)wstr::from($str)->replaceAll($subject, $replace, $ignoreCase, $offset));
    }

    public function replaceAllDataProvider()
    {
        return [
            ["AabcaAa", "a", "X", "AXbcXAX"],
            ["AabcaAa", "a", "X", "XXbcXXX", true],
            ["aaa", "a", "X", "aXX", false, 1],
            ["aaa", "a", "X", "aaX", false, -1],
            ["Aaa", "a", "", "A"],

            ["ăĂâÂîÎșȘțȚzZ", "Î", "X", "ăĂâÂîXșȘțȚzZ"],
            ["ăĂâÂîÎșȘțȚzZ", "Î", "X", "ăĂâÂXXșȘțȚzZ", true],

            ["aîÎb", "Îî", "X", "aîÎb"],
            ["aîÎb", "Îî", "X", "aXb", true],
            ["aîÎb", "Îî", "X", "aîÎb", true, 2],
        ];
    }

    /**
     * @dataProvider splitDataProvider
     */
    public function testSplit(string $str, string $delimiter, array $result, bool $ignoreCase = false)
    {
        $this->assertEquals($result, array_map('strval', wstr::from($str)->split($delimiter, $ignoreCase)));
    }

    public function splitDataProvider()
    {
        return [
            ["ȚabcȘ", "", ["Ț", "a", "b", "c", "Ș"]],
            ["a-b-c", "-", ["a", "b", "c"]],
            ["aXbxcXd", "x", ["aXb", "cXd"]],
            ["aXbxcXd", "x", ["a", "b", "c", "d"], true],

            ["a", "a", ["", ""]],
            ["ab", "a", ["", "b"]],
            ["ba", "a", ["b", ""]],
            ["aba", "a", ["", "b", ""]],
            ["aaa", "a", ["", "", "", ""]],

            ["xîÎx", "Îî", ["xîÎx"]],
            ["xîÎx", "Îî", ["x", "x"], true],
        ];
    }

    /**
     * @dataProvider substringDataProvider
     */
    public function testSubstring(string $str, int $offset, $length, string $result)
    {
        $this->assertEquals($result, (string)wstr::from($str)->substring($offset, $length));
    }

    public function substringDataProvider()
    {
        return [
            ["0123456789", 0, null, "0123456789"],
            ["0123456789", 4, null, "456789"],
            ["0123456789", 4, 3, "456"],
            ["abcdef", -1, null, "f"],
            ["abcdef", -2, null, "ef"],
            ["abcdef", -3, 1, "d"],
            ["abcdef", 0, -1, "abcde"],
            ["abcdef", 2, -1, "cde"],
            ["abcdef", 4, -4, ""],
            ["abcdef", -3, -1, "de"],

            ["ăĂâÂîÎșȘțȚ", 2, 4, "âÂîÎ"],
        ];
    }

    /**
     * @dataProvider padDataProvider
     */
    public function testPad(string $str, int $size, $char, string $left, string $right)
    {
        $str = wstr::from($str);

        $this->assertEquals($left, (string)$str->pad(-$size, $char));
        $this->assertEquals($right, (string)$str->pad($size, $char));

        $this->assertEquals($left, (string)$str->padLeft($size, $char));

        $this->assertEquals($right, (string)$str->padRight($size, $char));
    }

    public function padDataProvider()
    {
        return [
            ["abc", 6, "x", "xxxabc", "abcxxx"],
            ["abc", 3, "x", "abc", "abc"],
            ["abc", 5, "xy", "xxabc", "abcxx"], // only first char is taken

            ["123", 5, "0", "00123", "12300"],
            ["", 3, "Ț", "ȚȚȚ", "ȚȚȚ"],
        ];
    }

    /**
     * @dataProvider indexAccessDataProvider
     */
    public function testIndexAccess(string $str, int $index, int $codePoint, string $char)
    {
        $str = wstr::from($str);

        $this->assertEquals($codePoint, $str->codePointAt($index));
        $this->assertEquals($char, $str->charAt($index));

        if ($codePoint === -1) {
            try {
                $str($index);
                $this->assertTrue(false, "Invalid code point index");
            } catch (OutOfBoundsException $e) {
                $this->assertTrue(true);
            }
        } else {
            $this->assertEquals($codePoint, $str($index));
        }

        if ($char === '') {
            $this->assertFalse(isset($str[$index]));
            try {
                $str[$index];
                $this->assertTrue(false, "Invalid char index");
            } catch (OutOfBoundsException $e) {
                $this->assertTrue(true);
            }
        } else {
            $this->assertTrue(isset($str[$index]));
            $this->assertEquals($char, $str[$index]);
        }
    }

    public function indexAccessDataProvider()
    {
        return [
            ["abcd", 1, 0x62, "b"],
            ["abcd", -2, 0x63, "c"],
            ["abcd", 5, -1, ""],
            ["abcd", -6, -1, ""],

            ["ăĂâÂîÎșȘțȚ", 0, 0x103, "ă"],
            ["ăĂâÂîÎșȘțȚ", 4, 0xEE, "î"],
            ["ăĂâÂîÎșȘțȚ", -4, 0x219, "ș"],
            ["ăĂâÂîÎșȘțȚ", -1, 0x21A, "Ț"],
        ];
    }

    public function testArrayLike()
    {
        $str = wstr::from("abc");
        $this->assertEquals(3, count($str));


        $str->isLowerCase(); // just build cache
        $str[1] = "Ș";

        $this->assertEquals("aȘc", (string)$str);
        $this->assertFalse($str->isLowerCase()); // check if cache is rebuild

        $str[-1] = "Ț";
        $this->assertEquals("aȘȚ", (string)$str);

        // only first char
        $str[0] = "Îabc";
        $this->assertEquals("ÎȘȚ", (string)$str);

        // no change
        $str[1] = "";
        $this->assertEquals("ÎȘȚ", (string)$str);

        // cannot unset
        $this->expectException(RuntimeException::class);
        unset($str[1]);
    }


    public function testIsCase()
    {
        $this->assertTrue(wstr::from('abcd')->isLowerCase());
        $this->assertFalse(wstr::from('abCd')->isLowerCase());
        $this->assertTrue(wstr::from('ABCD')->isUpperCase());
        $this->assertFalse(wstr::from('ABcD')->isUpperCase());

        $this->assertTrue(wstr::from('țș@îâ#ă')->isLowerCase());
        $this->assertTrue(wstr::from('ȚȘ@ÎÂ#Ă')->isUpperCase());

        $this->assertFalse(wstr::from('Țș@îâ#ă')->isLowerCase());
        $this->assertFalse(wstr::from('țȘ@ÎÂ#Ă')->isUpperCase());

    }

    public function testToCase()
    {
        $this->assertEquals('ABC', (string)wstr::from('abc')->toUpper());
        $this->assertEquals('ABC', (string)wstr::from('aBc')->toUpper());
        $this->assertEquals('ȚȘ@ÎÂ#Ă', (string)wstr::from('țș@îâ#ă')->toUpper());

        $this->assertEquals('abc', (string)wstr::from('ABC')->toLower());
        $this->assertEquals('abc', (string)wstr::from('AbC')->toLower());
        $this->assertEquals('țș@îâ#ă', (string)wstr::from('ȚȘ@ÎÂ#Ă')->toLower());
    }

    public function testIsAscii()
    {
        $this->assertTrue(wstr::from("abc")->isAscii());
        $this->assertFalse(wstr::from("abcț")->isAscii());
    }

    /**
     * @dataProvider toAsciiDataProvider
     */
    public function testToAscii(string $str, string $result)
    {
        $this->assertEquals($result, (string)wstr::from($str)->toAscii());
    }

    public function toAsciiDataProvider()
    {
        return [
            ["ăĂâÂîÎșȘțȚ", "aAaAiIsStT"],
        ];
    }
}
