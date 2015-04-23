<?php
echo '<html>';
echo '<meta charset="utf-8">';
echo '<meta http-equiv="refresh" content="30; URL=connect.php?item=new">';
echo '<head>';
echo '<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Rancho">';
echo '<style> body {  text-align: center; font-size=500%;      font-family: "Rancho", serif;       font-size: 26px;  color: white; bgcolor: black;    }    </style>';
echo '</head>' ;

include 'cred.php'; // include the login credentials for the database.

echo '<BODY bgcolor="black">';
echo '<IMG SRC = "pics/trial-error.gif" width=800px>';
echo '<DIV id="totalpreis"></DIV>';

?>
<script type="text/javascript" src="jquery-2.1.3.js"></script>

<?php

// The coordinates can be calculated through php...

echo '<map name="beermap">';
echo '<area alt="item0"   coords="100,325,80" href="connect.php?item=item0" shape="circle"></area>';
echo '<area alt="item1"   coords="300,325,80" href="connect.php?item=item1" shape="circle"></area>';
echo '<area alt="item2"   coords="510,325,80" href="connect.php?item=item2" shape="circle"></area>';
echo '<area alt="item3"   coords="710,325,80" href="connect.php?item=item3" shape="circle"></area>';
echo '<area alt="item4"   coords="910,325,80" href="connect.php?item=item4" shape="circle"></area>';
echo '<area alt="rezet"  coords="1120,325,80" href="init.php" shape="circle"></area>';
// rezet
echo '<area alt="item5"   coords="100,580,80" href="connect.php?item=item5" shape="circle"></area>';
echo '<area alt="item6"   coords="300,580,80" href="connect.php?item=item6" shape="circle"></area>';
echo '<area alt="item7"   coords="510,580,80" href="connect.php?item=item7" shape="circle"></area>';
echo '<area alt="item8"   coords="710,580,80" href="connect.php?item=item8" shape="circle"></area>';
echo '<area alt="item9"   coords="910,580,80" href="connect.php?item=item9" shape="circle"></area>';
echo '<area alt="updat"   coords="1120,580,80" href="connect.php?item=new" shape="circle"></area>';
// update 
echo '</map>';

// Hash table for the items

$komponenten = array();
$komponenten['item0']='TT';
$komponenten['item1']='Staro';
$komponenten['item2']='Lattol';
$komponenten['item3']='Hof';
$komponenten['item4']='Punsch';
$komponenten['item5']='Lask';
$komponenten['item6']='Mariestad';
$komponenten['item7']='Sprit';
$komponenten['item8']='Stella';
$komponenten['item9']='Heineken';

// Hash table for the items

$prettyprint = array();
$prettyprint['item0']='TT';
$prettyprint['item1']='Staropramen';
$prettyprint['item2']='Leichtbier';
$prettyprint['item3']='Hof';
$prettyprint['item4']='Punsch';
$prettyprint['item5']='Sarsaparill';
$prettyprint['item6']='Mariestad';
$prettyprint['item7']='Sprit';
$prettyprint['item8']='Stella';
$prettyprint['item9']='Heineken';

$item = $_GET["item"];

// if statement

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

if ($item == "new") { // new customer 
$totalpreis = 0;
$sql = 'UPDATE `Preis` SET `Preis` = ' . $totalpreis; 
$result = $conn->query($sql);

echo "<script>
	$('#totalpreis').html('" . $totalpreis . " kr!');
	</script>";

} else {

// Identify the brand
$brand = $komponenten[$item];
$pretty = $prettyprint[$item];


// Increment the counter
$sql = 'UPDATE `komponent` SET `Verkauf` = `Verkauf` + 1 WHERE `Brand`="' . $brand . '"' ;
$result = $conn->query($sql);

// Algorithm for the preis-settings
// Troligen something with the minimipreis.

// Läser in det som lista om det finns flera poster
$sql = 'SELECT `Minimipreis` FROM  `komponent`  WHERE  `Brand` = "' . $brand . '"';
$result = $conn->query($sql);
// men antar att det alltid är det sista värdet hursomhelst
while($row = $result->fetch_assoc()) {
	   $minipreis = $row["Minimipreis"];
    }
// echo $minipreis;

// Läser in det som lista om det finns flera poster
$sql = 'SELECT * FROM  `komponent`  WHERE  `Brand` = "' . $brand . '"';
$result = $conn->query($sql);
// men antar att det alltid är det sista värdet hursomhelst
while($row = $result->fetch_assoc()) {
	   $minipreis  = $row["Minimipreis"];
	   $jetztpreis = $row["Preis"];
	   $altpreis   = $row["Altpreis"];
	   $origpreis  = $row["Originalpreis"];
	   $prettyname = $row["Prettyprint"];
    }

$neuPreis = $jetztpreis*1.03;

$sql = 'SELECT * FROM  `Preis`'; 
$result = $conn->query($sql);
// men antar att det alltid är det sista värdet hursomhelst
while($row = $result->fetch_assoc()) {
	   $totalpreis = $row["Preis"];
    }

$totalpreis = $totalpreis + $jetztpreis;
$sql = 'UPDATE `Preis` SET `Preis` = ' . $totalpreis; 
$result = $conn->query($sql);

echo "<script>
	$('#totalpreis').html('" . $totalpreis . " kr!');
	</script>";

$sql = 'UPDATE `komponent` SET `Altpreis` = ' . $jetztpreis . ' WHERE `Brand`="' . $brand . '"' ;
$result = $conn->query($sql);

$sql = 'UPDATE `komponent` SET `Preis` = ' . $neuPreis . ' WHERE `Brand`="' . $brand . '"' ;
$result = $conn->query($sql);

echo "<BR>";

}

// Läser in det som lista om det finns flera poster
$sql = 'SELECT * FROM  `messages`  WHERE  `id` = "Ticker"';
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
	   $dasString = $row["Message"];
    }
 
echo '<marquee attribute_name="beers" hspace = 10 scrollamount=30 bgcolor=black>' . '<FONT SIZE=+12 text-transform: uppercase; color=yellow face="monospace">' . $dasString . "</FONT>" . '</marquee>';
echo "<CENTER><IMG SRC = \"pics/bong.png\" width=1224px usemap=\"#beermap\"></CENTER>"; // 1000

$conn->close();

?>

