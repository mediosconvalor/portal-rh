<?php
// nominas.php: proxy para tu Web App de Apps Script
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

// URL de tu Apps Script desplegado como Web App
$scriptUrl = 'https://script.google.com/macros/s/AKfycby2AYwWASy43MhwiKMcBtZrjfasTZxNaIXLjF0E6xYZxze_wsUUSwR72OzUfDV1JuEVaQ/exec';

$method = $_SERVER['REQUEST_METHOD'];

// Inicializa cURL
$ch = curl_init($scriptUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if ($method === 'GET') {
  // reenviamos GET
  curl_setopt($ch, CURLOPT_HTTPGET, true);

} elseif ($method === 'POST') {
  // reenviamos POST con el JSON recibido
  $json = file_get_contents('php://input');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
  ]);
}

// Ejecuta y captura respuesta o error
$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

if ($error) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>$error]);
} else {
  // devolvemos la respuesta tal cual viene de Apps Script
  echo $response;
}