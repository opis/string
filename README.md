Opis String
===========
[![Build Status](https://travis-ci.org/opis/string.svg?branch=master)](https://travis-ci.org/opis/string)
[![Latest Stable Version](https://poser.pugx.org/opis/string/version.png)](https://packagist.org/packages/opis/string)
[![Latest Unstable Version](https://poser.pugx.org/opis/string/v/unstable.png)](//packagist.org/packages/opis/string)
[![License](https://poser.pugx.org/opis/string/license.png)](https://packagist.org/packages/opis/string)

Multi-byte string manipulation
------------------------------

This tiny library allows you to manipulate strings encoded in multi-byte 
encodings, using an OO paradigm. The library has **no dependencies** to 
mbstring or similar PHP extensions.

### License

**Opis String** is licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0). 

### Requirements

* PHP 5.3.* or higher

### Installation

This library is available on [Packagist](https://packagist.org/packages/opis/string) and can be installed using [Composer](http://getcomposer.org).

```json
{
    "require": {
        "opis/string": "^1.2.0"
    }
}
```

### Usage

Creating a new Unicode string is done using the static method `from`.

```php
use Opis\String\UnicodeString as wstring;

$str = wstring::from('ăĂâÂîÎşŞţŢ');
```

By default, it's assumed that your string is encoded using UTF-8. 
If your string is encoded using another encoding, you can pass the name 
of the encoding as the second argument of the `from` method.

```php
use Opis\String\UnicodeString as wstring;

$str = wstring::from($string, 'ISO-8859-1');
```

Once created, you can use the resulted object in the same manner in which 
you would use a regular string.

```php
echo $str; //> ăĂâÂîÎşŞţŢ
echo 'foo ' . $str . ' bar'; //> foo ăĂâÂîÎşŞţŢ bar
echo $str[0]; //> ă
echo $str[4]; //> î
```

You can chain multiple methods to perform operations against a string.

```php
use Opis\String\UnicodeString as wstring;

$str = wstring::from('ăĂâÂîÎşŞţŢ');

echo $str->substring(3, 4)
         ->toUpper(); //> ÂÎÎŞ
```

**Important!** The `UnicodeString` instances are immutable and works in
a similar manner as C# or Java strings.

```php
use Opis\String\UnicodeString as wstring;

$str = wstring::from('abcd');

echo $str->toUpper(); //> ABCD
echo $str; //> abcd
```

### Methods

##### length()

Returns the length of the string.

```php
echo $str->length(); //> 10
```

##### isEmpty()

Check if the string is empty.

```php
if($str->isEmpty()){
    //...
}
```

##### chars()

Returns an array of chars

##### codePoints()

Returns an array of Unicode code points

##### equals($text, bool $ignoreCase = false)

Check if two strings contains the same sequence of characters.

```php
if($str->equals('abcd')){
    //...
}

// Case insensitive checking
if($str->equals('abcd', true)){
    //...
}
```

##### compareTo($text, $ignoreCase = false)

Compare the current string with a given string and returns `0` if the 
strings are equal, `1` if the current string is greater and `-1` otherwise

```php
echo $str->compareTo('abcd'); //> 1
```

##### contains($text, $ignoreCase = false)

Check if the current string contains the given string

```php
if($str->contains('abcd')){
    //...
}

// Case insensitive checking
if($str->contains('abcd', true)){
    //...
}
```


##### startsWith($text, $ignoreCase = false)

Check if the current string starts with the given text

```php
if($str->startsWith('abcd')){
    //...
}

// Case insensitive checking
if($str->startsWith('abcd', true)){
    //...
}
```

##### endsWith($text, $ignoreCase = false)

Check if the current string ends with the given text

```php
if($str->endsWith('abcd')){
    //...
}

// Case insensitive checking
if($str->endsWith('abcd', true)){
    //...
}
```

##### indexOf($text, $offset = 0, $ignoreCase = false)

Find the first occurrence of the given string within the current string 

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcabc')->indexOf('a'); //> 0
```

You can also specify the index on which the search to begin from

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcabc')->indexOf('a', 1); //> 3
```

Case insensitive searching is done by passing `true` as the last argument
to the method

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcABC')->indexOf('A', 0, true); //> 0
```

##### lastIndexOf($text, $ignoreCase = false)

Find the last occurrence of the given string within the current string 

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('AbcAbcabc')->lastIndexOf('A'); //> 3

// Case insensitive
echo wstring::from('AbcAbcabc')->lastIndexOf('A', true); //> 6
```

##### append($text)

Append the given string to the current string

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abc')->append('def'); //> abcdef
```

##### prepend($text)

Prepend the given string to the current string

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abc')->prepend('def'); //> defabc
```

##### insert($text, $index)

Insert the given string at the specified position

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcdef')->prepend('x', 3); //> abcxdef
```

##### trim($character_mask = " \t\n\r\0\x0B")

Trim from both sides of the string, the characters specified in the character mask

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('  abc  ')->trim(); //> abc
echo wstring::from('xxxabcxxx')->trim('x'); //> abc
```

##### trimLeft($character_mask = " \t\n\r\0\x0B")

Trim from the left side of the string, the characters specified in the character mask

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('xxxabcxxx')->trimLeft('x'); //> abcxxx
```

##### trimRight($character_mask = " \t\n\r\0\x0B")

Trim from the right side of the string, the characters specified in the character mask

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('xxxabcxxx')->trimRight('x'); //> xxxabc
```

##### replace($subject, $replace, $offset = 0)

Replace the first occurrence of the given string with another one

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcabc')->replace('abc', 'foo'); //> fooabc

// Using an offset
echo wstring::from('abcabc')->replace('abc', 'foo', 2); //> abcfoo
```

##### replaceAll($subject, $replace)

Replace all occurrences of the given string with another one

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcabc')->replaceAll('abc', 'foo'); //> foofoo
```

##### reverse()

Returns a string containing all the characters from the current string,
but in reversed order

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcdef')->reverse(); //> fedcba
```

##### repeat($times = 1)

Repeat the current string an arbitrary number of times.

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abc')->repeat(); //> abcabc
echo wstring::from('abc')->repeat(2); //> abcabcabc
```

##### remove($index, $length)

Remove an arbitrary amount of chars starting at a given position

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcdef')->remove(2, 3); //> abf
```

##### padLeft($length, $char = ' ')

Pads the string to a given length using the specified string.

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abc')->padLeft(5, 'x'); //> xxabc
```

##### padRight($length, $char = ' ')

Pads the string to a given length using the specified string.

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abc')->padRight(5, 'x'); //> abcxx
```

##### split($char = '')

Split the current string using the given delimiter and return an array of
UTF-8 string. If no delimiter was given, an UTF-8 string will be created
foreach char in the current string.

```php
use Opis\String\UnicodeString as wstring;

$str = wstring::from('a,b,c');
$tmp = '';

foreach($str->split(',') as $part){
    $tmp .= $part;
}

echo $tmp; //> abc
```

##### substring($start, $length = null)

Copy the specified portion of the string to another string

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abcdef')->substring(3); //> def
echo wstring::from('abcdef')->substring(3, 2); //> de
```

##### isLowerCase()

Check if the current string is in lower case

##### isUpperCase()

Check if the current string is in upper case

##### isAscii()

Check if the current string contains only ASCII chars

##### toLower()

Returns the lowercase version of the current string

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('ABC')->toLower(); //> abc
```

##### toUpper()

Returns the uppercase version of the current string

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('abc')->toUpper(); //> ABC
```

##### toAscii()

Returns the ASCII version(if possible) of the current string

```php
use Opis\String\UnicodeString as wstring;

echo wstring::from('ăĂâÂîÎşŞţŢ')->toAscii(); //> aAaAiIsStT
```