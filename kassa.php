<html>
<head>
<title>Das Kassagerät!</title>
<meta charset="utf-8" />
     <link rel="stylesheet" href="bar.css">
     <script type="text/javascript" src="jquery-2.1.3.js"></script>
     <script type="text/javascript" src="marquee.js"></script>
     <script type="text/javascript">
     $(function() {
         $('marquee').marquee();
         requestPreis();
         setInterval(requestPreis, 8000);
     });

function ajaxDone(data) {
    switch(data["cmd"]) {
    case "kassapreis":
        brands = data["brands"];
        preise = data["preise"];
        for (var i = 0; i < brands.length; i++) {
            $("#button-" + i).html(brands[i] + "<br/>" + Math.round(preise[i]));
        }
        break;
    case "verkauf":
        
        break;
    default:
        break;
    }
}

function sendCmd(data) {
    data = $(this).serialize() + "&" + $.param(data);
    $.ajax({
      type: "POST",
            dataType: "json",
            url: "ajax.php",
            data: data,
            success: ajaxDone});
}

function requestPreis() {
    sendCmd({"cmd": "kassapreis"});
}

function logVerkauf(brand) {
    sendCmd({"cmd": "verkauf", "brand": brand});
}

function verkauf(brand) {
    var sum = parseFloat($("#summa").html());
    sum = Math.round(sum + parseFloat(preise[brands.indexOf(brand)]));
    $("#summa").html(sum);
    logVerkauf(brand);
    requestPreis();
}
 
function neuKund() {
    $("#summa").html("0");
}

</script>
</head>
<body bgcolor="black">
    <center><IMG SRC = "pics/trial-error.gif" width=400px></center>
    <br/>
    <div align="center">
    Das Summa: 
    <div id="summa">0</div>
    </div>
<?php
include 'cred.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}     

$sql = "SELECT * FROM `komponent` ORDER BY `Prettyprint` ASC ";
$result = $conn->query($sql);

$count = 0;
$preislist = "<script>preislist = {";
echo "<table align=\"center\"><tr>";
while ($row = $result->fetch_assoc()) {
    $preislist .= $row["Brand"] . ":" . $row["Preis"] . ", ";
    $newline = $count % 5 == 0;
    if ($newline) {
        echo"</tr><tr>";
    }
    echo "<td align=\"center\" height=150><button id=\"button-". $count ."\" style=\"width:200px\" onClick=\"verkauf('" . $row["Brand"] . "')\">#</button></td>\n";
    $count++;
}
echo "</tr></table>\n";
$preislist .= "};</script>\n";

echo $preislist;

$conn->close();

?>

<br/>
<table align="center">
<tr>
<td align="center">
<button style="width:200px" onClick="neuKund()">Neues Kund</button>
</td>
</tr>
</table>

</body>
</html>

