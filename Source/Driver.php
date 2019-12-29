<?php
namespace FileSystem;


use Traitor\TStaticClass;
use FileSystem\Exceptions\FSDriverException;


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
			FSDriverException::throwIfLastErrorNotEmpty($errorMessage);
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
	
	public static function touch(string $path): void
	{
		self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function filesize(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function mkdir(string $pathname, $mode = 0777, $recursive = false): void
	{
		self::execute("Failed to execute mkdir('$pathname')", __FUNCTION__, func_get_args());
	}
	
	public static function scandir(string $directory, ?int $sorting_order = null): array
	{
		return self::execute("Failed to execute scandir('$directory')", __FUNCTION__, func_get_args());
	}
	
	public static function basename(string $path, ?string $suffix = null): string
	{
		return self::execute("Failed to execute basename('$path', ...)", __FUNCTION__, func_get_args());
	}
	
	public static function chgrp(string $path, $group): string
	{
		return self::execute("Failed to change group for '$path' to '$group'", __FUNCTION__, func_get_args());
	}
	
	public static function chmod(string $path, int $mod): string
	{
		return self::execute("Failed to change file mode for '$path' to $mod", __FUNCTION__, func_get_args());
	}
	
	public static function chown(string $path, $user): string
	{
		return self::execute("Failed to change owner for '$path' to '$user'", __FUNCTION__, func_get_args());
	}
	
	public static function copy(string $from, string $to): string
	{
		return self::execute("Failed to copy from '$from' to '$to'", __FUNCTION__, func_get_args());
	}
	
	public static function dirname(string $path, int $level = 1): string
	{
		return self::execute("Failed to execute dirname('$path', $level)", __FUNCTION__, func_get_args());
	}
	
	public static function file_get_contents(string $path, bool $use_include_path = false, 
		int $offset = 0, ?int $maxlen = null) : string
	{
		return self::execute("Could not read file content from '$path'", __FUNCTION__, 
			[
				$path,
				$use_include_path,
				null, // Context
				$offset,
				$maxlen
			]);
	}
	
	public static function file_put_contents(string $path, $data, int $flag = 0): int
	{
		return self::execute("Could not write file content to '$path'", __FUNCTION__, func_get_args());
	}
	
	public static function file(string $path, int $flag = 0): array
	{
		return self::execute("Could not read file content from '$path'", __FUNCTION__, func_get_args());
	}
	
	public static function fileatime(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function filectime(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function filegroup(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function fileinode(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function filemtime(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function fileowner(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function fileperms(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function filetype(string $path): string
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_executable(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_readable(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_uploaded_file(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_writable(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function is_writeable(string $path): bool
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function glob(string $pattern, int $flags = 0): array
	{
		return self::execute("Glob('$pattern', $flags) failed", __FUNCTION__, func_get_args());
	}
	
	public static function lchgrp(string $path, $group): bool
	{
		return self::execute("Failed to execute lchgrp('$path', $group)", __FUNCTION__, func_get_args());
	}
	
	public static function lchown(string $path, $group): bool
	{
		return self::execute("Failed to execute lchgrp('$path', $group)", __FUNCTION__, func_get_args());
	}
	
	public static function link(string $target, string $linkName): bool
	{
		return self::execute("Failed to execute link('$target', '$linkName')", __FUNCTION__, func_get_args());
	}
	
	public static function linkinfo(string $path): int
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function lstat(string $path): array
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function move_uploaded_file(string $from, string $to): bool
	{
		return self::execute("Failed to execute move_uploaded_file('$from', '$to')", __FUNCTION__, func_get_args());
	}
	
	public static function pathinfo(string $path, int $options = null): bool
	{
		return self::execute("Failed to execute pathinfo('$path', ...)", __FUNCTION__, func_get_args());
	}
	
	public static function readlink(string $path): array
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function rename(string $from, string $to): bool
	{
		return self::execute("Failed to execute rename('$from', '$to')", __FUNCTION__, func_get_args());
	}
	
	public static function stat(string $path): array
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
	
	public static function symlink(string $target, string $linkName): bool
	{
		return self::execute("Failed to execute symlink('$target', '$linkName')", __FUNCTION__, func_get_args());
	}
	
	public static function umask(string $path): array
	{
		return self::executeOnPath(__FUNCTION__, $path);
	}
}