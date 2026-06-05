
<?php
header("Content-Type: application/json");

$chipid = isset($_GET["chipid"]) ? strtoupper(preg_replace("/[^0-9A-Fa-f]/", "", $_GET["chipid"])) : "";
$db = __DIR__ . "/registry.json";

if (!$chipid || !file_exists($db)) {
  http_response_code(404);
  echo json_encode(["ok"=>false]);
  exit;
}

$reg = json_decode(file_get_contents($db), true);
if (!is_array($reg) || !isset($reg[$chipid])) {
  http_response_code(404);
  echo json_encode(["ok"=>false]);
  exit;
}

echo json_encode(["ok"=>true] + $reg[$chipid]);
