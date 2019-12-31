<?php
namespace FileSystem;


use Structura\Random;
use FileSystem\Exceptions\PathException;


class TempFile
{
	private $wasUnlinked = false;
	
	/** @var Path */
	private $path;
	
	
	public function __destruct()
	{
		if (!$this->wasUnlinked)
		{
			$this->path->tryUnlink();
		}
	}
	
	public function __construct(Path $path)
	{
		$this->path = $path;
		
		if ($path->exists())
		{
			if (!$path->isFile())
			{
				throw new PathException($path, 'Can not create a temporary file in place of a directory');
			}
		}
	}
	
	public function __toString()
	{
		return (string)$this->path();
	}
	
	
	public function path(): Path
	{
		return clone $this->path;
	}
	
	public function exists(): bool
	{
		return $this->path->exists();
	}
	
	public function delete(): void
	{
		if (!$this->wasUnlinked)
		{
			$this->wasUnlinked = true;
			$this->path->unlink();
		}
	}
	
	public function touch(): void
	{
		$this->wasUnlinked = false;
		$this->path->touch(true);
	}
	
	
	public static function create($in, bool $touch = false, 
		?string $prefix = '_ok_fs_', ?string $suffix = '.tmp'): TempFile
	{
		$dir = Path::getPathObject($in);
		$name = $prefix . Random::string(64) . $suffix;
		
		$path = $dir->append($name);
		$temp = new TempFile($path);
		
		if ($touch)
		{
			$temp->touch();
		}
		
		return $temp;
	}
}