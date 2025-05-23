<?php
// Pornim sesiunea pentru a putea accesa variabilele de sesiune (ex: user_id)
session_start();

// Încărcăm autoloader-ul Composer (pentru Twig și alte dependențe)
require_once __DIR__ . '/../vendor/autoload.php';

// Importăm fișierul cu clasa de conexiune la baza de date
require_once __DIR__ . '/../src/DB.php';

// Verificăm dacă cererea este de tip POST și dacă userul este autentificat și a trimis titlu și conținut
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['titlu'], $_POST['continut'], $_SESSION['user_id'])) {

    // Curățăm datele trimise din formular
    $titlu = trim($_POST['titlu']);
    $continut = trim($_POST['continut']);
    $user_id = $_SESSION['user_id'];

    // Verificăm că titlul și conținutul nu sunt goale
    if ($titlu !== '' && $continut !== '') {
        // Pregătim și executăm interogarea SQL pentru inserarea noului blog
        $stmt = DB::connect()->prepare("
            INSERT INTO bloguri (titlu, continut, user_id, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$titlu, $continut, $user_id]);
    }
}

// După inserare (sau dacă cererea nu e validă), redirecționăm înapoi la lista blogurilor
header("Location: bloguri.php");
exit;
