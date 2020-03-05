<?php
function connect() {
  static $myDb = null;
  $dbName = "tpg";
  $dbUser = "tpg";
  $dbPass = "tpg-pop";
  if ($myDb === null) {
    try {
      $myDb = new PDO("mysql:host=localhost;dbname=$dbName;charset=utf8",
      $dbUser,
      $dbPass,
      array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_EMULATE_PREPARES => false)
    );
  }
  catch (Exception $e) {
    die("Impossible de se connecter à la base " . $e->getMessage());
  }
}
return $myDb;
}

$array = [];
$output = "";
function callAPI($url)
{
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($curl);
  curl_close($curl);
  return $result;
}
$array = json_decode(callAPI("http://prod.ivtr-od.tpg.ch/v1/GetDisruptions.json?key=10aM1oVKvWCqUJsAJyj9"), true);

foreach ($array["disruptions"] as $key => $value) {
  $output .= "<p>Date: " . $value["timestamp"] . "</p>";
  $output .= "<p>Place: " . $value["place"] . "</p>";
  $output .= "<p>Nature: " . $value["nature"] . "</p>";
  $output .= "<p>Consequence: " . $value["consequence"] . "</p>";
  $output .= "<p>Ligne: " . $value["lineCode"] . "</p><br><br><br>";
  if (selectIncident($value["timestamp"], $value["consequence"])) {
    echo "EXISTE -";
  }
  else {
    echo "EXISTE PAS -";
    insertIncident($value["timestamp"], $value["place"], $value["nature"], $value["consequence"], $value["lineCode"]);
  }
}

function selectIncident($date, $consequence) {
  $sql = "SELECT `Id_Incident` FROM `INCIDENT`
  WHERE `Dttm_Incident`=:date
  AND `Txt_Result`=:consequence";
  $query = connect()->prepare($sql);
  $query->execute([
    ':date' => $date,
    ':consequence' => $consequence
  ]);
  $result = $query->fetch(PDO::FETCH_ASSOC);
  return $result;
}

function insertIncident($date, $place, $nature, $consequence, $line) {
  $sql = "INSERT INTO `INCIDENT`(`Dttm_Incident`, `Geo_Place`, `Txt_Nature`, `Txt_Result`, `Cd_Line`)
  VALUES (:date, :place, :nature, :consequence, :line)";
  $query = connect()->prepare($sql);
  $query->execute([
    ':date' => $date,
    ':place' => $place,
    ':nature' => $nature,
    ':consequence' => $consequence,
	':line' => $line
  ]);
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <?=$output?>
  </body>
</html>
