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
			$this->path = Path::combineToPath(($path ?: '/'));
		}
		else
		{
			$this->path = new Path();
		}
	}
	
	
	public function getPath(): Path
	{
		return $this->path;
	}
}