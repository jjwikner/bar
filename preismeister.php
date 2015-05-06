#!/usr/bin/php

<?php
include 'cred.php';

declare(ticks = 1);

pcntl_signal(SIGTERM, "signal_handler");
pcntl_signal(SIGINT, "signal_handler");

function signal_handler($signal) {
    switch($signal) {
    case SIGINT:
        print "Caught SIGINT\n";
        global $conn;
        $conn->close();
        exit;
    case SIGTERM:
        print "Caught SIGTERM\n";
        global $conn;
        $conn->close();
        exit;        
    case SIGKILL:
        print "Caught SIGKILL\n";
        global $conn;
        $conn->close();
        exit;        
    }
}
        

// To increment the Preis on all bier sorts.
// They will increase the Preis varje time sidan ist refreshed.
// Run a new loop, just for the sake of it ...
global $servername, $username, $password, $dbname;
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}     

while (true) {
    $sql = "SELECT `Increase` FROM `profit`";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $increase = $row["Increase"] == 1;

    $sql = "SELECT * FROM `komponent`";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $preis = $row["Preis"];
            $mini  = $row["Minimipreis"];
            $altpreis = $preis;
            
            // Algorithm to set the Preis - be creative...
            if ($preis >= $mini) {
                $preis = $preis - ($preis-$mini)*0.05 + 0.2*rand(0, 2); 
            }
            
            if ($increase) {
                // We shouldn't loose money unless we want to
                if ($preis < $mini) {
                    $preis = $mini;
                }	  
            }
            
            echo "Changing price of " . $row["Prettyprint"] . " from " . $altpreis . " to " . $preis . "\n";
            
            // Set the new Preis and update the old Preis.
            $sql = 'UPDATE `komponent` SET `Preis` = ' . $preis . ' WHERE `Brand`="' . $row["Brand"] . '"' ;
            $conn->query($sql);
            $sql = 'UPDATE `komponent` SET `Altpreis` = ' . $altpreis . ' WHERE `Brand`="' . $row["Brand"] . '"' ;
            $conn->query($sql);
            
            // Store the  change in the log database
            $sql = 'INSERT INTO `dasBar`.`geschichte` (`Zeit`, `Preis`, `Brand`, `ID`) VALUES ( NOW(), "' . $preis . '", "' . $row["Brand"] . '" , "' . $row["ID"] . '" );';
            $conn->query($sql);
            
        }
    } else {
        echo "0 results";
    }   
    echo "===================================\n";
    sleep(60);

}
$conn->close();

?>


