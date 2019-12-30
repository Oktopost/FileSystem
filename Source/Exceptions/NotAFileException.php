<?php
namespace FileSystem\Exceptions;


class NotAFileException extends PathException
{
	public function __construct($path, string $message = "")
	{
		parent::__construct($path, "Is not a File! $message");
	}
}