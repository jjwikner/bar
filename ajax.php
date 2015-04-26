<?php
include 'cred.php';

if (is_ajax()) {
    if (isset($_POST["cmd"]) && !empty($_POST["cmd"])) {
        $cmd = $_POST["cmd"];
        $return["cmd"] = $cmd;
        switch ($cmd) {
        case "ping":
            $return["data"] = "pong";
            echo json_encode($return);
            break;
        case "marquee":
            construct_marquee($return);
            break;
        case "preis":
            update_preis($return);
            break;
        }
    } else {
        echo "Ojoj!";
    }
}
exit(0);

function update_preis($return) {
// To increment the Preis on all bier sorts.
// They will increase the Preis varje time sidan ist refreshed.
// Run a new loop, just for the sake of it ...
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }     
    
    $sql = "SELECT * FROM `komponent`";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $preis = $row["Preis"];
            $orig  = $row["Originalpreis"];
            $mini  = $row["Minimipreis"];
            $altpreis = $preis;
            
            // Algorithm to set the Preis - be creative...
            $preis = $preis - ($preis-$mini)*0.05 + 0.2*rand(0, 2); 
            
            // We shouldn't loose money
            if ($preis < $mini) {
                $preis = $mini;
            }	  
            
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
    
    $return["data"] = "Preis updated";
    echo json_encode($return);

    $conn->close();
}

function construct_marquee($return) {
    // Create and check connection
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    
// Run through the components to create a marquee.
    $sql = "SELECT * FROM `komponent`";
    $result = $conn->query($sql);
    
    $dasString = "";
    $updString = ""; // needed since html-tags were not allowed without escaping in the mysql server.
    
// For Prettyprinting
    $brands = [];
    $pretty = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // find the price and difference from original price ("for the day")
            $preis = $row["Preis"];
            $orig  = $row["Originalpreis"];
            $delta = $preis - $orig; 
            $brands[] = $row["Brand"];
            $pretty[$row["Brand"]] = $row["Prettyprint"];
            
            if ($delta > 0) {
                $sign = '<FONT color="blue"> +';
            } else {$sign='<FONT color="red">';
            } 
            $dasString = $dasString . " " . $row["Prettyprint"] . ": " . $row["Preis"] . " (" . $sign . $delta . "</FONT>)";
            $updString = $updString . " " . $row["Prettyprint"] . ": " . $row["Preis"] . " (" . $delta . ")";
        }
        $return["data"] = $dasString;
        echo json_encode($return);
    } else {
        echo "0 results";   
    }
    $conn->close();
}

function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
