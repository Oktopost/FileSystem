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
	
	
	public function test_name_EmptyPath_ReturnEmptyString()
	{
		self::assertEquals('', (new Path())->name());
	}
	
	public function test_name_PathEndsWithDot_DotReturned()
	{
		self::assertEquals('.', (new Path('.'))->name());
	}
	
	public function test_name_RootPath_ReturnRoot()
	{
		self::assertEquals('/', (new Path('/'))->name());
		self::assertEquals('//', (new Path('//'))->name());
	}
	
	public function test_name_SingleNameInPath_SameNameReturned()
	{
		self::assertEquals('a', (new Path('a'))->name());
	}
	
	public function test_name_LongPath_ReturnName()
	{
		self::assertEquals('abc', (new Path('hello/abc'))->name());
		self::assertEquals('abc', (new Path('/hello/abc'))->name());
		self::assertEquals('world', (new Path('//hello/world'))->name());
		self::assertEquals('world', (new Path('//hello/world/'))->name());
		self::assertEquals('world', (new Path('//hello///world'))->name());
	}
	
	
	public function test_length_EmptyPath_Return0()
	{
		self::assertEquals(0, (new Path())->length());
	}
	
	public function test_length_WithPath_ReturnLength()
	{
		self::assertEquals(3, (new Path('123'))->length());
	}
	
	
	public function test_depth_EmptyPath_Return0()
	{
		self::assertEquals(0, (new Path())->depth());
	}
	
	public function test_depth_RootPath_Return0()
	{
		self::assertEquals(0, (new Path('/'))->depth());
		self::assertEquals(0, (new Path('//'))->depth());
	}
	
	public function test_depth_SignleItem_Return0()
	{
		self::assertEquals(0, (new Path('abc'))->depth());
	}
	
	public function test_depth_SingleItemRelativeToRoot_Return1()
	{
		self::assertEquals(0, (new Path('/abc'))->depth());
		self::assertEquals(0, (new Path('//def'))->depth());
	}
	
	public function test_depth_HaveItems_ReturnCorrectDepth()
	{
		self::assertEquals(1, (new Path('/abc/def'))->depth());
		self::assertEquals(1, (new Path('abc/def'))->depth());
		self::assertEquals(4, (new Path('abc/def/123/~/./'))->depth());
	}
	
	public function test_depth_HasDuplicateSlashes_DuplicatesNotCounted()
	{
		self::assertEquals(2, (new Path('/abc/def///gh'))->depth());
		self::assertEquals(1, (new Path('abc////def'))->depth());
	}
	
	
	public function test_resolve_EmptyStringPassed_ReturnEmptyString()
	{
		self::assertEquals('', Path::getPathObject('')->resolve()->get());
	}
	
	public function test_resolve_SingleElementPassed_ReturnSameValue()
	{
		self::assertEquals('abc', Path::getPathObject('abc')->resolve()->get());
	}
	
	public function test_resolve_NumberOfElementsPassed_ReturnSameValue()
	{
		self::assertEquals('abc/def', Path::getPathObject('abc/def')->resolve()->get());
	}
	
	public function test_resolve_ContainsSingleDot_ReturnEmptyString()
	{
		self::assertEquals('', Path::getPathObject('.')->resolve()->get());
	}
	
	public function test_resolve_ContainsSingleDotInPath_RemoveDot()
	{
		self::assertEquals('a/b', Path::getPathObject('a/./b')->resolve()->get());
		self::assertEquals('a/b', Path::getPathObject('./a/b')->resolve()->get());
		self::assertEquals('a/b', Path::getPathObject('a/b/.')->resolve()->get());
		self::assertEquals('a/b', Path::getPathObject('a/././b')->resolve()->get());
	}
	
	public function test_resolve_ContainsTwoDots_TwoDotsReturned()
	{
		self::assertEquals('..', Path::getPathObject('..')->resolve()->get());
	}
	
	public function test_resolve_ContainsNumberOf2Dots_AllDotsRemain()
	{
		self::assertEquals('../../..', Path::getPathObject('../../..')->resolve()->get());
	}
	
	public function test_resolve_ContainsNumberOf2DotsBeforePath_AllDotsRemain()
	{
		self::assertEquals('../../../abc/def', Path::getPathObject('../../../abc/def')->resolve()->get());
	}
	
	public function test_resolve_Contains2DotsAfterRoot_2DotsRemoved()
	{
		self::assertEquals('/abc/def', Path::getPathObject('/../../../abc/def')->resolve()->get());
	}
	
	public function test_resolve_Contains2DotsInPath_2DotsRemovedAndExtraFoldersRemoved()
	{
		self::assertEquals('', Path::getPathObject('a/..')->resolve()->get());
		self::assertEquals('abc/hello', Path::getPathObject('abc/def/../hello/world/..')->resolve()->get());
		self::assertEquals('/abc/def', Path::getPathObject('/a/../../../abc/def')->resolve()->get());
		self::assertEquals('hello/world', Path::getPathObject('abc/def/../../hello/world')->resolve()->get());
	}
	
	public function test_resolve_HomeSignResolvedToHome()
	{
		self::assertEquals($_SERVER['HOME'], Path::getPathObject('~')->resolve()->get());
	}
	
	public function test_resolve_HomeDirectoryAfterPath_UsedAsDirectoryName()
	{
		self::assertEquals('/a/b/~/hello', Path::getPathObject('/a/b/~/hello')->resolve()->get());
	}
	
	public function test_resolve_HomeDirectoryAsFirstParam_HomeDirectoryResolved()
	{
		self::assertEquals($_SERVER['HOME'] . '/hello', Path::getPathObject('~/hello')->resolve()->get());
	}
	
	public function test_resolve_2DotsAfterHomeDir_OnlyOneDirectoryRemoved()
	{
		self::assertEquals('/home/hello', Path::getPathObject('~/../hello')->resolve()->get());
	}
}