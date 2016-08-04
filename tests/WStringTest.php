<?php

use PHPUnit\Framework\TestCase;

class WStringTest extends TestCase
{
    public function testEquals()
    {
        $this->assertTrue(wstring('abc')->equals('abc'));
        $this->assertFalse(wstring('Abc')->equals('abc'));
    }

    public function testEqualsCaseInsensitive()
    {
        $this->assertTrue(wstring('Abc')->equals('aBc', true));
        $this->assertFalse(wstring('Abd')->equals('aBc', true));
    }

    public function testCompareTo()
    {
        $this->assertEquals(0, wstring('abc')->compareTo('abc'));
        $this->assertEquals(1, wstring('abcd')->compareTo('abc'));
        $this->assertEquals(1, wstring('abc')->compareTo('Abc'));
        $this->assertEquals(-1, wstring('Abc')->compareTo('abc'));
        $this->assertEquals(-1, wstring('abc')->compareTo('abcd'));
    }

    public function testCompareToCaseInsensitive()
    {
        $this->assertEquals(0, wstring('abc')->compareTo('abc', true));
        $this->assertEquals(1, wstring('abcd')->compareTo('abc', true));
        $this->assertEquals(0, wstring('abc')->compareTo('Abc', true));
        $this->assertEquals(0, wstring('Abc')->compareTo('abc', true));
        $this->assertEquals(-1, wstring('abc')->compareTo('abcd', true));
    }

    public function testContains()
    {
        $this->assertTrue(wstring('abcdefg')->contains('cde'));
        $this->assertTrue(wstring('abcdefg')->contains('abc'));
        $this->assertTrue(wstring('abcdefg')->contains('efg'));
        $this->assertFalse(wstring('abcdefg')->contains('cda'));
    }


    public function testContainsCaseInsensitive()
    {
        $this->assertTrue(wstring('abcdefg')->contains('CDE', true));
        $this->assertTrue(wstring('abcdefg')->contains('ABC', true));
        $this->assertTrue(wstring('abcdefg')->contains('EFG', true));
        $this->assertFalse(wstring('abcdefg')->contains('CDA'));
    }

    public function testStartsWith()
    {
        $this->assertTrue(wstring('abcde')->startsWith('ab'));
        $this->assertFalse(wstring('abcde')->startsWith('ac'));
    }

    public function testStartsWithCaseInsensitive()
    {
        $this->assertTrue(wstring('abcde')->startsWith('AB', true));
        $this->assertFalse(wstring('abcde')->startsWith('AC', true));
    }

    public function testEndsWith()
    {
        $this->assertTrue(wstring('abcde')->endsWith('de'));
        $this->assertFalse(wstring('abcde')->endsWith('ce'));
    }

    public function testEndsWithCaseInsensitive()
    {
        $this->assertTrue(wstring('abcde')->endsWith('DE', true));
        $this->assertFalse(wstring('abcde')->endsWith('CE', true));
    }

    public function testIndexOf()
    {
        $this->assertEquals(0, wstring('abcabc')->indexOf('ab'));
        $this->assertEquals(3, wstring('abcabc')->indexOf('ab', 1));
    }

    public function testIndexOfCaseInsensitive()
    {
        $this->assertEquals(0, wstring('abcabc')->indexOf('AB', 0, true));
        $this->assertEquals(3, wstring('abcabc')->indexOf('AB', 1, true));
    }

    public function testLastIndexOf()
    {
        $this->assertEquals(3, wstring('abcabcAbc')->lastIndexOf('ab'));
    }

    public function testLastIndexOfCaseInsensitive()
    {
        $this->assertEquals(6, wstring('abcabcAbc')->lastIndexOf('AB', true));
    }

    public function testAppend()
    {
        $this->assertEquals("abcdef", (string) wstring('abc')->append('def'));
    }

    public function testPrepend()
    {
        $this->assertEquals("abcdef", (string) wstring('def')->prepend('abc'));
    }

    public function testTrim()
    {
        $this->assertEquals("abc", (string) wstring("   \nabc\n\r\t \n")->trim());
    }

    public function testRightTrim()
    {
        $this->assertEquals("   \nabc", (string) wstring("   \nabc\n\r\t \n")->rtrim());
    }

    public function testLeftTrim()
    {
        $this->assertEquals("abc\n\r\t \n", (string) wstring("   \nabc\n\r\t \n")->ltrim());
    }

    public function testReplace()
    {
        $this->assertEquals("0x0a0", (string) wstring("0a0a0")->replace("a", "x"));
        $this->assertEquals("0a0x0", (string) wstring("0a0a0")->replace("a", "x", 2));
    }

    public function testReplaceAll()
    {
        $this->assertEquals("0x0x0", (string) wstring("0a0a0")->replaceAll("a", "x"));
    }

    public function testReverse()
    {
        $this->assertEquals("fedcba", (string) wstring("abcdef")->reverse());
    }

    public function testSplit()
    {
        $map = function($value){
            return (string) $value;
        };
        $split = function($text, $char = ' ') use($map){
            return array_map($map, wstring($text)->split($char));
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
        $this->assertEquals('abc', wstring('abcdef')->substring(0, 3));
        $this->assertEquals('def', wstring('abcdef')->substring(3, 3));
        $this->assertEquals('def', wstring('abcdef')->substring(3));
        $this->assertEquals('abcdef', wstring('abcdef')->substring(0));
    }

    public function testIsCase()
    {
        $this->assertTrue(wstring('abcd')->isLowerCase());
        $this->assertFalse(wstring('abCd')->isLowerCase());
        $this->assertTrue(wstring('ABCD')->isUpperCase());
        $this->assertFalse(wstring('ABcD')->isUpperCase());
    }

    public function testToCase()
    {
        $this->assertEquals('ABC', wstring('abc')->toUpper());
        $this->assertEquals('ABC', wstring('aBc')->toUpper());
        $this->assertEquals('abc', wstring('ABC')->toLower());
        $this->assertEquals('abc', wstring('AbC')->toLower());
    }

}