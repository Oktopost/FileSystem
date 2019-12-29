<?php
namespace FileSystem;


use FileSystem\Exceptions\FileSystemException;


class Path
{
	/** @var string */
	private $path;
	
	
	private static function getRoot(string $source): string
	{
		// Linux rules:
		// '///' = '/'
		// '//' = '//'
		// '/' = '/'
		
		if (!$source || $source == '/' || $source == '//')
		{
			return $source;
		}
		else if ($source[0] != '/')
		{
			return '';
		}
		else if ($source[1] == '/')
		{
			return $source[2] == '/' ? '/' : '//';
		}
		
		return '/';
	}
	
	private static function fixSlashes(string $source, string $root): string
	{
		$parts = explode(DIRECTORY_SEPARATOR, $source);
		$parts = array_filter($parts);
		
		return $root . implode(DIRECTORY_SEPARATOR, $parts);
	}
	
	private static function partsToString(bool $keepRoot, ...$with): string
	{
		$result = '';
		
		foreach ($with as $part)
		{
			$part = self::pathToString($part, $keepRoot);
			$keepRoot = false;
			
			if ($result && $part && $part[0] != DIRECTORY_SEPARATOR && 
				$result != '/' && $result != '//')
			{
				$part = DIRECTORY_SEPARATOR . $part;
			}
			
			$result .= $part;
		}
		
		return $result;
	}
	
	private static function pathToString($source, bool $keepRoot): string
	{
		if (is_array($source))
		{
			return self::partsToString($keepRoot, ...$source);
		}
		else if ($source instanceof Path)
		{
			$source = $source->get();
		}
		else if (!is_string($source))
		{
			throw new FileSystemException(
				'Invalid parameter passed. Expecting string or \FileSystem\Path object');
		}
		
		$root = $keepRoot ? self::getRoot($source) : '';
		
		return self::fixSlashes($source, $root);
	}
	
	private static function createSkipCheck(string $path): Path
	{
		$pathObject = new Path();
		$pathObject->path = $path;
		return $pathObject;
	}
	
	
	public function __construct(...$path)
	{
		$this->path = self::combine(...$path);
	}
	
	public function __toString()
	{
		return $this->path;
	}
	
	public function __clone()
	{
		
	}
	
	
	public function exists(): bool
	{
		return 
			Driver::file_exists($this->path) || 
			Driver::is_dir($this->path);
	}
	
	public function isFile(): bool
	{
		return Driver::is_file($this->path);
	}
	
	public function isDir(): bool
	{
		return Driver::is_dir($this->path);
	}
	
	public function isLink(): bool
	{
		return Driver::is_link($this->path);
	}
	
	/**
	 * @param bool $excludeSpecial
	 * @param int $sorting_order
	 * @return Path[]
	 */
	public function scandir(bool $excludeSpecial = true, int $sorting_order = SCANDIR_SORT_ASCENDING): array
	{
		$items = Driver::scandir($this->path);
		
		if ($excludeSpecial)
		{
			$items = array_diff(Driver::scandir($this->path), ['.', '..']);
		}
		
		$paths = [];
		
		foreach ($items as $item)
		{
			$paths[] = $this->append($item);
		}
		
		return $paths;
	}
	
	/**
	 * @param bool $excludeSpecial
	 * @param int $sorting_order
	 * @return Path[]
	 */
	public function scandirIfExists(bool $excludeSpecial = true, int $sorting_order = SCANDIR_SORT_ASCENDING): array
	{
		if (!$this->isDir())
		{
			return [];
		}
		
		return $this->scandir($excludeSpecial, $sorting_order);
	}
	
	public function unlink(): void
	{
		Driver::unlink($this->path);
	}
	
	public function rmdir(): void
	{
		Driver::rmdir($this->path);
	}
	
	public function delete(bool $recursive = true): void
	{
		if ($this->isDir())
		{
			if ($recursive)
			{
				$this->cleanDirectory();
			}
			
			$this->rmdir();
		}
		else if ($this->isFile())
		{
			$this->unlink();
		}
	}
	
