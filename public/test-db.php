<?php
try {
    // Creează o conexiune PDO la baza de date MySQL (localhost, nume baza de date, charset)
    $pdo = new PDO(
        'mysql:host=localhost;dbname=nume_baza_date;charset=utf8mb4', // adresa, baza, charset
        'root', // utilizator MySQL
        ''       // parolă (în XAMPP de obicei e goală)
    );

    // Dacă conexiunea reușește, afișează mesaj de confirmare
    echo "Conexiune reușită!";
    
    // Execută o interogare care returnează toate tabelele din baza de date
    $stmt = $pdo->query("SHOW TABLES");

    // Preia toate rezultatele într-un array
    $tables = $stmt->fetchAll();

    // Afișează rezultatul sub formă de text formatat (pre)
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
    
} catch (PDOException $e) {
    // În caz de eroare la conectare, afișează mesajul de eroare
    echo "Eroare conexiune: " . $e->getMessage();
}
?>
