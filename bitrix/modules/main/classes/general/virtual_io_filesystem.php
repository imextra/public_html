<?
class CBXVirtualIoFileSystem
	implements IBXVirtualIO, IBXGetErrors
{
	private static $systemEncoding;
	private static $serverEncoding;

	const directionEncode = 1;
	const directionDecode = 2;

	private $arErrors = array();

	public static function ConvertCharset($string, $direction = 1)
	{
		if (is_null(self::$systemEncoding))
		{
			self::$systemEncoding = strtolower(defined("BX_FILE_SYSTEM_ENCODING") ? BX_FILE_SYSTEM_ENCODING : "");
			if (empty(self::$systemEncoding))
			{
				if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN")
					self::$systemEncoding = "windows-1251";
				else
					self::$systemEncoding = "utf-8";
			}
		}

		if (is_null(self::$serverEncoding))
		{
			if (defined('BX_UTF'))
				self::$serverEncoding = "utf-8";
			elseif (defined("SITE_CHARSET") && (strlen(SITE_CHARSET) > 0))
				self::$serverEncoding = SITE_CHARSET;
			elseif (defined("LANG_CHARSET") && (strlen(LANG_CHARSET) > 0))
				self::$serverEncoding = LANG_CHARSET;
			else
				self::$serverEncoding = "windows-1251";

			self::$serverEncoding = strtolower(self::$serverEncoding);
		}

		if (self::$serverEncoding == self::$systemEncoding)
			return $string;

		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/charset_converter.php");
		if ($direction == self::directionEncode)
			return CharsetConverter::ConvertCharset($string, self::$serverEncoding, self::$systemEncoding);
		else
			return CharsetConverter::ConvertCharset($string, self::$systemEncoding, self::$serverEncoding);
	}

	public function CombinePath()
	{
		$numArgs = func_num_args();
		if ($numArgs <= 0)
			return "";

		$arParts = array();
		for ($i = 0; $i < $numArgs; $i++)
		{
			$arg = func_get_arg($i);
			if (empty($arg))
				continue;

			if (is_array($arg))
			{
				foreach ($arg as $v)
				{
					if (empty($v))
						continue;
					$arParts[] = $v;
				}
			}
			else
			{
				$arParts[] = $arg;
			}
		}

		$result = "";
		foreach ($arParts as $part)
		{
			if (!empty($result))
				$result .= "/";
			$result .= $part;
		}

		$result = self::FormatPath($result);

		return $result;
	}

	public function RelativeToAbsolutePath($relativePath)
	{
		if (empty($relativePath))
			return null;

		$basePath = $_SERVER["DOCUMENT_ROOT"];

		return $this->CombinePath($basePath, $relativePath);
	}

	public function SiteRelativeToAbsolutePath($relativePath, $site = null)
	{
		if ((string)$site === "")
		{
			$site = SITE_ID;
		}
		else
		{
			$dbSite = CSite::GetByID($site);
			$site = "";
			if ($arSite = $dbSite->Fetch())
				$site = $_REQUEST["site"];
			if ((string)$site === "")
				$site = SITE_ID;
		}

		$basePath = CSite::GetSiteDocRoot($site);

		return $this->CombinePath($basePath, $relativePath);
	}

	public function GetPhysicalName($path)
	{
		return CBXVirtualIoFileSystem::ConvertCharset($path);
	}

	function GetLogicalName($path)
	{
		return CBXVirtualIoFileSystem::ConvertCharset($path, self::directionDecode);
	}

	public function ExtractNameFromPath($path)
	{
		$path = rtrim($path, "\\/");
		if (preg_match("#[^\\\\/]+$#", $path, $match))
			$path = $match[0];
		return $path;
	}

	public function ExtractPathFromPath($path)
	{
		return substr($path, 0, -strlen($this->ExtractNameFromPath($path)) - 1);
	}

	private function FormatPath($path)
	{
		if (empty($path))
			return null;

		$path = preg_replace("'[\\\/]+'", "/", $path);

		if (($p = strpos($path, "\0")) !== false)
			$path = substr($path, 0, $p);

		while (strpos($path, "/./") !== false)
			$path = str_replace("/./", "/", $path);

		while (($pos = strpos($path, "../")) !== false)
		{
			$lp = substr($path, 0, $pos - 1);
			$posl = bxstrrpos($lp, "/");
			if ($posl === false)
				return null;
			$lp = substr($lp, 0, $posl + 1);
			$rp = substr($path, $pos + 3);
			$path = $lp.$rp;
		}

		$path = preg_replace("'[\\\/]+'", "/", $path);

		$path = rtrim($path, "\0/.");
		if ($path === "")
			$path = "/";

		return $path;
	}

	function ValidatePathString($path)
	{
		$p = trim($path);
		if (empty($p))
			return false;

		if (strpos($path, "\0") !== false)
			return false;

		return (preg_match("#^([a-z]:)?/([^\\\/:*?\"\'<>|]+/?)*$#is", $path) > 0);
	}

	function ValidateFilenameString($filename)
	{
		$fn = trim($filename);
		if (empty($fn))
			return false;

		if (strpos($filename, "\0") !== false)
			return false;

		return (preg_match("#^[^\\\/:*?\"\'<>|]+$#is", $filename) > 0);
	}

	public function DirectoryExists($path)
	{
		$path = CBXVirtualIoFileSystem::ConvertCharset($path);
		return file_exists($path) && is_dir($path);
	}

	public function FileExists($path)
	{
		$path = CBXVirtualIoFileSystem::ConvertCharset($path);
		return file_exists($path) && is_file($path);
	}

	public function GetDirectory($path)
	{
		return new CBXVirtualDirectoryFileSystem($path);
	}

	public function GetFile($path)
	{
		return new CBXVirtualFileFileSystem($path);
	}

	public function OpenFile($path, $mode)
	{
		$file = $this->GetFile($path);
		return $file->Open($mode);
	}

	public function Delete($path)
	{
		$this->ClearErrors();

		if (substr($path, 0, strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
		{
			$pathTmp = substr($path, strlen($_SERVER["DOCUMENT_ROOT"]));
			if (empty($pathTmp) || $pathTmp == '/')
			{
				$this->AddError("Can not delete the root folder of the project");
				return false;
			}
		}

		$pathEncoded = CBXVirtualIoFileSystem::ConvertCharset($path);

		$f = true;
		if (is_file($pathEncoded) || is_link($pathEncoded))
		{
			if (@unlink($pathEncoded))
				return true;

			$this->AddError(sprintf("Can not delete file '%s'", $path));
			return false;
		}
		elseif (is_dir($pathEncoded))
		{
			if ($handle = opendir($pathEncoded))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file == "." || $file == "..")
						continue;

					$pathDecodedTmp = CBXVirtualIoFileSystem::ConvertCharset($pathEncoded."/".$file, CBXVirtualIoFileSystem::directionDecode);
					if (!$this->Delete($pathDecodedTmp))
						$f = false;
				}
				closedir($handle);
			}
			if (!@rmdir($pathEncoded))
			{
				$this->AddError(sprintf("Can not delete directory '%s'", $path));
				return false;
			}

			return $f;
		}

		$this->AddError("Unknown error");
		return false;
	}

	private function CopyDirFiles($pathFrom, $pathTo, $bRewrite = true, $bDeleteAfterCopy = false)
	{
		$this->ClearErrors();

		if (strpos($pathTo."/", $pathFrom."/") === 0)
		{
			$this->AddError("Can not copy a file onto itself");
			return false;
		}

		$pathFromEncoded = CBXVirtualIoFileSystem::ConvertCharset($pathFrom);
		if (is_dir($pathFromEncoded))
		{
			$this->CreateDirectory($pathTo);
		}
		elseif (is_file($pathFromEncoded))
		{
			$this->CreateDirectory($this->ExtractPathFromPath($pathTo));

			$pathToEncoded = CBXVirtualIoFileSystem::ConvertCharset($pathTo);
			if (file_exists($pathToEncoded) && !$bRewrite)
			{
				$this->AddError(sprintf("The file '%s' already exists", $pathTo));
				return false;
			}

			@copy($pathFromEncoded, $pathToEncoded);
			if (is_file($pathToEncoded))
				@chmod($pathToEncoded, BX_FILE_PERMISSIONS);

			if ($bDeleteAfterCopy)
				@unlink($pathFromEncoded);

			return true;
		}
		else
		{
			return true;
		}

		$pathToEncoded = CBXVirtualIoFileSystem::ConvertCharset($pathTo);
		if ($handle = @opendir($pathFromEncoded))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($pathFromEncoded."/".$file))
				{
					$pathFromDecodedTmp = CBXVirtualIoFileSystem::ConvertCharset($pathFromEncoded."/".$file, CBXVirtualIoFileSystem::directionDecode);
					$pathToDecodedTmp = CBXVirtualIoFileSystem::ConvertCharset($pathToEncoded."/".$file, CBXVirtualIoFileSystem::directionDecode);
					CopyDirFiles($pathFromDecodedTmp, $pathToDecodedTmp, $bRewrite, $bDeleteAfterCopy);
					if ($bDeleteAfterCopy)
						@rmdir($pathFromEncoded."/".$file);
				}
				elseif (is_file($pathFromEncoded."/".$file))
				{
					if (file_exists($pathToEncoded."/".$file) && !$bRewrite)
						continue;

					@copy($pathFromEncoded."/".$file, $pathToEncoded."/".$file);
					@chmod($pathToEncoded."/".$file, BX_FILE_PERMISSIONS);

					if ($bDeleteAfterCopy)
						@unlink($pathFromEncoded."/".$file);
				}
			}
			@closedir($handle);

			if ($bDeleteAfterCopy)
				@rmdir($pathFromEncoded);

			return true;
		}

		$this->AddError(sprintf("Can not open directory '%s'", $pathFrom));
		return false;
	}

	public function Copy($source, $target, $bRewrite = true)
	{
		return $this->CopyDirFiles($source, $target, $bRewrite, false);
	}

	public function Move($source, $target, $bRewrite = true)
	{
		return $this->CopyDirFiles($source, $target, $bRewrite, true);
	}

	public function Rename($source, $target)
	{
		$sourceEncoded = CBXVirtualIoFileSystem::ConvertCharset($source);
		$targetEncoded = CBXVirtualIoFileSystem::ConvertCharset($target);
		return rename($sourceEncoded, $targetEncoded);
	}

	public function CreateDirectory($path)
	{
		$fld = $this->GetDirectory($path);
		if ($fld->Create())
			return $fld;

		return null;
	}

	function ClearCache()
	{
		clearstatcache();
	}

	public function GetErrors()
	{
		return $this->arErrors;
	}

	protected function AddError($error, $errorCode = "")
	{
		if (empty($error))
			return;

		$fs = (empty($errorCode) ? "%s" : "[%s] %s");
		$this->arErrors[] = sprintf($fs, $error, $errorCode);
	}

	protected function ClearErrors()
	{
		$this->arErrors = array();
	}
}

