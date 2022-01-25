<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;



class Controller
{
    public function __construct()
    {
    }

    static function jsonSuccessReponse($data)
    {
        $response = ["success" => true, "data" =>  $data];
        return json_encode($response);
    }

    static function jsonFailureReponse(string $message)
    {
        $response = ["success" => false, "message" =>  $message];
        return json_encode($response);
    }

    static function Redirect($url)
    {
        header('Location: ' . $url, true);
        return;
    }
}
