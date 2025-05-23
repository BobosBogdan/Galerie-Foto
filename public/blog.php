<?php
// Pornim sesiunea pentru a accesa datele utilizatorului (user_id, username etc.)
session_start();

// Încărcăm autoloaderul pentru Twig și alte clase
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/DB.php';

// Preluăm ID-ul blogului din URL (GET)
$id = $_GET['id'] ?? null;
$pdo = DB::connect();

// Obținem informațiile blogului + autorul (username și user_id)
$stmt = $pdo->prepare("
    SELECT b.*, u.username 
    FROM bloguri b 
    JOIN utilizatori u ON b.user_id = u.id 
    WHERE b.id = ?
");
$stmt->execute([$id]);
$blog = $stmt->fetch();

// Dacă blogul nu există, afișăm un mesaj și oprim execuția
if (!$blog) {
    echo "Blogul nu a fost găsit.";
    exit;
}

// Verificăm dacă utilizatorul logat este autorul blogului
$este_autor = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $blog['user_id'];

// ✏️ Dacă autorul vrea să editeze blogul
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['actiune']) && $_POST['actiune'] === 'editeaza' &&
    isset($_POST['titlu_nou'], $_POST['continut_nou']) &&
    $este_autor
) {
    $titlu_nou = trim($_POST['titlu_nou']);
    $continut_nou = trim($_POST['continut_nou']);

    $stmt = $pdo->prepare("UPDATE bloguri SET titlu = ?, continut = ? WHERE id = ?");
    $stmt->execute([$titlu_nou, $continut_nou, $id]);

    // După salvare, redirecționăm către pagina blogului
    header("Location: blog.php?id=" . $id);
    exit;
}

// 💬 Dacă un utilizator logat trimite un comentariu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentariu'], $_SESSION['username'])) {
    $nume = $_SESSION['username'];
    $comentariu = trim($_POST['comentariu']);

    $stmt = $pdo->prepare("INSERT INTO comentarii_blog (blog_id, nume, continut) VALUES (?, ?, ?)");
    $stmt->execute([$id, $nume, $comentariu]);

    // Redirecționare după comentariu
    header("Location: blog.php?id=" . $id);
    exit;
}

// 🗑️ Ștergere comentariu (doar dacă este al userului logat)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['actiune']) && $_POST['actiune'] === 'sterge_comentariu' &&
    isset($_POST['comentariu_id'], $_SESSION['username'])
) {
    $stmt = $pdo->prepare("DELETE FROM comentarii_blog WHERE id = ? AND nume = ?");
    $stmt->execute([$_POST['comentariu_id'], $_SESSION['username']]);

    header("Location: blog.php?id=" . $id);
    exit;
}

// Preluăm toate comentariile pentru blog, ordonate descrescător după dată
$stmt = $pdo->prepare("SELECT * FROM comentarii_blog WHERE blog_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$comentarii = $stmt->fetchAll();

// Inițializăm sistemul de sabloane Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

// Afișăm pagina blogului cu datele colectate
echo $twig->render('blog.twig', [
    'blog' => $blog,
    'comentarii' => $comentarii,
    'base' => '/galerie-foto/public',
    'session' => $_SESSION,
    'este_autor' => $este_autor
]);
