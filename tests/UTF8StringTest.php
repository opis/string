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

namespace Opis\String\Test;

use Opis\String\UTF8String as wstring;
use PHPUnit\Framework\TestCase;

class UTF8StringTest extends TestCase
{
    public function testEquals()
    {
        $this->assertTrue(wstring::from('abc')->equals('abc'));
        $this->assertFalse(wstring::from('Abc')->equals('abc'));
    }

    public function testEqualsCaseInsensitive()
    {
        $this->assertTrue(wstring::from('Abc')->equals('aBc', true));
        $this->assertFalse(wstring::from('Abd')->equals('aBc', true));
    }

    public function testCompareTo()
    {
        $this->assertEquals(0, wstring::from('abc')->compareTo('abc'));
        $this->assertEquals(1, wstring::from('abcd')->compareTo('abc'));
        $this->assertEquals(1, wstring::from('abc')->compareTo('Abc'));
        $this->assertEquals(-1, wstring::from('Abc')->compareTo('abc'));
        $this->assertEquals(-1, wstring::from('abc')->compareTo('abcd'));
    }

    public function testCompareToCaseInsensitive()
    {
        $this->assertEquals(0, wstring::from('abc')->compareTo('abc', true));
        $this->assertEquals(1, wstring::from('abcd')->compareTo('abc', true));
        $this->assertEquals(0, wstring::from('abc')->compareTo('Abc', true));
        $this->assertEquals(0, wstring::from('Abc')->compareTo('abc', true));
        $this->assertEquals(-1, wstring::from('abc')->compareTo('abcd', true));
    }

    public function testContains()
    {
        $this->assertTrue(wstring::from('abcdefg')->contains('cde'));
        $this->assertTrue(wstring::from('abcdefg')->contains('abc'));
        $this->assertTrue(wstring::from('abcdefg')->contains('efg'));
        $this->assertFalse(wstring::from('abcdefg')->contains('cda'));
    }


    public function testContainsCaseInsensitive()
    {
        $this->assertTrue(wstring::from('abcdefg')->contains('CDE', true));
        $this->assertTrue(wstring::from('abcdefg')->contains('ABC', true));
        $this->assertTrue(wstring::from('abcdefg')->contains('EFG', true));
        $this->assertFalse(wstring::from('abcdefg')->contains('CDA'));
    }

    public function testStartsWith()
    {
        $this->assertTrue(wstring::from('abcde')->startsWith('ab'));
        $this->assertFalse(wstring::from('abcde')->startsWith('ac'));
    }

    public function testStartsWithCaseInsensitive()
    {
        $this->assertTrue(wstring::from('abcde')->startsWith('AB', true));
        $this->assertFalse(wstring::from('abcde')->startsWith('AC', true));
    }

    public function testEndsWith()
    {
        $this->assertTrue(wstring::from('abcde')->endsWith('de'));
        $this->assertFalse(wstring::from('abcde')->endsWith('ce'));
    }

    public function testEndsWithCaseInsensitive()
    {
        $this->assertTrue(wstring::from('abcde')->endsWith('DE', true));
        $this->assertFalse(wstring::from('abcde')->endsWith('CE', true));
    }

    public function testIndexOf()
    {
        $this->assertEquals(0, wstring::from('abcabc')->indexOf('ab'));
        $this->assertEquals(3, wstring::from('abcabc')->indexOf('ab', 1));
    }

    public function testIndexOfCaseInsensitive()
    {
        $this->assertEquals(0, wstring::from('abcabc')->indexOf('AB', 0, true));
        $this->assertEquals(3, wstring::from('abcabc')->indexOf('AB', 1, true));
    }

    public function testLastIndexOf()
    {
        $this->assertEquals(3, wstring::from('abcabcAbc')->lastIndexOf('ab'));
    }

    public function testLastIndexOfCaseInsensitive()
    {
        $this->assertEquals(6, wstring::from('abcabcAbc')->lastIndexOf('AB', true));
    }

    public function testAppend()
    {
        $this->assertEquals("abcdef", (string) wstring::from('abc')->append('def'));
    }

    public function testPrepend()
    {
        $this->assertEquals("abcdef", (string) wstring::from('def')->prepend('abc'));
    }

    public function testTrim()
    {
        $this->assertEquals("abc", (string) wstring::from("   \nabc\n\r\t \n")->trim());
    }

    public function testRightTrim()
    {
        $this->assertEquals("   \nabc", (string) wstring::from("   \nabc\n\r\t \n")->rtrim());
    }

    public function testLeftTrim()
    {
        $this->assertEquals("abc\n\r\t \n", (string) wstring::from("   \nabc\n\r\t \n")->ltrim());
    }

    public function testReplace()
    {
        $this->assertEquals("0x0a0", (string) wstring::from("0a0a0")->replace("a", "x"));
        $this->assertEquals("0a0x0", (string) wstring::from("0a0a0")->replace("a", "x", 2));
    }

    public function testReplaceAll()
    {
        $this->assertEquals("0x0x0", (string) wstring::from("0a0a0")->replaceAll("a", "x"));
    }

    public function testReverse()
    {
        $this->assertEquals("fedcba", (string) wstring::from("abcdef")->reverse());
    }

    public function testSplit()
    {
        $map = function($value){
            return (string) $value;
        };
        $split = function($text, $char = ' ') use($map){
            return array_map($map, wstring::from($text)->split($char));
        };

        $this->assertEquals(array('a', 'b', 'c'), $split('a b c'));
        $this->assertEquals(array('', 'a', 'b', 'c'), $split(' a b c'));
        $this->assertEquals(array('', 'a', 'b', 'c', '', ''), $split(' a b c  '));
        $this->assertEquals(array(' a b c', ''), $split(' a b c  ', '  '));
        $this->assertEquals(array('a b c'), $split('a b c', '#'));
        $this->assertEquals(array('a', 'b', 'c'), $split('abc', ''));
    }

    public function testSubstring()
    {
        $this->assertEquals('abc', wstring::from('abcdef')->substring(0, 3));
        $this->assertEquals('def', wstring::from('abcdef')->substring(3, 3));
        $this->assertEquals('def', wstring::from('abcdef')->substring(3));
        $this->assertEquals('abcdef', wstring::from('abcdef')->substring(0));
    }

    public function testIsCase()
    {
        $this->assertTrue(wstring::from('abcd')->isLowerCase());
        $this->assertFalse(wstring::from('abCd')->isLowerCase());
        $this->assertTrue(wstring::from('ABCD')->isUpperCase());
        $this->assertFalse(wstring::from('ABcD')->isUpperCase());
    }

    public function testToCase()
    {
        $this->assertEquals('ABC', wstring::from('abc')->toUpper());
        $this->assertEquals('ABC', wstring::from('aBc')->toUpper());
        $this->assertEquals('abc', wstring::from('ABC')->toLower());
        $this->assertEquals('abc', wstring::from('AbC')->toLower());
    }

    public function testToAscii()
    {
        $this->assertEquals("aAaAiIsStT", wstring::from("ăĂâÂîÎşŞţŢ")->toAscii());
    }

    public function testIsAscii()
    {
        $this->assertTrue(wstring::from('abcde')->isAscii());
        $this->assertFalse(wstring::from('abcîÎşa')->isAscii());
    }

}