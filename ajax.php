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
        case "chart":
            construct_chart($_POST["brandNumber"], $return);
            break;
        case "preisliste":
            construct_pricelist($return);
            break;
        case "kassapreis":
            kassapreis($return);
            break;
        case "verkauf":
            verkauf($_POST["brand"], $return);
            break;
        }
    } else {
        echo "Ojoj!";
    }
}
exit(0);

function verkauf($brand, $return) {
   global $servername, $username, $password, $dbname;
   $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }     

   $sql = "SELECT * FROM `profit`";
   $result = $conn->query($sql);
   $row = $result->fetch_assoc();
   $profit = $row["Profit"];
   $increase = $row["Increase"] == 1;

   $sql = 'SELECT * FROM  `komponent`  WHERE  `Brand` = "' . $brand . '"';
   $result = $conn->query($sql);
   $row = $result->fetch_assoc();
   $id = $row["ID"];

   $jetztpreis = $row["Preis"];
   if ($increase) {
       $neuPreis = $row["Preis"] * 1.03;
   } else {
       $neuPreis = $row["Preis"];       
   }

   $thisProfit = $jetztpreis - $row["Einkaufpreis"];
   $return["profit"] = $profit + $thisProfit;


   $sql = 'UPDATE `komponent` SET `Altpreis` = ' . $jetztpreis . ' , `Preis` = ' . $neuPreis . ' , `Verkauf` = `Verkauf` + 1 WHERE `Brand`="' . $brand . '"' ;
   $conn->query($sql);

   // Store the  change in the log database
   $sql = 'INSERT INTO `dasBar`.`geschichte` (`Zeit`, `Preis`, `Brand`, `ID`) VALUES ( NOW(), "' . $neuPreis . '", "' . $brand . '" , "' . $id . '" );';
   $conn->query($sql);

   $sql = 'UPDATE `profit` SET `Profit` = `Profit` + ' . $thisProfit;
   $conn->query($sql);

   if ($profit >= 100 && $increase) {
       //Rea ut något! Krasch på börsen! Twitter!
       $sql = 'SELECT * FROM `komponent` ORDER BY `Verkauf` ASC';
       $result = $conn->query($sql);
       
       for ($i == 0; $i < 3; $i++) {
           $row = $result->fetch_assoc();
           $brand = $row["Brand"];
           $reapris = rand(1, $row["Minimipreis"] * 0.75);
           $sql = 'UPDATE `komponent` SET `Altpreis` = ' . $row["Preis"] . ', Preis = ' . $reapris . ' WHERE `Brand`="' . $brand . '"';
           $conn->query($sql);
       }


       $sql = 'UPDATE `profit` SET `Increase` = 0';
       $result = $conn->query($sql);       
   } else if ($profit <= 50 && !$increase) {
       //Slut på rea
       $sql = 'UPDATE `komponent` SET `Preis` = `Minimipreis` WHERE `Preis` < `Minimipreis`';
       $conn->query($sql);       
       
       $sql = 'UPDATE `profit` SET `Increase` = 1';
       $conn->query($sql);       
   }

   echo json_encode($return);   
   $conn->close();
}

function kassapreis($return) {
   global $servername, $username, $password, $dbname;
   $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }     

   $sql = "SELECT * FROM `komponent` ORDER BY `Prettyprint` ASC";
   $result = $conn->query($sql);
   
   $preise = array();
   $brands = array();

   while($row = $result->fetch_assoc()) {
       $brands[] = $row["Brand"];
       $preise[] = $row["Preis"];
   }

   $return["brands"] = $brands;
   $return["preise"] = $preise;

   echo json_encode($return);   
   $conn->close();
}


function construct_pricelist($return) {
   global $servername, $username, $password, $dbname;
   $conn = new mysqli($servername, $username, $password, $dbname);
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }     

   $sql = "SELECT * FROM `komponent` ORDER BY `Prettyprint` ASC";
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

   $sql = "SELECT * FROM `komponent` ORDER BY `Prettyprint` ASC";
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

function construct_marquee($return) {
    // Create and check connection
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    
// Run through the components to create a marquee.
    $sql = "SELECT * FROM `komponent` ORDER BY `Prettyprint` ASC";
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
