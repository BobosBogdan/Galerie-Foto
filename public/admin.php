<?php
// Pornim sesiunea pentru a putea accesa variabilele de sesiune (ex: user_id, este_admin)
session_start();

// Încărcăm autoloader-ul Composer (pentru Twig și alte pachete necesare)
require_once __DIR__ . '/../vendor/autoload.php';

// Verificăm dacă utilizatorul este autentificat și este administrator
// Dacă nu este logat sau nu este admin, îl redirecționăm către pagina de login
if (!isset($_SESSION['user_id']) || !$_SESSION['este_admin']) {
    header('Location: login.php');
    exit;
}

// Inițializăm loaderul Twig cu calea către directoarele de template-uri
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

// Creăm un nou mediu Twig pentru a reda template-urile
$twig = new \Twig\Environment($loader);

// Afișăm pagina admin.twig (interfața de administrare)
echo $twig->render('admin.twig');
