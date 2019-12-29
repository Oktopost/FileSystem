<?php
namespace FileSystem;


use Traitor\TStaticClass;


class FS
{
	use TStaticClass;
	
	
	/**
	 * @param string|Path|array $root
	 * @param string|Path|array|null $structure
	 */
	public static function create($root, $structure = null): void
	{
		if (is_null($structure))
		{
			$structure = $root;
			$root = new Path('/');
		}
		else
		{
			$root = self::path($root);
		}
		
		if (!is_array($structure))
		{
			$structure = [];
		}
		
		foreach ($structure as $item)
		{
			$path = $root->append($item);
			$path->createDir();
		}
	}
	
	public static function path(...$for): Path
	{
		return Path::combineToPath(...$for);
	}
	
	public static function dir(...$path): Dir
	{
		return new Dir(...$path);
	}
	
	public static function file(...$path): File
	{
		return new File(...$path);
	}
	
	public static function exists(...$path): bool
	{
		return self::combineToPath(...$path)->exists();
	}
	
	public static function isFile(...$path): bool
	{
		return self::combineToPath(...$path)->isFile();
	}
	
	public static function isDir(...$path): bool
	{
		return self::combineToPath(...$path)->isDir();
	}
	
	public static function isLink(...$path): bool
	{
		return self::combineToPath(...$path)->isLink();
	}
	
	public static function resolve(...$path): string
	{
		return (string)(self::combineToPath(...$path)->resolve());
	}
	
	public static function resolveToPath(...$path): Path
	{
		return self::combineToPath(...$path)->resolve();
	}
	
	public static function combine(...$path): string
	{
		return Path::combineToPath(...$path);	
	}
	
	public static function combineToPath(...$path): Path
	{
		return Path::combineToPath(...$path);
	}
	
	public static function realpath(...$path): string
	{
		return Path::realpath(...$path);
	}
	
	public static function home(): string
	{
		return Path::home();
	}
	
	public static function rootPath(): Path
	{
		return Path::rootPath();
	}
	
	public static function homePath(): Path
	{
		return Path::homePath();
	}
	
	public static function cleanDirectory(...$path): void
	{
		Path::getPathObject($path)->cleanDirectory();
	}
	
	/**
	 * @param string|Path|array $root
	 * @param array $folders
	 */
	public static function createStructure($root, array $folders): void
	{
		$root = Path::getPathObject($root);
		
		foreach ($folders as $folder)
		{
			$folder = $root->append($folder);
			
			if (!$folder->exists())
			{
				$folder->createDir(true);
			}
		}
	}
}