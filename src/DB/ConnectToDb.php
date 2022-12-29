<?php

namespace Src\DB;

class  ConnectToDb
{

    public $host = 'localhost'; // имя хоста

    public $user = 'root';      // имя пользователя

    public $pass = '';          // пароль

    public $name = "reception";   // имя базы данных

    public $busy_info;

    public $empty_info;

    public function connect()
    {
        return mysqli_connect($this->host, $this->user, $this->pass, $this->name);
    }

    public function isCabinetEmpty($cabinet_number, $start, $end)
    {
        $link = $this->connect();
        $query = 'SELECT * FROM ORDERS WHERE cabinet_number =' . $cabinet_number;
        $result = mysqli_query($link, $query);
        $result_assoc = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $link->close();


        $counter = 0;

        foreach ($result_assoc as $item) {

            $res = $this->isBetweenEmpty($item['start_date'], $item['end_date'], $start, $end);

            if ($res === 'Ты тупой или Тестер') {
                $type = $res;
                break;

            } else if ($res !== 'Ты тупой или Тестер' && !$res) {
                $this->busy_info = $type = $this->ifBusy($item['user_id'], $item['start_date'], $item['end_date']);
                break;

            } else if ($res && !$this->busy_info) {

                if ($this->empty_info) {
                    break;
                } else if ($counter === count($result_assoc) - 1) {
                    $this->empty_info = $type = $this->ifEmpty($cabinet_number);
                }
            } else {
                echo $res;
            }

            $counter = $counter + 1;
        }


        return $type;
    }

    public function IsBetweenEmpty($db_start, $db_end, $user_start, $user_end)
    {
        if (strtotime($user_start) <= strtotime($user_end)) {

            if (((strtotime($user_start) >= strtotime($db_start)) and (strtotime($user_start) >= strtotime($db_end))) or ((strtotime($user_start) <= strtotime($db_start)) and (strtotime($user_start) <= strtotime($db_end)))) {

                return true;

            } else {

                return false;

            }
        } else {
            return 'Ты тупой или Тестер';
        }
    }

    public  function ifBusy($user_id, $start, $end){
        $link = $this->connect();
        $query = 'SELECT name FROM USERS WHERE ID = ' . $user_id;
        $result = mysqli_query($link, $query);
        $result_assoc = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $link->close();
        return "This Room is busy from " . $result_assoc[0]['name'] . '  from:' . $start . ' to: ' . $end;
    }

    public function ifEmpty($cabinet_number){
        $name = readline('Enter your name: ');
        $email = readline('Enter your email: ');
        $phone = readline('Enter your phone number: ');
        
        $this->saveUser($name, $email, $phone);
        die();
        $this->saveOrder($cabinet_number, $name, $email, $phone);
        return "This room is free: \n";
    }

    public function saveUser($name, $email, $phone){
        $link = $this->connect();
        try {

            $query = "INSERT INTO users (name, email, phone) VALUES ('.$name .', ' .$email .'  , '.$phone.')";
            mysqli_query($link, $query);
        }catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function saveOrder($cabinet_number, $user_id, $star_date, $end_date){
        file_put_contents('data.txt', print_r([$cabinet_number], 1));
        /*$link = $this->connect();
        $query = "INSERT INTO orders (firstname, lastname, email) VALUES ('John', 'Doe', 'john@example.com')";*/
    }
}