	public function cleanDirectory(bool $followLink = false): void
	{
		$items = $this->scandirIfExists();
  		
		foreach ($items as $item) 
		{
			if ($item->isLink())
			{
				if ($followLink && $item->isDir())
				{
					$item->delete();
				}
				
				$item->unlink();
			}
			else
			{
				$item->delete();
			}
		}
	}
	
	
	public function get(): string
	{
		return $this->path;
	}
	
	public function prepend(...$data): Path
	{
		return self::combineToPath($data, $this);
	}
	
	public function append(...$data): Path
	{
		return self::combineToPath($this, $data);
	}
	
	public function back(): Path
	{
		$pos = strrpos($this->path, DIRECTORY_SEPARATOR);
		
		if ($pos === false)
		{
			return new Path();
		}
		else if ($pos == 0)
		{
			return self::createSkipCheck(DIRECTORY_SEPARATOR);
		}
		else if ($pos == 1 && $this->path[0] == DIRECTORY_SEPARATOR)
		{
			return self::createSkipCheck('//');
		}
		
		return self::createSkipCheck(substr($this->path, 0, $pos));
	}
	
	public function isRelative(): bool
	{
		return !$this->path || $this->path[0] != '/';
	}
	
	public function resolve(): Path
	{
		$root = self::getRoot($this->path);
		
		$result = [];
		$last 	= null;
		$parts	= explode(DIRECTORY_SEPARATOR, $this->path);
		
		$parts = array_values(array_filter($parts));
		
		foreach ($parts as $part)
		{
			if ($part == '.')
			{
				continue;
			}
			else if ($part == '..')
			{
				if ($result && $last != '..')
				{
					array_pop($result);
				}
				else if (!$root)
				{
					$result[] = $part;
					$last = $part;
				}
			}
			else if ($part == '~')
			{
				$result = explode(DIRECTORY_SEPARATOR, self::home());
				$result = array_values(array_filter($result));
			}
			else
			{
				$result[] = $part;
				$last = $part;
			}
		}
		
		return self::createSkipCheck($root . $result);
	}
	
	public function mkdir(bool $recursive = true): Dir
	{
		if (!$this->isDir())
		{
			Driver::mkdir($this->path, 0777, $recursive);
		}
		
		return new Dir($this);
	}
	
	public function touch(bool $recursive = true): File
	{
		$dir = $this->back();
		
		if (!$dir->isDir())
		{
			if ($recursive)
			{
				$dir->mkdir(true);
			}
			else
			{
				throw new FileSystemException("Can not create file $this because the directory $dir does not exists");
			}
		}
		
		Driver::touch($this->path);
		
		return new File($this);
	}
	
	public function isEmpty(): bool
	{
		if ($this->isDir())
		{
			$items = Driver::scandir($this->path);
			return !((bool)array_diff($items, ['.', '..']));
		}
		else if ($this->isFile())
		{
			return $this->filesize() == 0;
		}
		else
		{
			return true;
		}
	}
	
	public function filesize(): int
	{
		return Driver::filesize($this->path);
	}
	
	
	public static function getPathObject($from): Path
	{
		if ($from instanceof Path)
		{
			return $from;
		}
		else if (is_array($from) && $from && reset($from) instanceof Path)
		{
			return reset($from);
		}
		
		return self::combineToPath($from);
	}
	
	public static function combine(...$with): string
	{
		return self::partsToString(true, ...$with);
	}
	
	public static function combineToPath(...$with): Path
	{
		return self::createSkipCheck(self::partsToString(true, ...$with));
	}
	
	public static function realpath(...$with): string
	{
		return (new Path(true, ...$with))->resolve();
	}
	
	public static function home(): string
	{
		return $_SERVER['HOME'];
	}
	
	public static function rootPath(): Path
	{
		return new Path('/');
	}
	
	public static function homePath(): Path
	{
		return new Path(self::home());
	}
}