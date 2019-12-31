<?php
namespace FileSystem;


use Traitor\TStaticClass;


class FS
{
	use TStaticClass;
	
	
	/**
	 * @param string|Path|array $root
	 * @param string|Path|array|null $folders
	 * @param string|Path|array|null $files
	 */
	public static function create($root, array $folders = [], array $files = []): void
	{
		$root = Path::getPathObject($root);
		
		if (!$folders && !$files)
		{
			$folders = [''];
		}
		
		foreach ($folders as $folder)
		{
			$folder = $root->append($folder);
			
			if (!$folder->exists())
			{
				$folder->mkdir(true);
			}
		}
		
		foreach ($files as $file)
		{
			$file = $root->append($file);
			
			if (!$file->exists())
			{
				$file->touch(true);
			}
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
	
	public static function delete(...$path): void
	{
		self::path(...$path)->delete();
	}
	
	public static function unlink(...$path): void
	{
		self::path(...$path)->unlink();
	}
	
	public static function rmdir(...$path): void
	{
		self::path(...$path)->rmdir();
	}
	
	public static function mkdir($path, bool $recursive = true): void
	{
		self::path(...$path)->mkdir($recursive);
	}
	
	public static function touch($path, bool $recursive = true): void
	{
		self::path(...$path)->touch($recursive);
	}
	
	public static function filesize(...$path): int
	{
		return self::path(...$path)->filesize();
	}
	
	public static function touchAll(array $paths, bool $recursive = true): void
	{
		foreach ($paths as $path)
		{
			self::path($path)->touch($recursive);
		}
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
	
	public static function scandir($path, bool $excludeSpecial = true, int $order = SCANDIR_SORT_ASCENDING): array
	{
		return Path::getPathObject($path)->scandir($excludeSpecial, $order);
	}
	
	/*
	public static function copy($from, $to): Path
	{
		return Path::getPathObject($from)->copy($to);
	}
	*/
	
	public static function copyFile($from, $to): Path
	{
		return Path::getPathObject($from)->copyFile($to);
	}
	
	public static function copyContent($from, $to): void
	{
		Path::getPathObject($from)->copyContent($to);
	}
}