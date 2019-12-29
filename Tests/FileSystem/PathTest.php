<?php
namespace FileSystem;


use PHPUnit\Framework\TestCase;


class PathTest extends TestCase
{
	public function test_combine_EmptyValuePassed_ReturnEmptyPath()
	{
		self::assertEquals(Path::combineToPath(), '');
	}
	
	public function test_combine_PassRootDirectories()
	{
		self::assertEquals('/', Path::combine('/'));
		self::assertEquals('//', Path::combine('//'));
		self::assertEquals('/', Path::combine('///'));
		self::assertEquals('/', Path::combine('////////'));
	}
	
	public function test_combine_FirstElementIsRoot()
	{
		self::assertEquals('/a', Path::combine('/', 'a'));
		self::assertEquals('//a', Path::combine('//', 'a'));
		self::assertEquals('/a', Path::combine('/', '/a'));
		self::assertEquals('//a', Path::combine('//', '/a'));
	}
	
	public function test_combine_SubPathsThatHaveRootPart()
	{
		self::assertEquals('/abc/def', Path::combine('/', '/abc', '//def'));
		self::assertEquals('abc/def', Path::combine('abc/', 'def//'));
		self::assertEquals('/', Path::combine('/', '//', '/'));
		self::assertEquals('/', Path::combine('/', '/'));
	}
	
	public function test_combine_PassMultipleSlashes_ExtraSlashesIgnored()
	{
		self::assertEquals('abc/de/f', Path::combine('abc////', 'de//f'));
		self::assertEquals('/abc/de/f', Path::combine('////abc////', 'de//f'));
		self::assertEquals('//abc/de/f', Path::combine('//abc////', 'de//f'));
		
		self::assertEquals('a/b', Path::combine('a/', '/b'));
		self::assertEquals('a/b', Path::combine('a/', '//b'));
		self::assertEquals('a/b', Path::combine('a/', 'b//'));
		self::assertEquals('a/b', Path::combine('a//', 'b'));
	}
	
	
	public function test_rmdir_DirectoryDoesNotExist()
	{
		$path = new Path(__DIR__, 'PathTest/rmdir/not_exist');
		$path->rmdir();
	}
}