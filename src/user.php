<?php
require_once 'db.php';

class User {
    // Metodă statică pentru autentificare utilizator
    public static function login($username, $parola) {
        // Conectare la baza de date
        $pdo = DB::connect();

        // Pregătire interogare pentru a căuta utilizatorul după username
        $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE username = ?");
        $stmt->execute([$username]);

        // Preluare rezultat sub formă de array asociativ
        $user = $stmt->fetch();

        // Verificare dacă parola introdusă corespunde cu cea hash-uită din baza de date
        if ($user && hash('sha256', $parola) === $user['parola']) {
            return $user;
        }

        // Dacă autentificarea eșuează, returnează false
        return false;
    }
}
