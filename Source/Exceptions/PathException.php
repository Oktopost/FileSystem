<?php
namespace FileSystem\Exceptions;


use FileSystem\Path;


class PathException extends FileSystemException
{
	/** @var Path */
	private $path;
	
	
	public function __construct($path, string $message)
	{
		$this->path = new Path($path);
		
		parent::__construct("With '{$this->path->get()}': $message");
	}
	
	
	public function path(): Path
	{
		return $this->path();
	}
}