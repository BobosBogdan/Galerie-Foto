<?php
// src/Auth.php

class Auth {

    // Funcție pentru autentificare utilizator
    public static function login($email, $parola) {
        $pdo = DB::connect(); // Conectare la baza de date
        $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE email = ?"); // Pregătește interogarea
        $stmt->execute([$email]); // Execută interogarea cu adresa de email
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Preia utilizatorul din baza de date

        // Verifică dacă utilizatorul există și parola este corectă
        if ($user && password_verify($parola, $user['parola'])) {
            $_SESSION['user'] = $user; // Salvează utilizatorul în sesiune
            return true; // Autentificare reușită
        }
        return false; // Autentificare eșuată
    }

    // Funcție pentru delogare
    public static function logout() {
        unset($_SESSION['user']); // Șterge utilizatorul din sesiune
    }

    // Verifică dacă utilizatorul este logat
    public static function check() {
        return isset($_SESSION['user']); // Returnează true dacă există un user în sesiune
    }

    // Returnează utilizatorul curent logat
    public static function user() {
        return $_SESSION['user'] ?? null; // Returnează utilizatorul sau null
    }

    // Verifică dacă utilizatorul are rolul de admin
    public static function isAdmin() {
        return isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
    }
}
