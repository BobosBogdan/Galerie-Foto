<?php
require_once 'DB.php';

class Gallery {

    //  Returnează toate galeriile împreună cu numele utilizatorului creator
    public static function toateGaleriile() {
        $pdo = DB::connect();
        $stmt = $pdo->query("
            SELECT g.*, u.username 
            FROM galerii g 
            JOIN utilizatori u ON g.user_id = u.id
            ORDER BY g.id DESC
        ");
        return $stmt->fetchAll(); // Returnează rezultatele sub formă de array asociativ
    }

    //  Returnează galeriile doar pentru un anumit utilizator
    public static function galeriileUserului($user_id) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("SELECT * FROM galerii WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    //  Adaugă o nouă galerie în baza de date
    public static function adaugaGalerie($titlu, $descriere, $user_id) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("INSERT INTO galerii (titlu, descriere, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$titlu, $descriere, $user_id]);
    }

    //  Șterge o galerie doar dacă aparține utilizatorului conectat
    public static function stergeGalerie($id, $user_id) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("DELETE FROM galerii WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }

    //  Returnează informațiile despre o galerie după ID
    public static function gasesteGalerie($id) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("SELECT * FROM galerii WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(); // Returnează o singură galerie
    }

    //  Adaugă o imagine într-o galerie specificată
    public static function adaugaImagine($galerie_id, $cale) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("INSERT INTO imagini (galerie_id, cale) VALUES (?, ?)");
        $stmt->execute([$galerie_id, $cale]);
    }

    //  Returnează toate imaginile asociate unei galerii
    public static function imaginiPentruGalerie($galerie_id) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("SELECT * FROM imagini WHERE galerie_id = ?");
        $stmt->execute([$galerie_id]);
        return $stmt->fetchAll();
    }

    //  Șterge o imagine după ID (doar dacă aparține galeriei)
    public static function stergeImagine($imagine_id) {
        $pdo = DB::connect();
        $stmt = $pdo->prepare("DELETE FROM imagini WHERE id = ?");
        $stmt->execute([$imagine_id]);
    }
}
