<?php
// Pornim sesiunea pentru a folosi datele utilizatorului (ex: user_id, username)
session_start();

// Încărcăm clasele necesare pentru funcționare: autoloader, clasa Gallery și conexiunea DB
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/gallery.php';
require_once __DIR__ . '/../src/DB.php';

// Definim baza URL relativă pentru a o trimite spre sabloanele Twig
$base = '/galerie-foto/public';

// 📨 Dacă se face o cerere POST, înseamnă că se trimite un formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificăm dacă s-a trimis un câmp 'actiune'
    if (isset($_POST['actiune'])) {
        $actiune = $_POST['actiune'];

        // 🔧 Dacă se trimite un formular de creare galerie
        if (
            $actiune === 'creeaza' &&
            isset($_POST['titlu'], $_POST['descriere'], $_SESSION['user_id'])
        ) {
            $titlu = trim($_POST['titlu']);
            $descriere = trim($_POST['descriere']);
            $user_id = $_SESSION['user_id'];

            // Inserăm noua galerie în baza de date
            $stmt = DB::connect()->prepare("INSERT INTO galerii (titlu, descriere, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$titlu, $descriere, $user_id]);

            // Redirect pentru a evita dublarea formularului la refresh
            header("Location: galerii.php");
            exit;
        }

        // 🗑️ Dacă se trimite un formular de ștergere galerie
        if (
            $actiune === 'sterge' &&
            isset($_POST['galerie_id'], $_SESSION['user_id'])
        ) {
            $galerie_id = $_POST['galerie_id'];
            $user_id = $_SESSION['user_id'];

            // Ștergem doar dacă galeria aparține utilizatorului logat
            $stmt = DB::connect()->prepare("DELETE FROM galerii WHERE id = ? AND user_id = ?");
            $stmt->execute([$galerie_id, $user_id]);

            // Redirect după ștergere
            header("Location: galerii.php");
            exit;
        }
    }
}

// 📥 Cerere GET: încărcăm toate galeriile din baza de date
$galerii = Gallery::toateGaleriile();

// Inițializăm Twig pentru a încărca sablonul de afișare
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Randăm șablonul galerii.twig cu datele galeriei, URL-ului și sesiunii
echo $twig->render('galerii.twig', [
    'galerii' => $galerii,
    'propriu' => false,
    'base' => $base,
    'session' => $_SESSION
]);
