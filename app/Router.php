<?php


namespace App;

use App\Controllers\Controller;
use App\Models\UserModel;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Controllers\UserController;
use App\Controllers\BookingController;

class Router
{
    public $loader;
    public $twig;
    public function __construct()
    {
        $this->loader = new FilesystemLoader(__DIR__ . '/Views');
        $this->twig = new Environment($this->loader);
    }
    public function route(string $uri)
    {



        $varsForView = [
            "loggedin" => isset($_SESSION["loggedin"]) ? $_SESSION["loggedin"] : false,
        ];
        $varsForView["logoHazelText"] = "HAZEL";



        if (preg_match('/api\/([A-Za-z]+)/', $uri, $apiRouteMatch)) {
            return $this->APIRoutes($uri);
        }

        $userController = new UserController();
        $bookingController = new BookingController();
        $userModel = new UserModel();
        switch ($uri) {
            case '/':
                # code...
                $varsForView["navLinkActive"] = 'home';
                echo $this->twig->render('home.html.twig', $varsForView);
                break;

            case '/members':
                # code...
                $varsForView["navLinkActive"] = 'members';
                $varsForView["members"] = $userController->getAllUsers();
                echo $this->twig->render('members.html.twig', $varsForView);
                break;


            case (bool)preg_match('/members\/([A-Za-z]+)/', $uri, $regResult):

                $username = $regResult[1];
                //If we try to go on our profil page
                if(isset($_SESSION["auth"]) && $username === $_SESSION["auth"]["username"]){
                    Controller::Redirect('/profile');
                }
                $user = $userController->isUserExistsInDatabase($username);
                $varsForView["navLinkActive"] = 'members';
                if (!$user) {
                    echo "Cet utilisateur n'existe pas";
                    return;
                }

                $varsForView["userPage"] = $user;
                if(isset($_SESSION["auth"]))
                    $varsForView["loggedUser"] = $_SESSION["auth"];

                echo $this->twig->render('member.html.twig', $varsForView);
                break;


            case '/profile':
                Controller::Redirect('/profile/edit/informations');
                break;



            case (bool)preg_match('/profile\/edit\/([A-Za-z]+)/', $uri, $regResult):
                $varsForView["navLinkActive"] = "edit";
                $varsForView["userPage"] = $userModel->findOneByUsername($_SESSION["auth"]["username"]);
                $varsForView["editAction"] = $regResult[1];
                echo $this->twig->render('profile.html.twig',$varsForView);
                break;

            case '/profile/admin':
                $varsForView["navLinkActive"] = "admin";
                $varsForView["userPage"] = $userModel->findOneByUsername($_SESSION["auth"]["username"]);
                $varsForView["members"] = $userController->getAllUsers();
                echo $this->twig->render('profile.html.twig',$varsForView);
                break;


            case (bool)preg_match('/profile\/([A-Za-z]+)/', $uri, $regResult):

                $varsForView["navLinkActive"] = $regResult[1];
                $varsForView["userPage"] = $userModel->findOneByUsername($_SESSION["auth"]["username"]);
                $varsForView["appointments"] = $bookingController->getBookings($_SESSION["auth"]["id"],$_SESSION["auth"]["role"]);

                echo $this->twig->render('profile.html.twig',$varsForView);
                break;



            /** ACCOUNT ROUTES */
            case '/login':
                # code...
                if (UserController::isUserLoggedIn()) {
                    # code...
                    Controller::Redirect("/");
                }

                $varsForView["navLinkActive"] = 'login';
                echo $this->twig->render('login.html.twig', $varsForView);
                break;

            case "/register":
                # code...
                if (UserController::isUserLoggedIn()) {
                    # code...
                    Controller::Redirect("/");
                }
                $varsForView["navLinkActive"] = 'register';
                echo $this->twig->render('register.html.twig', $varsForView);
                break;
            case '/logout':
                # code...
                UserController::handleLogout();
                break;

            default:
                # code...
                echo $uri;

                break;
        }
    }

    private function APIRoutes(string $uri)
    {

        $userController = new UserController;
        $bookingController = new BookingController();
        switch ($uri) {
            case '/api/register':
                # code...
                if (isset($_POST["name"]) && isset($_POST["username"]) && isset($_POST["email"]) && isset($_POST["role"])) {
                    # code...
                    $userController->handleRegister($_POST);
                } else {
                    echo "Register Values NOT Valid";
                }

                break;
            case '/api/login':
                # code...
                if (isset($_POST["username"]) && isset($_POST["password"])) {
                    # code...
                    $userController->handleLogin($_POST);
                } else {
                    echo "Informations manquantes";
                }
                break;
            case '/api/booking':
                if (isset($_POST["date"]) && isset($_POST["username"])) {

                    $bookingInformations = array('loggedUser' => $_SESSION["auth"]["id"], 'bookedUser' => $_POST["username"], 'date' => $_POST["date"]);
                    $bookingController->handleBooking($bookingInformations);
                } else {
                    # code...
                    echo "La date n'a pas été saisie";
                }
                break;


            case '/api/changePassword':
                if(isset($_POST["oldpassword"]) && isset($_POST["newpassword"])){

                    $userController->handleChangePassword(array('newpassword' => $_POST["newpassword"],'idUser' => $_SESSION["auth"]["id"],'username' => $_SESSION["auth"]["username"],'oldpassword' => $_POST["oldpassword"]));
                }else{
                    echo "Le mot de passe n'a pas été saisie";
                }
                break;


            case '/api/changeInformations':

                if(isset($_POST["description"]) && isset($_POST["dateOfBirth"]) && isset($_POST["name"]) && isset($_POST["userId"]) && isset($_POST["place"]) && isset($_POST["phone"])){

                    $userController->handleChangeInformations($_POST);
                }else{
                    echo "Des informations n'ont pas été saisies";
                }
                break;


            case '/api/confirm':
                if(isset($_POST["dateBrut"]) && isset($_POST["userWaitingForValidation"]) && isset($_POST["userValidating"])){
                    $bookingController->handleConfirm($_POST);
                }
                break;

            case '/api/refuse':
                if(isset($_POST["dateBrut"]) && isset($_POST["userWaitingForValidation"]) && isset($_POST["userValidating"])){
                    $bookingController->handleRefuse($_POST);
                }
                break;


            case '/api/delete':
                if(isset($_POST["id"]) && isset($_POST["role"])){
                    $userController->handleDelete($_POST);
                }
                break;


            default:
                # code...
                echo "API ROUTE DOES NOT EXIST";
                break;
        }
        return;
    }
}
