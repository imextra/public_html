<?
$MESS["MAIN_DUMP_FILE_CNT"] = "Files compressed:";
$MESS["MAIN_DUMP_FILE_SIZE"] = "Files size:";
$MESS["MAIN_DUMP_FILE_FINISH"] = "Backup completed";
$MESS["MAIN_DUMP_FILE_MAX_SIZE"] = "Do not include files which size exceeds (0 - no limit): ";
$MESS["MAIN_DUMP_FILE_STEP"] = "Step:";
$MESS["MAIN_DUMP_FILE_STEP_SLEEP"] = "interval:";
$MESS["MAIN_DUMP_FILE_STEP_sec"] = "sec";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_b"] = "B";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_kb"] = "kB";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_mb"] = "MB ";
$MESS["MAIN_DUMP_FILE_MAX_SIZE_gb"] = "GB ";
$MESS["MAIN_DUMP_FILE_DUMP_BUTTON"] = "Back up";
$MESS["MAIN_DUMP_FILE_STOP_BUTTON"] = "Stop";
$MESS["MAIN_DUMP_FILE_KERNEL"] = "Back up kernel files:";
$MESS["MAIN_DUMP_FILE_NAME"] = "Filename";
$MESS["FILE_SIZE"] = "File Size";
$MESS["MAIN_DUMP_FILE_TIMESTAMP"] = "Modified";
$MESS["MAIN_DUMP_FILE_PUBLIC"] = "Back up public files:";
$MESS["MAIN_DUMP_FILE_TITLE"] = "Files";
$MESS["MAIN_DUMP_BASE_STAT"] = "statistics";
$MESS["MAIN_DUMP_BASE_SINDEX"] = "search index";
$MESS["MAIN_DUMP_BASE_IGNORE"] = "Exclude from archive:";
$MESS["MAIN_DUMP_BASE_TRUE"] = "Back up database:";
$MESS["MAIN_DUMP_BASE_TITLE"] = "Database";
$MESS["MAIN_DUMP_BASE_SIZE"] = "Mb";
$MESS["MAIN_DUMP_PAGE_TITLE"] = "Backup";
$MESS["MAIN_DUMP_TAB"] = "Backup";
$MESS["MAIN_DUMP_SITE_PROC"] = "Compressing...";
$MESS["MAIN_DUMP_ARC_SIZE"] = "Archive size:";
$MESS["MAIN_DUMP_TABLE_FINISH"] = "Tables processed:";
$MESS["MAIN_DUMP_ACTION_DOWNLOAD"] = "Download";
$MESS["MAIN_DUMP_DELETE"] = "Delete";
$MESS["MAIN_DUMP_ALERT_DELETE"] = "Are you sure you want to delete file?";
$MESS["MAIN_DUMP_FILE_PAGES"] = "Backup copies";
$MESS["MAIN_RIGHT_CONFIRM_EXECUTE"] = "Attention! Unpacking the backup copy on the working site can corrupt the site! Continue?";
$MESS["MAIN_DUMP_RESTORE"] = "Unpack";
$MESS["MAIN_DUMP_ENCODE"] = "Attention! You are using encoded product version.";
$MESS["MAIN_DUMP_MYSQL_ONLY"] = "The backup feature supports MySQL databases only.<br>Please use external tools to create the database copy.";
$MESS["MAIN_DUMP_HEADER_MSG"] = "To move the site back-up archive to another server, copy the restore script <a href='/bitrix/admin/restore_export.php'>restore.php</a> and the archive file to the root directory of the new server. Then, type in your browser: <b>&lt;site name&gt;/restore.php</b>.";
$MESS["MAIN_DUMP_SKIP_SYMLINKS"] = "Skip Symbolic Links to Directories:";
$MESS["MAIN_DUMP_MASK"] = "Exclude Files and Folders (mask):";
$MESS["MAIN_DUMP_MORE"] = "More...";
$MESS["MAIN_DUMP_FOOTER_MASK"] = "The following rules apply to exclusion masks:
 <p>
 <li>the mask can contain asterisks &quot;*&quot; that match any or none characters in the file or folder name;</li>
 <li>if a path starts with a slash or a backslash (&quot;/&quot; or &quot;\\&quot;), the path is relative to the site root;</li>
 <li>otherwise, the mask applies to each file and folder;</li>
 <p>Examples of templates:</p>
 <li>/content/photo - excludes the folder/content/photo;</li>
 <li>*.zip - excludes ZIP files (the ones with the &quot;zip&quot; extension);</li>
 <li>.access.php - excludes all files &quot;.access.php&quot;;</li>
 <li>/files/download/*.zip - excludes ZIP files in /files/download;</li>
 <li>/files/d*/*.ht* - excludes files with extensions starting with &quot;ht&quot; in directories starting with &quot;/files/d&quot;.</li>";
