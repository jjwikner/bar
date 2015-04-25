<?php
include 'cred.php';

if (is_ajax()) {
    if (isset($_POST["cmd"]) && !empty($_POST["cmd"])) {
        $cmd = $_POST["cmd"];
        switch ($cmd) {
        case "ping":
            $return["data"] = "pong";
            echo json_encode($return);
            break;
        case "marquee":
            construct_marquee();
            break;
        }
    } else {
        echo "Ojoj!";
    }
}

function construct_marquee() {
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
    $updstring = ""; // needed since html-tags were not allowed without escaping in the mysql server.
    
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
}

function is_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
