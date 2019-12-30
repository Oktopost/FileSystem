<?php
namespace FileSystem;


use PHPUnit\Framework\TestCase;


class PathTest extends TestCase
{
	public function test_constructor_EmptyPath()
	{
		self::assertEquals('', (new Path())->get());
	}
	
	public function test_constructor_RelativePath()
	{
		self::assertEquals('hello/world', (new Path('hello/world'))->get());
	}
	
	public function test_constructor_FullPath()
	{
		self::assertEquals(__DIR__ . '/hello/world', (new Path(__DIR__ . '/hello/world'))->get());
	}
	
	public function test_constructor_SetOfPathsPassed()
	{
		self::assertEquals('hello/world', (new Path('hello', 'world'))->get());
	}
	
	public function test_constructor_SetOfRootPathsPassed()
	{
		self::assertEquals(__DIR__ . '/hello/world', (new Path(__DIR__, '/hello', '/world'))->get());
	}
	
	
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
	
	
	/**
	 * @expectedException \FileSystem\Exceptions\FileSystemException
	 */
	public function test_rmdir_DirectoryDoesNotExist_ExceptionThrown()
	{
		$path = new Path(__DIR__, 'PathTest/rmdir/not_exist');
		$path->rmdir();
	}
	
	/**
	 * @expectedException \FileSystem\Exceptions\FileSystemException
	 */
	public function test_rmdir_NotADirectory_ExcpetionThrown()
	{
		FS::path(__DIR__, 'PathTest/rmdir/not_a_dir')->rmdir();
	}
	
	/**
	 * @expectedException \FileSystem\Exceptions\FileSystemException
	 */
	public function test_rmdir_DirectoryNotempty_ExcpetionThrown()
	{
		FS::path(__DIR__, 'PathTest/rmdir/dir_with_content')->rmdir();
	}
	
	public function test_rmdir_EmptyDirectory_DirectoryRemoved()
	{
		$dir = __DIR__ . '/PathTest/rmdir/to_delete';
		
		mkdir($dir);
		self::assertTrue(is_dir($dir));
		
		FS::path(__DIR__, 'PathTest/rmdir/to_delete')->rmdir();
		
		self::assertFalse(is_dir($dir));
	}
	
	
	public function test_cleanDirectory_DirectoryEmpty_NoError()
	{
		$path = new Path(__DIR__ . '/PathTest/cleanDirectory/empty_dir');
		
		$path->mkdir();
		$path->cleanDirectory();
	}
	
	public function test_cleanDirectory_DirectoryHasFiles_FilesRemoved()
	{
		$path = new Path(__DIR__ . '/PathTest/cleanDirectory/not_empty');
		
		FS::create(
			$path,
			[],
			[
				'hello.bin',
				'world.bin'
			]
		);
		
		
		self::assertTrue(file_exists($path->append('hello.bin')->get()));
		self::assertTrue(file_exists($path->append('world.bin')->get()));
		
		$path->cleanDirectory();
		
		self::assertFalse(file_exists($path->append('hello.bin')->get()));
		self::assertFalse(file_exists($path->append('world.bin')->get()));
		self::assertTrue(is_dir($path->get()));
	}
	
	public function test_cleanDirectory_DirectoryHasFolders_FoldersRemoved()
	{
		$path = new Path(__DIR__ . '/PathTest/cleanDirectory/has_folders');
		
		FS::create(
			$path,
			[
				'hello',
				'world'
			],
			[]
		);
		
		
		self::assertTrue(is_dir($path->append('hello')->get()));
		self::assertTrue(is_dir($path->append('world')->get()));
		
		$path->cleanDirectory();
		
		self::assertFalse(is_dir($path->append('hello')->get()));
		self::assertFalse(is_dir($path->append('world')->get()));
		self::assertTrue(is_dir($path->get()));
	}
	
	public function test_cleanDirectory_DirectoryHasHierarchy_InternalItemsRemoved()
	{
		$path = new Path(__DIR__ . '/PathTest/cleanDirectory/complex');
		
		FS::create(
			$path,
			[],
			[
				'hello/a.bin',
				'hello/world/b.bin',
			]
		);
		
		self::assertTrue(is_dir($path->append('hello')->get()));
		self::assertTrue(is_dir($path->append('hello/world')->get()));
		self::assertTrue(file_exists($path->append('hello/a.bin')->get()));
		self::assertTrue(file_exists($path->append('hello/world/b.bin')->get()));
		
		$path->cleanDirectory();
		
		self::assertFalse(is_dir($path->append('hello')->get()));
		self::assertFalse(is_dir($path->append('hello/world')->get()));
		self::assertFalse(file_exists($path->append('hello/a.bin')->get()));
		self::assertFalse(file_exists($path->append('hello/world/b.bin')->get()));
		self::assertTrue(is_dir($path->get()));
	}
}