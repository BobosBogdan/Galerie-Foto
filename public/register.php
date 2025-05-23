<?php
// Pornim sesiunea pentru a putea salva informații despre utilizatorul nou înregistrat
session_start();

// Încărcăm autoloaderul și fișierul pentru conexiunea la baza de date
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DB.php';

// Inițializăm o variabilă pentru afișarea mesajului de eroare (dacă este cazul)
$eroare = null;

// Verificăm dacă formularul a fost trimis (metodă POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Preluăm și curățăm datele din formular
    $username = trim($_POST['username'] ?? '');
    $parola = trim($_POST['parola'] ?? '');
    $confirmare = trim($_POST['confirmare'] ?? '');

    // Validăm parola și confirmarea
    if ($parola !== $confirmare) {
        $eroare = "Parolele nu se potrivesc.";
    } elseif (strlen($username) < 3 || strlen($parola) < 3) {
        $eroare = "Usernameul și parola trebuie să aibă cel puțin 3 caractere.";
    } else {
        // Ne conectăm la baza de date
        $pdo = DB::connect();

        // Verificăm dacă utilizatorul există deja
        $stmt = $pdo->prepare("SELECT id FROM utilizatori WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $eroare = "Acest utilizator există deja.";
        } else {
            // Dacă este un username unic, criptăm parola și salvăm userul în BD
            $hash = hash('sha256', $parola);
            $stmt = $pdo->prepare("INSERT INTO utilizatori (username, parola, este_admin) VALUES (?, ?, 0)");
            $stmt->execute([$username, $hash]);

            // Salvăm informațiile utilizatorului în sesiune
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['este_admin'] = 0;

            // Redirecționăm către pagina principală
            header("Location: index.php");
            exit;
        }
    }
}

// Dacă e o cerere GET sau există eroare -> afișăm formularul
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Randăm șablonul register.twig și trimitem variabilele către el
echo $twig->render('register.twig', [
    'eroare' => $eroare,
    'base' => '/galerie-foto/public',
    'session' => $_SESSION
]);
