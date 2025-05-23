<?php
// Pornim sesiunea pentru a accesa variabilele utilizatorului (user_id, username etc.)
session_start();

// Încărcăm autoloaderul Composer (Twig și alte librării)
require_once __DIR__ . '/../vendor/autoload.php';

// Importăm clasa de conexiune la baza de date
require_once __DIR__ . '/../src/DB.php';

// Ne conectăm la baza de date
$pdo = DB::connect();

// 🗑️ Ștergere blog (doar dacă utilizatorul este autorul)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['actiune']) &&
    $_POST['actiune'] === 'sterge'
) {
    $blog_id = $_POST['blog_id'];
    $user_id = $_SESSION['user_id'] ?? null;

    // Dacă userul este autentificat, executăm ștergerea blogului pe care îl deține
    if ($user_id) {
        $stmt = $pdo->prepare("DELETE FROM bloguri WHERE id = ? AND user_id = ?");
        $stmt->execute([$blog_id, $user_id]);

        // După ștergere, redirecționăm la lista blogurilor
        header("Location: bloguri.php");
        exit;
    }
}

// 📋 Preluăm toate blogurile împreună cu username-ul autorului
$stmt = $pdo->query("
    SELECT b.*, u.username 
    FROM bloguri b 
    JOIN utilizatori u ON b.user_id = u.id 
    ORDER BY b.created_at DESC
");
$bloguri = $stmt->fetchAll();

// 🔧 Inițializăm sistemul Twig (sabloane)
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Afișăm pagina `bloguri.twig` cu toate datele
echo $twig->render('bloguri.twig', [
    'bloguri' => $bloguri,
    'base' => '/galerie-foto/public',
    'session' => $_SESSION
]);
