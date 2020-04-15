<?php
echo "<h1>情報を更新しています...</h1>";
echo str_pad(" ",4096)."<br/>";

ob_start();
ob_end_flush();
ob_start('mb_output_handler');

set_time_limit( 0 );
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// get json from remote
include('Net/SSH2.php');
include('Crypt/RSA.php');

echo " Connecting SSH...<br/>";
ob_flush();
flush();

$key = new Crypt_RSA();
$key->loadKey(file_get_contents('was.pem'));
$ssh = new Net_SSH2('ec2-18-188-94-61.us-east-2.compute.amazonaws.com');
try {
    if (!$ssh->login('ubuntu', $key)) {
	throw new Exception('SSH Login Failed');
    }

    echo "SSH Login Success<br/>";
    ob_flush();
    flush();

    echo "Exec /usr/bin/python /opt/scraping/test.py...<br/>";
    ob_flush();
    flush();

    $ssh->exec('/usr/bin/python /opt/scraping/test.py');

    echo "Finish Exec /usr/bin/python /opt/scraping/test.py<br/>";
    ob_flush();
    flush();

    $string = $ssh->exec('cat /opt/scraping/data');

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
$dsn = 'mysql:dbname=scraping;host=localhost';
$u='user'; $pw='geoglyph';
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

// insert into newdata
echo " Scraping...<br/>";
ob_flush();
flush();

$command = "python3 /opt/scraping.py 2>&1";
$c = escapeshellcmd($command);
try {	
    exec($c, $output, $return_var);
    if($return_var>0)
	throw new Exception('error');
} catch (Exception $e){
    echo $e->getMessage();
    ob_flush();
    flush();
}

echo "Finish Exec Local-Python<br/>";
ob_flush();
flush();

// marge array
$array = array_merge(json_decode($output[0]),$getdata);

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