class CBXVirtualFileFileSystem
	extends CBXVirtualFile
{
	protected $pathEncoded = null;
	private $arErrors = array();

	protected function GetPathWithNameEncoded()
	{
		if (is_null($this->pathEncoded))
			$this->pathEncoded = CBXVirtualIoFileSystem::ConvertCharset($this->path);

		return $this->pathEncoded;
	}

	public function Open($mode)
	{
		if ($this->IsExists())
			return fopen($this->GetPathWithNameEncoded(), $mode);

		return null;
	}

	public function GetContents()
	{
		if ($this->IsExists())
			return file_get_contents($this->GetPathWithNameEncoded());

		return null;
	}

	public function PutContents($data)
	{
		$this->ClearErrors();

		$io = CBXVirtualIo::GetInstance();
		$dir = $io->CreateDirectory($this->GetPath());
		if (is_null($dir))
		{
			$this->AddError(sprintf("Can not create directory '%s' or access denied", $this->GetPath()));
			return false;
		}

		if ($this->IsExists() && !$this->IsWritable())
			$this->MarkWritable();

		$fd = fopen($this->GetPathWithNameEncoded(), "wb");
		if (!$fd)
		{
			$this->AddError(sprintf("Can not open file '%s' for writing", $this->GetPathWithNameEncoded()));
			return false;
		}
		if (fwrite($fd, $data) === false)
		{
			$this->AddError(sprintf("Can not write %d bytes to file '%s'", strlen($data), $this->GetPathWithNameEncoded()));
			fclose($fd);
			return false;
		}
		fclose($fd);

		return true;
	}

	public function GetFileSize()
	{
		if ($this->IsExists())
			return intval(filesize($this->GetPathWithNameEncoded()));

		return 0;
	}

	public function GetModificationTime()
	{
		if ($this->IsExists())
			return filemtime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetLastAccessTime()
	{
		if ($this->IsExists())
			return fileatime($this->GetPathWithNameEncoded());

		return null;
	}

	public function IsWritable()
	{
		return is_writable($this->GetPathWithNameEncoded());
	}

	public function IsReadable()
	{
		return is_readable($this->GetPathWithNameEncoded());
	}

	public function MarkWritable()
	{
		if ($this->IsExists())
			@chmod($this->GetPathWithNameEncoded(), BX_FILE_PERMISSIONS);
	}

	public function IsExists()
	{
		$io = CBXVirtualIo::GetInstance();
		return $io->FileExists($this->path);
	}

	public function GetPermissions()
	{
		return fileperms($this->GetPathWithNameEncoded());
	}

	public function ReadFile()
	{
		return readfile($this->GetPathWithNameEncoded());
	}

	public function GetErrors()
	{
		return $this->arErrors;
	}

	protected function AddError($error, $errorCode = "")
	{
		if (empty($error))
			return;

		$fs = (empty($errorCode) ? "%s" : "[%s] %s");
		$this->arErrors[] = sprintf($fs, $error, $errorCode);
	}

	protected function ClearErrors()
	{
		$this->arErrors = array();
	}
}

class CBXVirtualDirectoryFileSystem
	extends CBXVirtualDirectory
{
	protected $pathEncoded = null;
	private $arErrors = array();

	protected function GetPathWithNameEncoded()
	{
		if (is_null($this->pathEncoded))
			$this->pathEncoded = CBXVirtualIoFileSystem::ConvertCharset($this->path);

		return $this->pathEncoded;
	}

	public function GetChildren()
	{
		$arResult = array();

		if (!$this->IsExists())
			return $arResult;

		if ($handle = opendir($this->GetPathWithNameEncoded()))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				$pathDecoded = CBXVirtualIoFileSystem::ConvertCharset($this->GetPathWithNameEncoded()."/".$file, CBXVirtualIoFileSystem::directionDecode);
				if (is_dir($this->GetPathWithNameEncoded()."/".$file))
					$arResult[] = new CBXVirtualDirectoryFileSystem($pathDecoded);
				else
					$arResult[] = new CBXVirtualFileFileSystem($pathDecoded);
			}
			closedir($handle);
		}

		return $arResult;
	}

	public function Create()
	{
		if (!file_exists($this->GetPathWithNameEncoded()))
			return mkdir($this->GetPathWithNameEncoded(), BX_DIR_PERMISSIONS, true);
		else
			return is_dir($this->GetPathWithNameEncoded());
	}

	public function IsExists()
	{
		$io = CBXVirtualIo::GetInstance();
		return $io->DirectoryExists($this->path);
	}

	public function MarkWritable()
	{
		if ($this->IsExists())
			@chmod($this->GetPathWithNameEncoded(), BX_DIR_PERMISSIONS);
	}

	public function GetPermissions()
	{
		return fileperms($this->GetPathWithNameEncoded());
	}

	public function GetModificationTime()
	{
		if ($this->IsExists())
			return filemtime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetLastAccessTime()
	{
		if ($this->IsExists())
			return fileatime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetErrors()
	{
		return $this->arErrors;
	}

	protected function AddError($error, $errorCode = "")
	{
		if (empty($error))
			return;

		$fs = (empty($errorCode) ? "%s" : "[%s] %s");
		$this->arErrors[] = sprintf($fs, $error, $errorCode);
	}

	protected function ClearErrors()
	{
		$this->arErrors = array();
	}
}
?>