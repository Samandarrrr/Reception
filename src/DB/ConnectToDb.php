<?php

namespace Src\DB;

class  ConnectToDb
{

    /**
     * @var string
     */
    public $host = 'localhost'; // имя хоста

    /**
     * @var string
     */
    public $user = 'root';      // имя пользователя

    /**
     * @var string
     */
    public $pass = '';          // пароль

    /**
     * @var string
     */
    public $name = "reception";   // имя базы данных

    /**
     * @var
     */
    public $busy_info;

    /**
     * @var
     */
    public $empty_info;

    /**
     *
     * Function  connect
     * @return  false|\mysqli
     */
    public function connect()
    {
        return mysqli_connect($this->host, $this->user, $this->pass, $this->name);
    }

    /**
     *
     * Function  isCabinetEmpty
     * @param $cabinet_number
     * @param $start
     * @param $end
     * @return  bool|string|null
     */
    public function isCabinetEmpty($cabinet_number, $start, $end)
    {
        $link = $this->connect();
        $query = 'SELECT * FROM ORDERS WHERE cabinet_number =' . $cabinet_number;
        $result = mysqli_query($link, $query);
        $result_assoc = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $link->close();
        $counter = 0;
        if ($result->num_rows) {
            foreach ($result_assoc as $item) {

                $res = $this->isBetweenEmpty($item['start_date'], $item['end_date'], $start, $end);
                if ($res === 'Ты тупой или Тестер') {
                    $type = $res;
                    break;

                } else if ($res !== 'Ты тупой или Тестер' && !$res) {
                    $this->busy_info = $type = $this->ifBusy($item['user_id'], $item['start_date'], $item['end_date']);
                    break;

                } else if (($res && !$this->busy_info)) {

                    if ($this->empty_info) {
                        break;
                    } else if ($counter === count($result_assoc) - 1) {
                        $this->empty_info = $type = $this->ifEmpty($cabinet_number, $start, $end);
                    }
                } else {
                    $type = $res;
                    break;
                }

                $counter = $counter + 1;
            }
        } else {
            $type = $this->ifEmpty($cabinet_number, $start, $end);
        }


        return $type;
    }

    /**
     *
     * Function  IsBetweenEmpty
     * @param $db_start
     * @param $db_end
     * @param $user_start
     * @param $user_end
     * @return  bool|string
     */
    public function IsBetweenEmpty($db_start, $db_end, $user_start, $user_end)
    {

        if (strtotime($user_start) <= strtotime($user_end)) {
            if ((strtotime($user_start) <= strtotime($db_start)) && (strtotime($user_end) <= strtotime($db_end))) {

                return false;

            } else if ((strtotime($user_start) >= strtotime($db_start)) && (strtotime($user_start) <= strtotime($db_end))) {

                return false;

            } else if ((strtotime($user_start) <= strtotime($db_end)) && (strtotime($user_start) >= strtotime($db_end))) {

                return false;

            }
            else if ((strtotime($user_start) <= strtotime($db_end)) && (strtotime($user_end) >= strtotime($db_end))){

                return false;

            }
            else {

                return true;

            }
        } else {
            return 'Ты тупой или Тестер';
        }
    }

    /**
     *
     * Function  ifBusy
     * @param $user_id
     * @param $start
     * @param $end
     * @return  string
     */
    public function ifBusy($user_id, $start, $end)
    {
        $link = $this->connect();
        $query = 'SELECT name FROM USERS WHERE ID = ' . $user_id;
        $result = mysqli_query($link, $query);
        $result_assoc = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $link->close();
        return "This Room is busy from " . $result_assoc[0]['name'] . '  from:' . $start . ' to: ' . $end;
    }

    /**
     *
     * Function  ifEmpty
     * @param $cabinet_number
     */
    public function ifEmpty($cabinet_number, $start, $end)
    {
        $name = readline('Enter your name: ');
        $email = readline('Enter your email: ');
        $phone = readline('Enter your phone number: ');

        (int)$id = $this->saveUser($name, $email, $phone);
        $this->saveOrder($cabinet_number, $id, $start, $end);
        return "\nOrder received\n";
    }

    /**
     *
     * Function  saveUser
     * @param $name
     * @param $email
     * @param $phone
     */
    public function saveUser($name, $email, $phone)
    {
        $link = $this->connect();


        $query = "INSERT INTO users (name, email, phone_number) VALUES ('$name', '$email' , '$phone')";
        mysqli_query($link, $query);

        return mysqli_insert_id($link);

    }

    /**
     *
     * Function  saveOrder
     * @param $cabinet_number
     * @param $user_id
     * @param $star_date
     * @param $end_date
     */
    public function saveOrder($cabinet_number, $user_id, $start_date, $end_date)
    {
        $link = $this->connect();
        try {

            $query = "INSERT INTO orders (cabinet_number, user_id, start_date, end_date, notification) VALUES ('$cabinet_number', '$user_id', '$start_date', '$end_date', 0)";

            mysqli_query($link, $query);

            return mysqli_insert_id($link);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
