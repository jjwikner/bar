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
        case "chart":
            construct_chart($_POST["brandNumber"], $return);
            break;
        case "preisliste":
            construct_pricelist($return);
            break;
        }
    } else {
        echo "Ojoj!";
    }
}
exit(0);

function construct_pricelist($return) {
   global $servername, $username, $password, $dbname;
   $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }     

   $sql = "SELECT * FROM `komponent`";
   $result = $conn->query($sql);

   $table = "<table>";
   
   $count = 0;
   
   while($row = $result->fetch_assoc()) {
       $even = ($count % 2) == 0;
       if ($even) {
           $table = $table . "<tr>";
           $padding = "";
       } else {
           $padding = " style='padding:0 0px 0 70px;'";
       }
       $table = $table . "<td" . $padding . ">" . $row["Prettyprint"] . "</td>";
       $table = $table . "<td>" . (string)round($row["Preis"]) . "kr</td>";
       if (!$even) {
           $table = $table . "</tr>";
       }
       $count++;
   }

   $table = $table . "</table>";
   $return["preisliste"] = $table;

   echo json_encode($return);   
   $conn->close();
}

function construct_chart($brandNumber, $return) {
   global $servername, $username, $password, $dbname;
   $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }     

   $sql = "SELECT * FROM `komponent`";
   $result = $conn->query($sql);
   if ($result->num_rows > 0) {
       $num = $brandNumber % $result->num_rows;

       $row = $result->fetch_assoc();
       for ($i=0; $i < $num; $i++) {
           $row = $result->fetch_assoc();
       }
       $brand = $row["Brand"];
       $return["brand"] = $row["Prettyprint"];
       $return["preis"] = $row["Preis"] . " kr";

       $sql = 'SELECT * FROM `geschichte` WHERE `Brand`= "' . $brand . '" AND `Zeit` >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY `Zeit` ASC';
       $result = $conn->query($sql);
       $labels = array();
       $data = array();
       if ($result->num_rows > 0) {
           $index = $result->num_rows;
           while ($row = $result->fetch_assoc()) {
               $data[] = $row["Preis"];
               $labels[] = $index;
               $index--;
           }
       }
       $return["labels"] = $labels;
       $return["data"] = $data;       
   }
   
   echo json_encode($return);
   
   $conn->close();
}

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
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // find the price and difference from original price ("for the day")
            $preis = $row["Preis"];
            $orig  = $row["Originalpreis"];
            $delta = $preis - $orig; 
            
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
