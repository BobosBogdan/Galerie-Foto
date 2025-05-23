<?php
// Pornim sesiunea pentru a putea salva date despre utilizatorul logat
session_start();

// Activăm afișarea erorilor pentru depanare (doar pe localhost!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Încărcăm toate clasele/autoloaderul Composer și clasa User
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/user.php';

// Definim baza URL pentru a genera linkuri corecte în sabloane
$base = '/galerie-foto/public';

// Inițializăm o variabilă pentru afișarea mesajului de eroare
$eroare = null;

//  Dacă formularul a fost trimis (cerere POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Preluăm datele introduse de utilizator
    $username = $_POST['username'] ?? '';
    $parola = $_POST['parola'] ?? '';

    // Verificăm în baza de date dacă datele sunt corecte
    $user = User::login($username, $parola);

    // Dacă datele sunt valide, salvăm datele userului în sesiune și redirecționăm spre index
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['este_admin'] = $user['este_admin'];
        header("Location: index.php");
        exit;
    } else {
        // Dacă autentificarea a eșuat, afișăm un mesaj de eroare
        $eroare = "Date incorecte!";
    }
}

// Inițializăm Twig pentru a încărca sablonul de login
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Randăm sablonul 'login.twig' și trimitem datele către el:
// - eroare: mesajul dacă există o problemă la autentificare
// - base: calea pentru linkuri
// - session: pentru a ști dacă userul e logat
echo $twig->render('login.twig', [
    'eroare' => $eroare,
    'base' => $base,
    'session' => $_SESSION
]);
