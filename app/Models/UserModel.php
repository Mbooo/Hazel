<?php

namespace App\Models;

use App\Models\Model;
use PDOException;
use PDO;

class UserModel extends Model
{

    public function __construct()
    {
        parent::__construct("user");
    }



    public function findOneByUsername(string $username)
    {
        if (!$this->tableName || !$username) {
            return false;
        }

        $sql = "SELECT * from $this->tableName
        where username = :username";

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":username", $username, PDO::PARAM_STR);

            $statement->execute();
            return $statement->fetch();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function findOneByEmail(string $email)
    {
        if (!$this->tableName || !$email) {
            return false;
        }


        $sql = "SELECT * from $this->tableName
        where email = :email";

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":email", $email, PDO::PARAM_STR);

            $statement->execute();
            return $statement->fetch();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    public function deleteAccount(array $userInformations){
        if(!$this->tableName || !$userInformations){
            return false;
        }

//        We're deleting user's appointments before deleting his account to avoid foreign key issues
        if($userInformations["role"] == "Photographe"){
            $sqlBeforeDeletingAccount = "DELETE FROM appointments where photographeId = :id";
        }else{
            $sqlBeforeDeletingAccount = "DELETE FROM appointments where clientId = :id";
        }

        try {
            $firstStatement = $this->db->prepare($sqlBeforeDeletingAccount);
            $firstStatement->bindParam(":id",$userInformations["id"]);
            $success = $firstStatement->execute();
        }catch (PDOException $e){
            echo $e->getMessage();
        }

        if($success){
            $sql = "DELETE FROM user where id = :id";

            try {
                $statement = $this->db->prepare($sql);
                $statement->bindParam(":id",$userInformations["id"]);
                return $statement->execute();
            }catch (PDOException $e){
                echo $e->getMessage();
            }
        }


    }
    public function changeInformations(array $informations)
    {

        if(!$this->tableName || !$informations){
            return false;
        }

        $sql = "UPDATE user SET name = :name, birthday = :dateOfBirth, description = :description, placeForAppointments = :place, phone = :phone where id = :id";

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":name",$informations["name"]);
            $statement->bindParam(":description",$informations["description"]);
            $statement->bindParam(":dateOfBirth",$informations["dateOfBirth"]);
            $statement->bindParam(":id",$informations["userId"]);
            $statement->bindParam(":phone",$informations["phone"]);
            $statement->bindParam(":place",$informations["place"]);
            return $statement->execute();
        }catch (PDOException $e){

            echo $e->getMessage();

        }
    }
    public function changePassword(array $changingInformations)
    {

        if(!$this->tableName || !$changingInformations){
            return false;
        }


        $user = $this->findOneByUsername($changingInformations["username"]);

        $actualPassword = $user["password"];
        if($actualPassword === hash('sha256',$changingInformations["oldpassword"])) {

            $hashedNewPassword = hash('sha256', $changingInformations["newpassword"]);

            $sql = "UPDATE $this->tableName SET password = :pwd where id = :id";

            try {
                $statement = $this->db->prepare($sql);
                $statement->bindParam(":pwd", $hashedNewPassword);
                $statement->bindParam(":id", $changingInformations["idUser"]);

                return $statement->execute();

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }else{
            return false;
        }
    }
    public function createUser(array $user)
    {
        if (!$this->tableName || !$user) {
            return false;
        }

        $sql = "INSERT INTO $this->tableName (username,password,name,role,email)
        VALUES ( :username,:password,:name,:role,:email)";

        $hashedPassword = hash('sha256',$user["password"]);

        try {
            $statement = $this->db->prepare($sql);
            $statement->bindParam(":email", $user["email"], PDO::PARAM_STR);
            $statement->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
            $statement->bindParam(":name", $user["name"], PDO::PARAM_STR);
            $statement->bindParam(":role", $user["role"], PDO::PARAM_STR);
            $statement->bindParam(":username", $user["username"], PDO::PARAM_STR);

            $statement->execute();

            return $this->findOneByUsername($user["username"]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
