<?php

namespace App\Controllers;


use App\Controllers\Controller;
use App\Models\UserModel;

class UserController extends Controller
{
    private $userModel;
    public function __construct(){
        $this->userModel = new UserModel();
    }



    public function handleDelete(array $userInformations)
    {


        if(!isset($userInformations["id"]) || !isset($userInformations["role"])){
            echo self::jsonFailureReponse("Informations manquantes");
            return;
        }

        if($this->userModel->deleteAccount($userInformations)){
            echo self::jsonSuccessReponse($userInformations);
        }else{
            echo self::jsonFailureReponse("Ce compte n'a pas pu être supprimé");
        }
    }
    public function handleChangeInformations(array $informations)
    {

        if(!isset($informations["userId"]) || !isset($informations["description"]) || !isset($informations["name"]) || !$informations["dateOfBirth"] || !$informations["phone"] || !$informations["place"]){
            echo self::jsonFailureReponse("Informations manquantes");
            return;
        }

        if($this->userModel->changeInformations($informations)){

            echo self::jsonSuccessReponse($informations);
        }else{
            echo self::jsonFailureReponse("Vos informations n'ont pas pu être modifiées");
        }
    }
    public function handleLogin(array $credentials)
    {
        if (!isset($credentials["username"]) || !isset($credentials["password"])) {

            echo self::jsonFailureReponse("Informations manquantes");
            return;
        }

        $user = $this->isUserExistsInDatabase($credentials["username"]);


        if (empty($user)) {
            echo self::jsonFailureReponse("Ce nom d'utilisateur / Email est inconnu");
            return;
        }


        if ($user["password"] == hash('sha256',$credentials["password"])) {

            unset($user['password']);

            $_SESSION["auth"] = $user;
            $_SESSION["loggedin"] = true;

            echo self::jsonSuccessReponse($user);
        } else {
            echo self::jsonFailureReponse("Mot de passe incorrect");
            return;
        }
    }

    public function handleChangePassword(array $changingInformations)
    {
        if(!isset($changingInformations["newpassword"]) || !isset($changingInformations["idUser"]) || !isset($changingInformations["oldpassword"]) || !isset($changingInformations["username"])){
            echo self::jsonFailureReponse("Des informations sont manquantes");
            return;
        }

        if (empty($changingInformations["newpassword"]) || empty($changingInformations["idUser"]) || empty($changingInformations["oldpassword"]) || empty($changingInformations["username"])){
            echo self::jsonFailureReponse("Des information n'ont pas été saisie");
            return;
        }

        if($this->userModel->changePassword($changingInformations)){
            echo self::jsonSuccessReponse($changingInformations);
        }else{
            echo self::jsonFailureReponse("Votre mot de passe actuel ne correspond pas à celui que vous avez saisi");
        }

    }


    public function handleRegister(array $credentials)
    {
        if (!isset($credentials["email"]) || !isset($credentials["username"]) || !isset($credentials["password"]) || !isset($credentials["name"]) || !isset($credentials["role"])) {
            # code...
            echo self::jsonFailureReponse("Une ou plusieurs des informations de connection n'ont pas été fournis");
            return;
        }


        $userByUsername = $this->isUserExistsInDatabase($credentials["username"]);

        if ($userByUsername) {
            echo self::jsonFailureReponse("Ce nom d'utilisateur existe déjà");
            return;
        }

        $userByEmail = $this->isUserExistsInDatabase($credentials["email"]);

        if ($userByEmail) {
            echo self::jsonFailureReponse("Cette adresse Email existe déjà");
            return;
        }


        $user = $this->userModel->createUser($credentials);

        unset($user['password']);

        $_SESSION["auth"] = $user;
        $_SESSION["loggedin"] = true;

        echo self::jsonSuccessReponse($user);

    }

    public function isUserExistsInDatabase($username)
    {


        //If the username is a mail
        if (preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $username)) {
            # code...
            return $this->userModel->findOneByEmail($username);
        }

        return $this->userModel->findOneByUsername($username);
    }

    static function handleLogout()
    {
        unset($_SESSION["auth"]);
        unset($_SESSION["loggedin"]);

        self::Redirect("/login");
    }

    public function getAllUsers()
    {

        return $this->userModel-> find();

    }

    static function isUserLoggedin()
    {
        return isset($_SESSION["loggedin"]) ? $_SESSION["loggedin"] : false;
    }


}
