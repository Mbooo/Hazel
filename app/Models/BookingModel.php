<?php

namespace App\Models;

use App\Models\Model;
use PDOException;
use PDO;
use DateTime;

class BookingModel extends Model
{
    private $mois = array('January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre');

    private $jours = array('Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche');

    public function __construct()
    {
        parent::__construct("appointments");
    }

    public function refuseAppointment(array $informations){
        if(!$this->tableName || !$informations){
            return false;
        }

        $idUserValidating = $this->getIdUser($informations["userValidating"]);
        $idUserWaiting = $this->getIdUser($informations["userWaitingForValidation"]);

        $sql = "UPDATE $this->tableName SET isConfirmedByPhotographe = 0, isConfirmedByClient = 0
                where date = :date and clientId = :clientId and photographeId = :photographeId";

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":date",$informations["dateBrut"]);
            $statement->bindParam(":clientId",$idUserWaiting["id"]);
            $statement->bindParam(":photographeId",$idUserValidating["id"]);
            return $statement->execute();
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }


    public function confirmAppointment(array $informations){
        if(!$this->tableName || !$informations){
            return false;
        }
        $idUserValidating = $this->getIdUser($informations["userValidating"]);
        $idUserWaiting = $this->getIdUser($informations["userWaitingForValidation"]);

        $sql = "UPDATE $this->tableName SET isConfirmedByPhotographe = 1
                where date = :date and clientId = :clientId and photographeId = :photographeId";

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":date",$informations["dateBrut"]);
            $statement->bindParam(":clientId",$idUserWaiting["id"]);
            $statement->bindParam(":photographeId",$idUserValidating["id"]);
            return $statement->execute();
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }

    public function bookAppointment(array $appointmentInformations)
    {
        if (!$this->tableName || !$appointmentInformations) {

            return false;
        }

        $sql = "INSERT INTO $this->tableName (clientId,photographeId,date)
        VALUES(:clientId,:photographeId,:date)";
        $idLoggedUser = $appointmentInformations["loggedUser"];
        $idBookedUser = $this->getIdUser($appointmentInformations["bookedUser"]);
        $date = $appointmentInformations["date"];

        try {
            //code...
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":clientId", $idLoggedUser);
            $statement->bindParam(":photographeId", $idBookedUser["id"]);
            $statement->bindParam(":date", $date);
            return $statement->execute();

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    private function getIdUser($username)
    {
        if (!$username) {
            return false;
        }
        $sql = "SELECT id from user where username = :username";
        try {

            $statement = $this->db->prepare($sql);
            $statement->bindParam(":username", $username);
            $statement->execute();
            return $statement->fetch();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getAppointmentsInformations($idUser, $role)
    {
        if(!$this->tableName || !$idUser || !$role){
            return false;
        }

        if($role === 'Photographe'){
            $sql = "SELECT date,name,username,isConfirmedByClient,isConfirmedByPhotographe FROM $this->tableName INNER JOIN user on clientId = user.id where photographeId = :id";
        }else{
            $sql = "SELECT date,name,username,isConfirmedByClient,isConfirmedByPhotographe from $this->tableName INNER JOIN user ON photographeId = user.id where clientId = :id";
        }

        try {
            $statement =$this->db->prepare($sql);
            $statement->bindParam(":id",$idUser);
            $statement->execute();

            $appointments = $statement->fetchAll();

            for($i = 0; $i < count($appointments); $i++){
                $date = $appointments[$i]["date"];

                $appointments[$i]["dateBrut"] = $date;
                $dateFormated = strftime('%A %d %B %Y %H %M',strtotime($date));
                $appointments[$i]["date"] = $this->tradDate($dateFormated);
            }
            return $appointments;
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    private function tradDate($date)
    {
        $keywords = preg_split("/[\s]/",$date);
        return $this->jours[$keywords[0]] . ' ' . $keywords[1] . ' ' . $this->mois[$keywords[2]] . ' ' . $keywords[3] . ' '. $keywords[4] . 'h'.$keywords[5];
    }
}
