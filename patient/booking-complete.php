<?php
session_start();

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
        header("location: ../login.php");
    }else{
        $useremail=$_SESSION["user"];
    }
}else{
    header("location: ../login.php");
}

//import database
include("../connection.php");
$sqlmain= "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s",$useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["pid"];
$username=$userfetch["pname"];

if($_POST){
    if(isset($_POST["booknow"])){
        $apponum=$_POST["apponum"];
        $scheduleid=$_POST["scheduleid"];
        $date=$_POST["date"];
        
        // Insert appointment and get the appointment ID
        $sql2="insert into appointment(pid,apponum,scheduleid,appodate) values ($userid,$apponum,$scheduleid,'$date')";
        $result= $database->query($sql2);
        
        if($result){
            // Get the last inserted appointment ID
            $appoid = $database->insert_id;
            
            // Redirect to invoice page with the appointment ID
            header("location: invoice.php?appoid=".$appoid);
            exit();
        } else {
            // If booking failed, redirect back with error
            header("location: schedule.php?error=booking_failed");
            exit();
        }
    }
}
?>