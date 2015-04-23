<?php
echo '<HTML>';
echo '<HEAD>';
echo '<meta charset="utf-8" />';
echo '<meta http-equiv="refresh" content="60">';
echo '<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Tangerine">';
echo '<style>  body {font-family: "Rancho", serif;       font-size: 24px;    color: lightgreen;  text-align: center;}    </style>';
echo '<script src="chart.js"></script>';
echo '</HEAD>';
echo '<BODY bgcolor="black">';

include 'cred.php'; // include the login credentials for the database.

// Create and check connection
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
} else {

    echo "0 results";

}

// Save the marquee string to the database such that anyone can access
$sql = 'UPDATE `dasBar`.`messages` SET `Message` = "' . $updString . '" WHERE `id`= "Ticker"; ' ;
$results = $conn->query($sql);

// To increment the Preis on all bier sorts.
// They will increase the Preis varje time sidan ist refreshed.
// Run a new loop, just for the sake of it ...

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

// Output the pics 
echo '<center> <IMG SRC = "pics/trial-error.gif" width=800px></center>';
echo '<marquee attribute_name="beers" hspace = 10 scrollamount=30 bgcolor=black>' . '<FONT SIZE=+12 text-transform: uppercase; color=yellow face="monospace">' . $dasString . "</FONT>" . '</marquee>';
echo '<center> <IMG SRC = "pics/ping.png" width=1000px></center>';

// --------------------------

// foreach over all brands to skapa alle grafen.
 
foreach ($brands as $brand) {

	// Setup a new graph for the bier in question
	// echo '<H1>' . $pretty[$brand] . '</H1>';
	echo '<canvas id="biers_' . $brand . '" width="300" height="300"></canvas>';

	$sql = 'SELECT * FROM `geschichte` WHERE `Brand`= "' . $brand . '"';
	$result = $conn->query($sql);

	$counter = 0;
	$counterLabels = '[';
	$dataVector = '[';

	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
	           $counterLabels = $counterLabels . $counter . ',';
		   $preis = $row["Preis"];
	 	   $dataVector = $dataVector . $preis . ','; 
       		   $counter = $counter + 1;
		   }
} else {
    echo "0 results";
}

$counterLabels = $counterLabels . $counter . ']'; // Ugly but simple
$dataVector = $dataVector . $preis . ']'; // Ugly but simple

$labels = $counterLabels;
$data   = $dataVector;

$struct =  'var bierData = {
	labels : ' . $labels . ', 
	title : { text : "Das Bier" }, 
	datasets : [
		{
			fillColor : "rgba(255,255,255,0)",
			strokeColor : "#00FF00",
			pointColor : "#00ff00",
			pointStrokeColor : "#00ff00",
			data : ' . $data . '	}
			] };';

echo '<script>';
echo $struct;
echo '</script>';

echo "<script>
    var biers = document.getElementById('biers_" . $brand . "').getContext('2d');
    new Chart(biers).Line(bierData); </script>";

}

echo "</BODY>";
echo "</HTML>";

$conn->close();

?>
