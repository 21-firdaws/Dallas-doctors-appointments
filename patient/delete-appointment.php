<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }
}else{
    header("location: ../login.php");
}

if($_GET){
    //import database
    include("../connection.php");
    $id=$_GET["id"];
    
    // Delete the appointment
    $sql= $database->query("delete from appointment where appoid='$id';");
    
    // Redirect to patient index.php instead of appointment.php
    header("location: index.php");
    exit();
}
?>