<?php
// Pornim sesiunea pentru a avea acces la informațiile utilizatorului logat (dacă există)
session_start();

// Încărcăm autoloaderul Composer pentru a folosi Twig și alte clase
require_once __DIR__ . '/../vendor/autoload.php';

// Setăm calea de bază pentru linkuri (utilizată în șablonul Twig)
$base = '/galerie-foto/public';

// Configurăm Twig pentru a încărca fișierele din folderul "templates"
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Randăm fișierul "index.twig" și trimitem variabilele către șablon:
// - "base" pentru a construi linkuri corecte
// - "session" pentru a verifica dacă userul e logat și cine este
echo $twig->render('index.twig', [
    'base' => $base,
    'session' => $_SESSION
]);
