<?php
// This file resets all the bier Preises to das Originalpreis utan att das Historia to erase.

echo '<HTML><HEAD>';
echo '<meta charset="utf-8" />';
echo '<meta http-equiv="refresh" content="1; URL=http://127.0.0.1/connect.php">';
echo '</HEAD>';
echo '<BODY bgcolor="black">';

include 'cred.php'; // include the login credentials for the database.

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT * FROM `komponent`";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $preis = $row["Originalpreis"];
        $sql = 'UPDATE `komponent` SET `Preis` = ' . $preis . ' WHERE `Brand`="' . $row["Brand"] . '"' ;
	$conn->query($sql);		      
       $sql = 'INSERT INTO `dasBar`.`geschichte` (`Zeit`, `Preis`, `Brand`, `ID`) VALUES ( NOW(), "' . $preis . '", "' . $row["Brand"] . '" , "' . $row["ID"] . '" );';
       $conn->query($sql);
    }
} else {
    echo "0 results";
}


$conn->close();

echo "</BODY>";
echo "</HTML>";

?>
