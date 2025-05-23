<?php
// Pornim sesiunea pentru a avea acces la datele utilizatorului logat
session_start();

// Încărcăm autoloaderul și clasele necesare (Twig, DB, Gallery)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/gallery.php';
require_once __DIR__ . '/../src/DB.php';

// Setăm baza URL relativă (pentru linkuri în sabloane)
$base = '/galerie-foto/public';

// Variabilă pentru afișarea erorilor în interfață
$eroare = null;

// Preluăm ID-ul galeriei din URL (GET)
$id = $_GET['id'] ?? null;

// Obținem detalii despre galerie și imaginile asociate
$galerie = Gallery::gasesteGalerie($id);
$imagini = Gallery::imaginiPentruGalerie($id);
$comentarii = [];

// Verificăm dacă galeria există
if (!$galerie) {
    echo "Galeria nu a fost găsită.";
    exit;
}

// Verificăm dacă utilizatorul logat este proprietarul galeriei
$este_proprietar = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $galerie['user_id'];

// Tratăm cererile POST (formular)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✏️ Editare titlu și descriere (doar de către proprietar)
    if (
        isset($_POST['actiune']) && $_POST['actiune'] === 'editeaza' &&
        isset($_POST['titlu_nou'], $_POST['descriere_noua']) &&
        $este_proprietar
    ) {
        $titlu_nou = trim($_POST['titlu_nou']);
        $descriere_noua = trim($_POST['descriere_noua']);

        $stmt = DB::connect()->prepare("UPDATE galerii SET titlu = ?, descriere = ? WHERE id = ?");
        $stmt->execute([$titlu_nou, $descriere_noua, $id]);

        // Redirect pentru a evita repostarea la refresh
        header("Location: galerie.php?id=$id");
        exit;
    }

    // 📷 Adăugare imagine (doar de către proprietar)
    if (isset($_FILES['imagine'])) {
        if ($este_proprietar) {
            $nume = basename($_FILES['imagine']['name']);
            $tmp = $_FILES['imagine']['tmp_name'];

            $targetDir = __DIR__ . '/uploads/';
            $targetPath = $targetDir . $nume;
            $webPath = $base . '/uploads/' . $nume;

            // Cream folderul dacă nu există
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Încercăm să salvăm fișierul pe server
            if (move_uploaded_file($tmp, $targetPath)) {
                Gallery::adaugaImagine($id, $webPath);
            } else {
                $_SESSION['eroare'] = "Eroare la salvarea imaginii pe server. Verifică permisiunile folderului uploads.";
            }
        } else {
            $_SESSION['eroare'] = "Imposibil – nu ești autorul galeriei.";
        }

        header("Location: galerie.php?id=$id");
        exit;
    }

    // 🗑️ Ștergere imagine (doar de către proprietar)
    if (isset($_POST['sterge_imagine']) && $este_proprietar) {
        $img_id = $_POST['sterge_imagine'];
        Gallery::stergeImagine($img_id);
        header("Location: galerie.php?id=$id");
        exit;
    }

    // 💬 Adăugare comentariu (doar pentru utilizatori logați)
    if (isset($_POST['comentariu'], $_SESSION['user_id'])) {
        $comentariu = trim($_POST['comentariu']);
        $nume = $_SESSION['username'];

        $stmt = DB::connect()->prepare("INSERT INTO comentarii (galerie_id, nume, continut) VALUES (?, ?, ?)");
        $stmt->execute([$id, $nume, $comentariu]);

        header("Location: galerie.php?id=$id");
        exit;
    }

    // 🗑️ Ștergere comentariu propriu (verificare după nume)
    if (
        isset($_POST['actiune']) && $_POST['actiune'] === 'sterge_comentariu' &&
        isset($_POST['comentariu_id'], $_SESSION['username'])
    ) {
        $comentariu_id = $_POST['comentariu_id'];
        $nume = $_SESSION['username'];

        $stmt = DB::connect()->prepare("DELETE FROM comentarii WHERE id = ? AND nume = ?");
        $stmt->execute([$comentariu_id, $nume]);

        header("Location: galerie.php?id=$id");
        exit;
    }
}

// 📰 Obținem toate comentariile pentru această galerie
if ($id) {
    $stmt = DB::connect()->prepare("SELECT * FROM comentarii WHERE galerie_id = ?");
    $stmt->execute([$id]);
    $comentarii = $stmt->fetchAll();
}

// Inițializăm Twig pentru randare sablon
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Verificăm dacă există erori din sesiune și le ștergem
$eroare = $_SESSION['eroare'] ?? null;
unset($_SESSION['eroare']);

// Randăm șablonul cu datele galeriei, comentariilor și imaginilor
echo $twig->render('galerie.twig', [
    'galerie' => $galerie,
    'comentarii' => $comentarii,
    'imagini' => $imagini,
    'base' => $base,
    'session' => $_SESSION,
    'eroare' => $eroare,
    'este_proprietar' => $este_proprietar
]);