$MESS["MAIN_DUMP_ERROR"] = "Error";
$MESS["DUMP_NO_PERMS"] = "Insufficient server permission to create backup files.";
$MESS["DUMP_NO_PERMS_READ"] = "Error opening the backup file for reading.";
$MESS["DUMP_DB_CREATE"] = "Creating database dump";
$MESS["DUMP_CUR_PATH"] = "Current Path:";
$MESS["INTEGRITY_CHECK"] = "Integrity Check";
$MESS["CURRENT_POS"] = "Current Position:";
$MESS["TAB_STANDARD"] = "Standard";
$MESS["TAB_STANDARD_DESC"] = "Standard backup creation parameters";
$MESS["TAB_ADVANCED"] = "Advanced";
$MESS["TAB_ADVANCED_DESC"] = "Advanced backup creation parameters";
$MESS["MODE_DESC"] = "You are about to create the full backup copy of the <b>current site</b> (if multiple sites configuration), <b>system kernel</b> and <b>database</b> (MySQL only) which can be used for system restore and migration to other server. Select the desired mode and, if required, configure additional parameters using the &quot;<b>Advanced</b>&quot; tab.";
$MESS["MODE_VPS"] = "Dedicated Server Or VPS (time optimum)";
$MESS["MODE_SHARED"] = "Standard Hosting Parameters (good for most sites)";
$MESS["MODE_SLOW"] = "Safe Mode (use if other modes fail; no compression, long interstep interval)";
$MESS["PUBLIC_PART"] = "Public Section:";
$MESS["SERVER_LIMIT"] = "Server Restrictions";
$MESS["STEP_LIMIT"] = "Step Duration:";
$MESS["DISABLE_GZIP"] = "Disable Compression (reduces CPU load):";
$MESS["INTEGRITY_CHECK_OPTION"] = "Check Backup Integrity When Completed:";
$MESS["MAIN_DUMP_DB_PROC"] = "Compressing database dump";
$MESS["CDIR_FOLDER_ERROR"] = "Error processing the folder:";
$MESS["CDIR_FOLDER_OPEN_ERROR"] = "Error opening the folder: ";
$MESS["CDIR_FILE_ERROR"] = "Error processing the file: ";
$MESS["BACKUP_NO_PERMS"] = "Insufficient write permission for /bitrix/backup";
$MESS["TIME_SPENT"] = "Time spent:";
$MESS["TIME_H"] = "h";
$MESS["TIME_M"] = "m";
$MESS["TIME_S"] = "s";
$MESS["MAIN_DUMP_FOLDER_ERR"] = "The folder #FOLDER# has no write permissions.";
$MESS["MAIN_DUMP_NO_CLOUDS_MODULE"] = "The Cloud Storage module is not installed.";
$MESS["MAIN_DUMP_INT_CLOUD_ERR"] = "Error initializing the cloud storage. Please try again later.";
$MESS["MAIN_DUMP_ERR_FILE_SEND"] = "Cannot upload file to cloud storage:";
$MESS["MAIN_DUMP_ERR_OPEN_FILE"] = "Cannot open file for reading:";
$MESS["MAIN_DUMP_SUCCESS_SENT"] = "Archive has been uploaded to cloud storage successfully.";
$MESS["MAIN_DUMP_CLOUDS_DOWNLOAD"] = "Download files from cloud storage";
$MESS["MAIN_DUMP_FILES_DOWNLOADED"] = "Files uploaded";
$MESS["MAIN_DUMP_FILES_SIZE"] = "Total uploaded";
$MESS["MAIN_DUMP_DOWN_ERR_CNT"] = "Files skipped";
$MESS["MAIN_DUMP_FILE_SENDING"] = "Sending archive to cloud storage";
$MESS["MAIN_DUMP_USE_THIS_LINK"] = "Use this link when moving the archive to another server using";
$MESS["MAIN_DUMP_ERR_COPY_FILE"] = "Cannot copy file: ";
$MESS["MAIN_DUMP_ERR_INIT_CLOUD"] = "Cannot connect to cloud storage";
$MESS["MAIN_DUMP_ERR_FILE_RENAME"] = "File rename error: ";
$MESS["MAIN_DUMP_ERR_NAME"] = "The archive name can include only Latin characters, digits, hyphens and periods.";
$MESS["MAIN_DUMP_FILE_SIZE1"] = "Archive size";
$MESS["MAIN_DUMP_LOCATION"] = "Location";
$MESS["MAIN_DUMP_PARTS"] = "parts: ";
$MESS["MAIN_DUMP_LOCAL"] = "local storage";
$MESS["MAIN_DUMP_GET_LINK"] = "Get link";
$MESS["MAIN_DUMP_SEND_CLOUD"] = "Upload to cloud storage ";
$MESS["MAIN_DUMP_SEND_FILE_CLOUD"] = "Upload archive to cloud storage ";
$MESS["MAIN_DUMP_RENAME"] = "Rename";
$MESS["MAIN_DUMP_ARC_NAME_W_O_EXT"] = "Archive name without extension";
$MESS["MAIN_DUMP_CLOUD_STORAGES"] = "Cloud Storages";
$MESS["MAIN_DUMP_ARC_LOCATION"] = "Archive location: ";
$MESS["MAIN_DUMP_LOCAL_DISK"] = "local disk";
$MESS["MAIN_DUMP_ARC_FROM_CLOUD"] = "Download files from cloud storage to archive:";
$MESS["MAIN_DUMP_EVENT_LOG"] = "event log";
?>