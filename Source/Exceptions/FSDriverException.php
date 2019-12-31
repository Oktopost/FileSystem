<?php
namespace FileSystem\Exceptions;


class FSDriverException extends \Exception
{
	public static function throwIfLastErrorNotEmpty(?string $message = null): void
	{
		if (!error_get_last())
			return;
		
		$last = error_get_last();
		$message = $message ? "$message: " : '';
		$message .= "`" . $last['message'] . "`";
		
		throw new FSDriverException($message, $last['type'] ?? 0);
	}
}