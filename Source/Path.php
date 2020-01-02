<?php
namespace FileSystem;


use FileSystem\Exceptions\FileSystemException;
use FileSystem\Exceptions\FSDriverException;
use FileSystem\Exceptions\NotADirectoryException;
use FileSystem\Exceptions\NotAFileException;


class Path
{
	/** @var string */
	private $path;
	
	
	private static function getRootDirectory(string $source): string
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
		
		$root = $keepRoot ? self::getRootDirectory($source) : '';
		
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
	
	
	public function get(): string
	{
		return $this->path;
	}
	
	public function name(): string
	{
		$pos = strrpos($this->path, DIRECTORY_SEPARATOR);
		
		// Only root object can end with a directory separator. 
		if ($pos == strlen($this->path) - 1)
		{
			return $this->path;
		}
		
		return substr($this->path, $pos + 1);
	}
	
	public function length(): int
	{
		return strlen($this->path);
	}
	
	public function depth(): int
	{
		$items = explode(DIRECTORY_SEPARATOR, $this->path);
		$items = array_filter($items);
		
		$count = count($items);
		
		return $count ? $count - 1 : 0;
	}
	
	public function getRoot(): string
	{
		return self::getRootDirectory($this->path);
	}
	
	public function getRootPath(): Path
	{
		return new Path($this->getRoot());
	}
	
	
	public function exists(): bool
	{
		return Driver::file_exists($this->path);
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
		if (!$this->isDir())
		{
			return [];
		}
		
		$items = Driver::scandir($this->path, $sorting_order);
		
		if ($excludeSpecial)
		{
			$items = array_diff($items, ['.', '..']);
		}
		
		$paths = [];
		
		foreach ($items as $item)
		{
			$paths[] = $this->append($item);
		}
		
		return $paths;
	}
	
	public function unlink(): void
	{
		Driver::unlink($this->path);
	}
	
	public function tryUnlink(): void
	{
		try { $this->unlink(); } catch (FSDriverException $e) {}
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
		$items = $this->scandir();
  		
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
	
	public function filesize(): int
	{
		return Driver::filesize($this->path);
	}
	
	/**
	 * @param string|Path|array ...$to
	 * @return Path
	 */
	public function copyFile(...$to): Path
	{
		$to = self::getPathObject(...$to);
		
		if ($to->exists() && !$to->isFile())
		{
			throw new NotAFileException($this, 'Copy destination must be a file');
		}
		
		$to->back()->mkdir(true);
		
		Driver::copy($this->path, $to->path);
		
		return $to;
	}
	
	/**
	 * @param string|Path|array ...$to
	 */
	public function copyContent(...$to): void
	{
		if (!$this->isDir())
		{
			throw new NotADirectoryException($this->path, "Can only copy content of a directory");
		}
		
		$to = self::getPathObject(...$to);
		
		if ($to->exists() && !$to->isDir())
		{
			throw new NotADirectoryException($this->path, "Can copy content only into a directory");
		}
		else if (!$to->exists())
		{
			$to->mkdir();
		}
		
		foreach ($this->scandir() as $child)
		{
			if ($child->isLink())
				continue;
			
			if ($child->isFile())
			{
				$child->copy($to->append($child->name()));
			}
			else if ($child->isDir())
			{
				$toDir = $to->append($child->name());
				$toDir->mkdir();
				$child->copyContent($toDir);
			}
		}
	}
	
	public function copy(...$to): Path
	{
		// TODO
	}
	
	public function moveFile(...$to): Path
	{
		$to = self::getPathObject($to);
		$to->back()->mkdir();
		
		Driver::rename($this->path, $to->path);
		
		return $to;
	}
	
	public function move(...$to): Path
	{
		// TODO
	}
	
	public function moveInto(...$directory): Path
	{
		$into = self::getPathObject($directory);
		$into->mkdir();
		$intoPath = $into->append($this->name());
		
		Driver::rename($this->path, $intoPath->path);
		
		return $intoPath;
	}
	
	public function chmod(int $mod): void
	{
		Driver::chmod($this->get(), $mod);
	}
	
	
	/**
	 * @param string|Path|array ...$data
	 * @return Path
	 */
	public function prepend(...$data): Path
	{
		return self::combineToPath($data, $this);
	}
	
	/**
	 * @param string|Path|array ...$data
	 * @return Path
	 */
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
	
	public function isRoot(): bool
	{
		return $this->path == '/' || $this->path == '//';
	}
	
	public function isReadable(): bool
	{
		return Driver::is_readable($this->path);
	}
	
	public function isWritable(): bool
	{
		return Driver::is_writable($this->path);
	}
	
	public function isExecutable(): bool
	{
		return Driver::is_executable($this->path);
	}
	
	public function resolve(): Path
	{
		$result = [];
		$last 	= null;
		$parts	= explode(DIRECTORY_SEPARATOR, $this->path);
		
		$parts = array_values(array_filter($parts));
		
		if (($parts[0] ?? '') === '~')
		{
			$root = '/';
			$result = explode(DIRECTORY_SEPARATOR, self::home());
			$result = array_values(array_filter($result));
			
			array_shift($parts);
		}
		else
		{
			$root = self::getRootDirectory($this->path);
		}
		
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
			else
			{
				$result[] = $part;
				$last = $part;
			}
		}
		
		return self::createSkipCheck($root . implode(DIRECTORY_SEPARATOR, $result));
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
	
	
	public static function getPathObject($from): Path
	{
		if ($from instanceof Path)
		{
			return clone $from;
		}
		else if ($from instanceof AbstractFSElement)
		{
			return $from->getPath();
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