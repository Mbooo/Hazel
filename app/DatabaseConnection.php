<?php

namespace App;

use PDOException;
use PDO;

class DatabaseConnection
{

    private static $user = "root";
    private static $dns = "mysql:host=localhost;dbname=hazel";


//    protected static $bdd;
//    private static $dns = 'mysql:host=database-etudiants.iut.univ-paris8.fr;dbname=dutinfopw201642';
//    private static $user = 'dutinfopw201642';
//    private static $password = 'pubepete';

    protected function connect()
    {

        try {
            $pdo = new PDO(self::$dns, self::$user);
            $pdo->setAttribute(PDO::ERRMODE_EXCEPTION, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
