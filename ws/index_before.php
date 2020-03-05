<?php
$nature = "%";
$choice = filter_input(INPUT_GET, "choice", FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_GET, "date", FILTER_SANITIZE_STRING);
$line = filter_input(INPUT_GET, "line", FILTER_SANITIZE_STRING);
$dateEnd = filter_input(INPUT_GET, "dateEnd", FILTER_SANITIZE_STRING);
$nature .= filter_input(INPUT_GET, "nature", FILTER_SANITIZE_STRING);

$date .= "%";

if ($id == "" || $id == NULL) {
  $id = "%";
}
if ($date == "" || $date == NULL) {
  $date = "%";
}
if ($line == "" || $line == NULL) {
  $line = "%";
}

if ($dateEnd == "" || $dateEnd == NULL) {
  $dateEnd = date("Y-m-d");
}
else {
  $date = substr($date, 0, strlen($date)-1);
}

if ($nature == "%" || $nature == NULL) {
  $nature = "%";
}
else {
  $nature .= "%";
}

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

function incidentReadAll($id, $nature, $date) {
  $sql = "SELECT `Id_Incident`, `Dttm_Incident`, `Geo_Place`, `Txt_Nature`, `Txt_Result` FROM `INCIDENT`
  WHERE `Id_Incident` LIKE :id AND
  `Txt_Nature` LIKE :nature AND
  `Dttm_Incident` LIKE :date";
  $query = connect()->prepare($sql);
  $query->execute([
    ':id' => $id,
    ':nature' => $nature,
    ':date' => $date
  ]);
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  return $result;
}
function incidentDateReadAll($nature, $date, $dateEnd) {
  $sql = "SELECT `Id_Incident`, `Dttm_Incident`, `Geo_Place`, `Txt_Nature`, `Txt_Result` FROM `INCIDENT`
  WHERE `Txt_Nature` LIKE :nature AND
  `Dttm_Incident` BETWEEN :date AND :dateEnd";
  $query = connect()->prepare($sql);
  $query->execute([
    ':date' => $date,
    ':dateEnd' => $dateEnd,
    ':nature' => $nature
  ]);
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  return $result;
}

function lineReadAll($id, $line) {
  $sql = "SELECT `Id_Line`, `Cd_Line`, `Id_Incident` FROM `LINE`
  WHERE `Id_Line` LIKE :id AND
  `Cd_Line` LIKE :line";
  $query = connect()->prepare($sql);
  $query->execute([
    ':id' => $id,
    ':line' => $line
  ]);
  $result = $query->fetchAll(PDO::FETCH_ASSOC);
  return $result;
}

$json = "";
if ($choice == "incident") {
  if (substr($date, strlen($date)-1, strlen($date)-1)  == "%") {
    $json = incidentReadAll($id, $nature, $date);
  }
  else {
    $json = incidentDateReadAll($nature, $date, $dateEnd);
  }
}
else if ($choice == "line") {
  $json = lineReadAll($id, $line);
}
else {
  $json = array (
    "error"  => "Veuillez reformuler votre demande"
  );
}

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');
echo json_encode($json);
?>
