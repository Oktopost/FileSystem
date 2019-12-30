<?php
namespace FileSystem\Exceptions;


class NotADirectoryException extends PathException
{
	public function __construct($path, string $message = "")
	{
		parent::__construct($path, "Is not a directory! $message");
	}
}