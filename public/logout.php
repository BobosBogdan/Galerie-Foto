<?php
// Pornim sesiunea pentru a putea accesa datele curente ale utilizatorului
session_start();

// Ștergem toate datele din sesiune (userul este delogat)
session_destroy();

// Redirecționăm utilizatorul către pagina de login
header("Location: login.php");
exit;
