<?php
namespace FileSystem;


use FileSystem\Exceptions\FileSystemException;
use Traitor\TStaticClass;


class Driver
{
	use TStaticClass;
	
	
	/**
	 * @param string $errorMessage
	 * @param callable $callback
	 * @param array $params
	 * @return mixed
	 */
	private static function execute(string $errorMessage, callable $callback, array $params)
	{
		error_clear_last();
		
		try
		{
			return @$callback(...$params);
		}
		finally
		{
			FileSystemException::throwIfLastErrorNotEmpty($errorMessage);
		}
	}
	
	/**
	 * @param string $func
	 * @param string $path
	 * @return mixed
	 */
	private static function executeOnPath(string $func, string $path)
	{
		return self::execute("Failed to execute $func('$path')", $func, [$path]);
	}
	
	
	public static function is_dir(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_file(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_link(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function file_exists(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function rmdir(string $path): void
	{
		self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function unlink(string $path): void
	{
		self::executeOnPath(__FUNCTION__, $path);
	}
	
	
	public static function mkdir(string $pathname, $mode = 0777, $recursive = false): void
	{
		self::execute("Failed to mkdir('$pathname')", __FUNCTION__, func_get_args());
	}
	
	public static function scandir(string $directory, ?int $sorting_order = null): array
	{
		return self::execute("Failed to scandir('$directory')", __FUNCTION__, func_get_args());
	}
}