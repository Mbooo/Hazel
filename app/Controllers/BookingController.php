<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\BookingModel;

class BookingController extends Controller
{
    private $bookingModel;
    public function __construct()
    {
        $this->bookingModel = new BookingModel();
    }

    public function handleRefuse(array $informations){
        if (!isset($informations["userValidating"]) || !isset($informations["dateBrut"]) || !isset($informations["userWaitingForValidation"])){
            echo self::jsonFailureReponse("Informations manquantes");
            return;
        }

        if($this->bookingModel->refuseAppointment($informations)){
            echo self::jsonSuccessReponse($informations);
        }else{
            echo self::jsonFailureReponse("Erreur dans l'annulation du rendez-vous");
        }
    }

    public function handleConfirm(array $informations){
        if (!isset($informations["userValidating"]) || !isset($informations["dateBrut"]) || !isset($informations["userWaitingForValidation"])){
            echo self::jsonFailureReponse("Informations manquantes");
            return;
        }

        if($this->bookingModel->confirmAppointment($informations)){
            echo self::jsonSuccessReponse($informations);
        }else{
            echo self::jsonFailureReponse("Erreur dans la confirmation du rendez-vous");
        }
    }

    public function handleBooking(array $bookingInformations)
    {
        if (!isset($bookingInformations["date"]) || !isset($bookingInformations["loggedUser"]) || !isset($bookingInformations["bookedUser"])) {
            echo self::jsonFailureReponse("Informations manquantes");
            return;
        }


        if(empty($bookingInformations["date"])){
            echo self::jsonFailureReponse("La date n'a pas été saisie");
            return;
        }
        if ($this->bookingModel->bookAppointment($bookingInformations)) {
            echo self::jsonSuccessReponse($bookingInformations);
        }
    }


    public function getBookings($username,$role)
    {
        if(!isset($username)){
            echo self::jsonFailureReponse("Le nom d'utilisateur n'a pas été renseigné");
            return;
        }
        return $this->bookingModel->getAppointmentsInformations($username,$role);
    }
}
