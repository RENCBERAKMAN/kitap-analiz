<?php
echo "API klasörü çalışıyor<br>";

$url = 'https://www.googleapis.com/books/v1/volumes?q=harry+potter&maxResults=1&printType=books';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Kodu: " . $httpCode . "<br>";
echo "Veri geldi mi: " . (empty($response) ? 'HAYIR' : 'EVET') . "<br>";

$data = json_decode($response, true);
echo "Kitap sayısı: " . count($data['items'] ?? []);