<?php
// src/DB.php

class DB {
    // Variabilă statică pentru instanța singleton
    private static $instance = null;

    // Obiect PDO pentru conexiunea la baza de date
    private $pdo;

    // Constructorul privat – se apelează doar intern în clasă (pentru singleton)
    private function __construct() {
        try {
            // Creează o nouă conexiune PDO la baza de date (localhost, port 3307, baza galerie-foto)
            $this->pdo = new PDO(
                'mysql:host=localhost;port=3307;dbname=galerie-foto;charset=utf8mb4',
                'root',
                ''
            );
            // Setează opțiunea de afișare a erorilor
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // În caz de eroare la conectare, se oprește execuția și se afișează mesajul
            die("Eroare la conexiunea la baza de date: " . $e->getMessage());
        }
    }

    // Metodă statică care returnează conexiunea PDO
    public static function connect() {
        // Creează o instanță dacă nu există deja (singleton)
        if (self::$instance === null) {
            self::$instance = new self();
        }
        // Returnează obiectul PDO pentru utilizare
        return self::$instance->pdo;
    }
}
