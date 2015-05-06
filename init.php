<?php
// This Feil sets up the enchilada. Manual entry of Das Values in das Slut des Feiles.

echo '<HTML><HEAD>';
echo '<meta charset="utf-8" />';
echo '</HEAD>';
echo '<BODY>';

include 'cred.php'; // include the login credentials for the database.

// Create the databases
$conn = mysqli_connect($servername, $username, $password);
$sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if (mysqli_query($conn, $sql)) {
    echo "Databas ist gemacht geworden!";
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

mysqli_close($conn);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "DROP TABLE IF EXISTS geschichte, komponent, messages, Preis, profit";
if ($conn->query($sql) === TRUE) {
    echo "Alle Tables in Database dropped, Jah!";
} else {
    echo "Error dropping tables: " . $conn->error;
}


$sql = "CREATE TABLE profit (Profit float, Increase int(1))";

// sql to create table
if ($conn->query($sql) === TRUE) {
    echo "Table profit created successfully. Gewesen, Jah!";
} else {
    echo "Error creating table profit: " . $conn->error;
}


// sql to create table

$sql = "CREATE TABLE geschichte (
Zeit TIMESTAMP, 
Preis float, 
Brand text,
ID  int(11)
)";

// sql to create table
if ($conn->query($sql) === TRUE) {
    echo "Table geschichte created successfully. Gewesen, Jah!";
} else {
    echo "Error creating table: " . $conn->error;
}

$sql = "CREATE TABLE komponent (
Brand text, 
Hersteller text, 
Total int(11),
Verkauf int(11),
Preis float,
ID int(11),
Gut text,
Minimipreis float,
Altpreis float, 
Originalpreis float,
Prettyprint text,
Einkaufpreis float
)";

if ($conn->query($sql) === TRUE) {
    echo "Table komponent created successfully. Gut!";
} else {
    echo "Error creating table: " . $conn->error;
}
// sql to create table
if ($conn->query($sql) === TRUE) {
    echo "Table geschichte created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}


// Messages in general
$sql = "CREATE TABLE messages (
Message text,
id text)";

if ($conn->query($sql) === TRUE) {
    echo "Table messages created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

// Messages in general
$sql = "CREATE TABLE Preis (
Preis float)";

if ($conn->query($sql) === TRUE) {
    echo "Table Preis created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();


//
//$mode = $_GET["mode"];


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = 'TRUNCATE TABLE  `komponent`';
$result = $conn->query($sql);

$sql = 'TRUNCATE TABLE  `messages`';
$result = $conn->query($sql);

$sql = 'TRUNCATE TABLE  `geschichte`';
$result = $conn->query($sql);

$sql = 'TRUNCATE TABLE  `Preis`';
$result = $conn->query($sql);

// MANUAL entry for time being --------------------------------------------------------------------------------------------------


// TT
$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("TT", "Pripps", "200", "0", "20", "0", "Ja, sehr gut.", "10", "20", "20", "TT", "14.5")';
$result = $conn->query($sql);

// Staropramen
$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Staro", "Pripps", "200", "0", "20", "1", "Ja, sehr gut.", "10", "20", "20", "Staropramen", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Lattol", "Pripps", "200", "0", "20", "2", "Ja, sehr gut.", "10", "20", "20", "Das Leichtbier", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Hof", "Pripps", "200", "0", "20", "3", "Ja, sehr gut.", "10", "20", "20", "Hof", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Punsch", "Pripps", "200", "0", "20", "4", "Ja, sehr gut.", "10", "20", "20", "Punsch", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Lask", "Pripps", "200", "0", "20", "5", "Ja, sehr gut.", "10", "20", "20", "Lemonade", "10")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Mariestad", "Pripps", "200", "0", "20", "6", "Ja, sehr gut.", "10", "20", "20", "Mariestad", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Sprit", "Pripps", "200", "0", "20", "7", "Ja, sehr gut.", "10", "20", "20", "Sprit", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Stella", "Pripps", "200", "0", "20", "8", "Ja, sehr gut.", "10", "20", "20", "Stella Artois", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`komponent` (`Brand`, `Hersteller`, `Total`, `Verkauf`, `Preis`, `ID`, `Gut`, `Minimipreis`, `Altpreis`, `Originalpreis`, `Prettyprint`, `Einkaufpreis`) VALUES ("Heineken", "Pripps", "200", "0", "20", "9", "Ja, sehr gut.", "10", "20", "20", "Heineken", "14.5")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`messages` (`id`, `Message` ) VALUES ("Ticker", "Soon to be Preises")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`Preis` (`Preis` ) VALUES ("0")';
$result = $conn->query($sql);

$sql = 'INSERT INTO `dasBar`.`profit` (`Profit`, `Increase`) VALUES ("0", "1")';
$result = $conn->query($sql);



$conn->close();
echo '</BODY>';
echo '</HTML>';
?>

