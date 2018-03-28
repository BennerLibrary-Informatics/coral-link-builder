<?php
    class CoralDatabase {

      private $dbhost;
      private $dbuser;
      private $dbpass;
      private $dbname;

      var $conn;

      function __construct() {
          error_reporting(E_ALL);
          ini_set('display_errors', 1);


          $path = $_SERVER['DOCUMENT_ROOT'];
          $path .= "/bin/CORAL/class";

          if (!file_exists($path . '/db_config/local.php')) {
            die('Local config file has not been created, run `cp local.php.dist local.php` inside the config folder and fill in the values.');
          }


          $config = require($path . '/db_config/local.php');


          $this->dbhost = $config['host'];
          $this->dbuser = $config['user'];
          $this->dbpass = $config['pass'];
          $this->dbname = $config['name'];

          $this->connect();
        }

        function connect() {
            $this->conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }


            //$this->conn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
            //mysql_select_db($this->dbname, $this->conn);
            //if(!$this->conn){
              //  die("Could not connect to the database");
            //}
            //$db_selected = mysqli_select_db($this->dbname, $this->conn);
            /*if (!$db_selected) {
              die("Can\'t use db_name : " . mysqli_error());
            }*/
        }

        function __destruct() {
            mysqli_close($this->conn);
        }
    }
?>
