<?php
header("Content-Type: text/html; charset=UTF-8");
set_time_limit( 0 );
echo str_repeat( ' ', 1024 );

echo "<h1>情報を更新しています...</h1>";
ob_start();
ob_end_flush();
ob_start('mb_output_handler');
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// get json from remote
set_include_path(get_include_path().':/home/k-consulting-o/www/wp2/regulation/script/phpseclib');

//echo get_include_path();
include('Net/SSH2.php');
include('Crypt/RSA.php');

echo " Connecting SSH...<br/>";
ob_flush();
flush();
$key = new Crypt_RSA();
$file = file_get_contents('/home/k-consulting-o/.ssh/scraping_rsa');
if(is_null($file)) {
    echo 'Failed';
}
$key->loadKey($file);
$ssh = new Net_SSH2('***.***.***.***');

try {
    if (!$ssh->login('scraping', $key)) {
        throw new Exception('SSH Autentication rejected by server<br/>');
    }
    echo "SSH Login Success<br/>";
    ob_flush();
    flush();

    echo "Exec python3 /home/scraping/test.py now <br/>";
    echo "Please wait...<br/>";
    ob_flush();
    flush();

    echo $ssh->exec('/usr/bin/python3 /home/scraping/test.py');

    echo "Finish Exec python3 /home/scraping/test.py<br/>";
    ob_flush();
    flush();

    $string = $ssh->exec('cat /home/scraping/data');

    echo " Finish Exec cat /opt/scraping/data<br/>";
    ob_flush();
    flush();

    if (empty($string) || !strcmp($string,"") ) {
	throw new Exception("Get JSON Failed<br/>");
    }

    echo " Get JSON Success<br/>";
    ob_flush();
    flush();

    $getdata = json_decode($string);
    // var_dump($getdata);
    if (empty($getdata)) {
	throw new Exception('Decode JSON Failed');
    }

    echo "Decode JSON Success<br/>";
    ob_flush();
    flush();

} catch (Exception $e){
    echo $e->getMessage();
    ob_flush();
    flush();
}

echo "Finish All Exec Remote-Python<br/>";
ob_flush();
flush();

// connect database
$dsn = 'mysql:dbname=**********;host=************';
$u='***********'; $pw='**********';
$pdo = new PDO($dsn, $u, $pw);

// truncate table
$truncate = "TRUNCATE TABLE `newdata`";
$stmt = $pdo->prepare($truncate);
try {
    $stmt->execute();
} catch (Exception $e){
    echo $e->getMessage();
    ob_flush();
    flush();
}

echo "Finish Truncate<br/>";
ob_flush();
flush();


$array = $getdata;

// insert data into mysql database
echo " Inserting data...<br/>";
ob_flush();
flush();
$sql = "INSERT INTO `newdata` (title, id, updated, site, link) VALUES (?,?,?,?,?)";
foreach ($array as &$a) {
    try {
	if(!(isset($a->title) && isset($a->id) && isset($a->site))) {
	    continue;
	}
	$b = array($a->title,$a->id,$a->updated,$a->site,$a->link);
    	// var_dump($b);
	$pdo->beginTransaction();
	$pdo->prepare($sql)->execute($b);
	$pdo->commit();
    } catch (Exception $e){
	$pdo->rollback();
	echo $e->getMessage();
	ob_flush();
	flush();
    }
}

echo "Finish Inserting</br>";
ob_flush();
flush();

$delete_old = "DELETE FROM `olddata` WHERE (`updated` < DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
$delete_new = "DELETE FROM `newdata` WHERE (`updated` < DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
$stmt1 = $pdo->prepare($delete_old);
$stmt2 = $pdo->prepare($delete_new);
try {
    $stmt1->execute();
    $stmt2->execute();
} catch (Exception $e){
    echo $e->getMessage();
    ob_flush();
    flush();
}

echo "Finish Delete Olddata<br/>";
ob_flush();
flush();

$sql = "INSERT INTO olddata (title, id, site, updated, link) SELECT newdata.title, newdata.id, newdata.site, newdata.updated, newdata.link FROM newdata LEFT OUTER JOIN olddata ON newdata.id = olddata.id WHERE olddata.id IS NULL";
echo " Inserting newdata to olddata...<br/>";
ob_flush();
flush();
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} catch (Exception $e){
    echo $e->getMessage();
    ob_flush();
    flush();
}

echo "Finish insert to olddata<br/>";
echo "<strong>Finish All Processes!</strong></br><a href='../index.php'>戻る</a>";
ob_flush();
flush();

?>
