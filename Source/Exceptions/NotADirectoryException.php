<?php
namespace FileSystem\Exceptions;


class NotADirectoryException extends FileSystemException
{
	public function __construct(string $path, string $message = "")
	{
		parent::__construct("'$path' is not a directory! $message");
	}
}