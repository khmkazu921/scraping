<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

echo "Current directry: ".getcwd() . "\n";

// connect database
echo "Connecting Local Database...\n";
$dsn = 'mysql:dbname=scraping;host=localhost';
$u='user'; $pw='geoglyph';
$pdo = new PDO($dsn, $u, $pw);

// get json from remote
echo "Get Json from Remote\n"; 
$location = getcwd();
set_include_path(get_include_path() . PATH_SEPARATOR . $location . '/script/phpseclib');
include('Net/SSH2.php');
include('Crypt/RSA.php');
echo " Connecting SSH...\n";
$key = new Crypt_RSA();
$key->loadKey(file_get_contents($location .'/script/was.pem'));
$ssh = new Net_SSH2('ec2-3-20-234-204.us-east-2.compute.amazonaws.com');
try {
    if (!$ssh->login('ubuntu', $key)) {
	throw new Exception('SSH Login Failed');
    }
    echo " SSH Login Success\n";
    echo " Executing   python test.py...\n";
    $ssh->exec('/usr/bin/python /opt/scraping/test.py');
    echo " Finish Exec python test.py\n";
    $string = $ssh->exec('cat /opt/scraping/data');
    echo " Finish Exec cat data\n";
    if (empty($string) || !strcmp($string,"") ) {
	throw new Exception("Get JSON Failed\n");
    }
    echo " Get JSON Success\n";
    $getdata = json_decode($string);
    if (empty($getdata)) {
	throw new Exception('Decode JSON Failed');
    }
    echo " Decode JSON Success\n";
} catch (Exception $e){
    echo $e->getMessage();
}
echo "Finish Getting Json from Remote\n";


// truncate table
$truncate = "TRUNCATE TABLE `newdata`";
$stmt = $pdo->prepare($truncate);
try {
    $stmt->execute();
} catch (Exception $e){
    echo $e->getMessage();
}
echo "Finish Truncate\n";

// insert into newdata
echo " Scraping...\n";
$command = '/usr/bin/python3 '.$location.'/script/scraping.py >&1';
$c = escapeshellcmd($command);
$output = "";
try {
    exec($c,$output);
} catch (Exception $e){
    echo $e->getMessage();
}
echo "Finish Getting Json from Local\n";

// marge array
$array = array_merge(json_decode($output[0]),$getdata);

// insert data into mysql database
echo " Inserting data...\n";
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
    }
}

echo "Finish Inserting";

?>

