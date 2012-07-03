<?
/*.
	require_module 'standard';
	require_module 'pcre';
	require_module 'hash';
	require_module 'bitrix_main';
	require_module 'bitrix_clouds_classes_storage_service';
	require_module 'bitrix_clouds_classes_storage_bucket';
.*/
IncludeModuleLangFile(__FILE__);

class CCloudStorageUpload
{
	protected /*.string.*/ $_filePath = "";
	protected /*.string.*/ $_ID = "";
	protected /*.CCloudStorageBucket.*/ $obBucket;
	protected /*.int.*/ $_max_retries = 3;
	protected /*.array[string]string.*/ $_cache = null;

	/**
	 * @param string $filePath
	 * @return void
	*/
	function __construct($filePath)
	{
		$this->_filePath = $filePath;
		$this->_ID = "1".substr(md5($filePath), 1);
	}

	/**
	 * @return array[string]string
	*/
	public function GetArray()
	{
		global $DB;

		if(!isset($this->_cache))
		{
			$rs = $DB->Query("
				SELECT *
				FROM b_clouds_file_upload
				WHERE ID = '".$this->_ID."'
			");
			$this->_cache = $rs->Fetch();
		}

		return $this->_cache;
	}

	/**
	 * @return bool
	*/
	public function isStarted()
	{
		return is_array($this->GetArray());
	}

	/**
	 * @return void
	*/
	public function Delete()
	{
		global $DB;
		$DB->Query("DELETE FROM b_clouds_file_upload WHERE ID = '".$this->_ID."'");
		unset($this->_cache);
	}

	/**
	 * @return void
	*/
	public function DeleteOld()
	{
		global $DB;
		$DB->Query("DELETE FROM b_clouds_file_upload WHERE TIMESTAMP_X < ".$DB->CharToDateFunction(ConvertTimeStamp(time()-24*60*60)));
	}

	/**
	 * @param int $bucket_id
	 * @param float $fileSize
	 * @param string $ContentType
	 * @return bool
	*/
	function Start($bucket_id, $fileSize, $ContentType = 'binary/octet-stream')
	{
		global $DB;

		$obBucket = new CCloudStorageBucket(intval($bucket_id));
		if(!$obBucket->Init())
			return false;

		if(!$this->isStarted())
		{
			$arUploadInfo = /*.(array[string]string).*/array();
			$bStarted = $obBucket->GetService()->InitiateMultipartUpload(
				$obBucket->GetBucketArray(),
				$arUploadInfo,
				$this->_filePath,
				$fileSize,
				$ContentType
			);

			if($bStarted)
			{
				$bAdded = $DB->Add("b_clouds_file_upload", array(
					"ID" => $this->_ID,
					"~TIMESTAMP_X" => $DB->CurrentTimeFunction(),
					"FILE_PATH" => $this->_filePath,
					"BUCKET_ID" => $obBucket->ID,
					"PART_SIZE" => $obBucket->GetService()->GetMinUploadPartSize(),
					"PART_NO" => 0,
					"PART_FAIL_COUNTER" => 0,
					"NEXT_STEP" => serialize($arUploadInfo),
				), array("NEXT_STEP"));
				unset($this->_cache);

				return $bAdded !== false;
			}
		}

		return false;
	}

	/**
	 * @param string $data
	 * @return bool
	*/
	function Next($data)
	{
		global $DB;

		if($this->isStarted())
		{
			$ar = $this->GetArray();

			$obBucket = new CCloudStorageBucket(intval($ar["BUCKET_ID"]));
			if(!$obBucket->Init())
				return false;

			$arUploadInfo = unserialize($ar["NEXT_STEP"]);
			$bSuccess = $obBucket->GetService()->UploadPart(
				$obBucket->GetBucketArray(),
				$arUploadInfo,
				$data
			);

			if($bSuccess)
			{
				$arFields = array(
					"NEXT_STEP" => serialize($arUploadInfo),
					"~PART_NO" => "PART_NO + 1",
					"PART_FAIL_COUNTER" => 0,
				);
				$arBinds = array(
					"NEXT_STEP" => $arFields["NEXT_STEP"],
				);
			}
			else
			{
				$arFields = array(
					"~PART_FAIL_COUNTER" => "PART_FAIL_COUNTER + 1",
				);
				$arBinds = array(
				);
			}

			$strUpdate = $DB->PrepareUpdate("b_clouds_file_upload", $arFields);
			$strSql = "UPDATE b_clouds_file_upload SET ".$strUpdate." WHERE ID = '".$this->_ID."'";
			if(!$DB->QueryBind($strSql, $arBinds))
				return false;

			unset($this->_cache);

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	*/
	function Finish()
	{
		if($this->isStarted())
		{
			$ar = $this->GetArray();

			$obBucket = new CCloudStorageBucket(intval($ar["BUCKET_ID"]));
			if(!$obBucket->Init())
				return false;

			$arUploadInfo = unserialize($ar["NEXT_STEP"]);
			$bSuccess = $obBucket->GetService()->CompleteMultipartUpload(
				$obBucket->GetBucketArray(),
				$arUploadInfo
			);

			if($bSuccess)
				$this->Delete();

			$this->DeleteOld();

			return $bSuccess;
		}

		return false;
	}

	/**
	 * @return int
	*/
	function GetPartCount()
	{
		$ar = $this->GetArray();

		if(is_array($ar))
			return intval($ar["PART_NO"]);
		else
			return 0;
	}

	/**
	 * @return float
	*/
	function GetPos()
	{
		$ar = $this->GetArray();

		if(is_array($ar))
			return intval($ar["PART_NO"])*doubleval($ar["PART_SIZE"]);
		else
			return 0;
	}

	/**
	 * @return int
	*/
	function getPartSize()
	{
		$ar = $this->GetArray();

		if(is_array($ar))
			return intval($ar["PART_SIZE"]);
		else
			return 0;
	}

	/**
	 * @return bool
	*/
	function hasRetries()
	{
		$ar = $this->GetArray();
		return is_array($ar) && (intval($ar["PART_FAIL_COUNTER"]) < $this->_max_retries);
	}
}
?>