<?php
error_reporting(0);
ini_set('display_errors', 0);

$HOST = $_ENV["DB_HOST"];
$USERNAME = $_ENV["DB_USERNAME"];
$PASSWORD = $_ENV["DB_PASSWORD"];
$DB = $_ENV["DB_NAME"];
$PORT = $_ENV["DB_PORT"];
$TABLE = $_ENV["DB_TABLE"];

$con = mysqli_connect($HOST, $USERNAME, $PASSWORD, $DB, $PORT);

function CreateGUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

$content = $_POST['content'];
$guid = CreateGUID();
$title = $_POST['title'];
$language = $_POST['language'];

if (empty($content)) {
    die("ERROR: No content!");
}

if (empty($title)) {
    $title = "Unnamed Code";
}

if (empty($language)) {
    $language = "ace/mode/text";
}

$content_safe = mysqli_real_escape_string($con, $content);
$title_safe = mysqli_real_escape_string($con, $title);
$language_safe = mysqli_real_escape_string($con, $language);

$tableSQL = "SHOW TABLES LIKE '$TABLE';";
$tables = $con->query($tableSQL);

if ($tables->num_rows < 1) {
    $createSQL = <<<HEREB
        CREATE TABLE `$TABLE` (
            `InternalID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `Content` mediumtext NOT NULL,
            `GUID` varchar(36) NOT NULL,
            `Title` text NOT NULL,
            `Language` text NOT NULL,
            PRIMARY KEY (`InternalID`),
            UNIQUE KEY `GUID` (`GUID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        HEREB;
    $ans = $con->query($createSQL);;
}

$insertSQL = "INSERT INTO `$TABLE` (`Content`, `GUID`, `Title`, `Language`) VALUES ('$content_safe','$guid', '$title_safe', '$language_safe');";

if(!mysqli_query($con, $insertSQL)){
    die("DB Connection failed!");
}

$pathV = "./view.php?guid=$guid";

die("Code has been uploaded successfully! The Code can be viewed <a style='color: white; font-weight: bold;' target='_blank' href='$pathV'>here</a>.");
?>