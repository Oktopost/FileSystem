<?php
namespace FileSystem;


class AbstractFSElement
{
	/** @var Path */
	private $path;
	
	
	public function __construct(...$path)
	{
		if ($path)
		{
			if (count($path) == 1 && reset($path) instanceof Path)
			{
				$this->path = clone reset($path);
			}
			else
			{
				$this->path = Path::combineToPath(($path ?: '/'));
			}
		}
		else
		{
			$this->path = new Path();
		}
	}
	
	
	public function getPath(): Path
	{
		return clone $this->path;
	}
}