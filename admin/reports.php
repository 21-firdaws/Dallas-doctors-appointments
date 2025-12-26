<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Reports</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .report-card{
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .report-header{
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .export-btn{
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .export-btn:hover{
            background: #218838;
        }
        .stat-box{
            display: inline-block;
            padding: 15px 25px;
            margin: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--primarycolor);
        }
        .stat-number{
            font-size: 32px;
            font-weight: bold;
            color: var(--primarycolor);
        }
        .stat-label{
            font-size: 14px;
            color: #6c757d;
        }
        .filter-section{
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .chart-container{
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }
    }else{
        header("location: ../login.php");
    }

    include("../connection.php");
    date_default_timezone_set('Asia/Kolkata');

    // Get date range from filter or set defaults
    $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : date('Y-m-01');
    $date_to = isset($_POST['date_to']) ? $_POST['date_to'] : date('Y-m-d');
    $report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'all';

    // Export functionality
    if(isset($_GET['export'])){
        $export_type = $_GET['export'];
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="report_'.$export_type.'_'.date('Y-m-d').'.csv"');
        
        $output = fopen('php://output', 'w');
        
        if($export_type == 'appointments'){
            fputcsv($output, array('Appointment ID', 'Patient Name', 'Doctor Name', 'Session Title', 'Date', 'Time', 'Appointment Number'));
            $result = $database->query("SELECT a.appoid, p.pname, d.docname, s.title, s.scheduledate, s.scheduletime, a.apponum 
                                       FROM appointment a 
                                       INNER JOIN patient p ON a.pid = p.pid 
                                       INNER JOIN schedule s ON a.scheduleid = s.scheduleid 
                                       INNER JOIN doctor d ON s.docid = d.docid 
                                       WHERE a.appodate BETWEEN '$date_from' AND '$date_to'
                                       ORDER BY a.appodate DESC");
            while($row = $result->fetch_assoc()){
                fputcsv($output, $row);
            }
        }
        elseif($export_type == 'doctors'){
            fputcsv($output, array('Doctor ID', 'Name', 'Email', 'NIC', 'Telephone', 'Specialties', 'Total Sessions', 'Total Appointments'));
            $result = $database->query("SELECT d.*, sp.sname,
                                       (SELECT COUNT(*) FROM schedule WHERE docid = d.docid) as total_sessions,
                                       (SELECT COUNT(*) FROM appointment a INNER JOIN schedule s ON a.scheduleid = s.scheduleid WHERE s.docid = d.docid) as total_appointments
                                       FROM doctor d 
                                       LEFT JOIN specialties sp ON d.specialties = sp.id");
            while($row = $result->fetch_assoc()){
                fputcsv($output, array($row['docid'], $row['docname'], $row['docemail'], $row['docnic'], $row['doctel'], $row['sname'], $row['total_sessions'], $row['total_appointments']));
            }
        }
        elseif($export_type == 'patients'){
            fputcsv($output, array('Patient ID', 'Name', 'Email', 'NIC', 'Telephone', 'Date of Birth', 'Total Appointments'));
            $result = $database->query("SELECT p.*,
                                       (SELECT COUNT(*) FROM appointment WHERE pid = p.pid) as total_appointments
                                       FROM patient p");
            while($row = $result->fetch_assoc()){
                fputcsv($output, array($row['pid'], $row['pname'], $row['pemail'], $row['pnic'], $row['ptel'], $row['pdob'], $row['total_appointments']));
            }
        }
        
        fclose($output);
        exit();
    }

    // Calculate statistics
    $total_doctors = $database->query("SELECT COUNT(*) as count FROM doctor")->fetch_assoc()['count'];
    $total_patients = $database->query("SELECT COUNT(*) as count FROM patient")->fetch_assoc()['count'];
    $total_appointments = $database->query("SELECT COUNT(*) as count FROM appointment WHERE appodate BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['count'];
    $total_sessions = $database->query("SELECT COUNT(*) as count FROM schedule WHERE scheduledate BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['count'];
    $completed_appointments = $database->query("SELECT COUNT(*) as count FROM appointment a INNER JOIN schedule s ON a.scheduleid = s.scheduleid WHERE s.scheduledate < CURDATE() AND s.scheduledate BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['count'];
    $upcoming_appointments = $database->query("SELECT COUNT(*) as count FROM appointment a INNER JOIN schedule s ON a.scheduleid = s.scheduleid WHERE s.scheduledate >= CURDATE() AND s.scheduledate BETWEEN '$date_from' AND '$date_to'")->fetch_assoc()['count'];
    ?>

    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@edoc.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">Doctors</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedule</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointment</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-patient">
                        <a href="patient.php" class="non-style-link-menu"><div><p class="menu-text">Patients</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord menu-active menu-icon-dashbord-active">
                        <a href="reports.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">Reports</p></div></a>
                    </td>
                </tr>
            </table>
        </div>

        <div class="dash-body">
            <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;margin-top:25px;">
                <tr>
                    <td width="13%">
                        <a href="index.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Reports & Analytics</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo date('Y-m-d'); ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>

                <!-- Filter Section -->
                <tr>
                    <td colspan="4">
                        <div class="filter-section">
                            <form method="POST" action="">
                                <table width="100%">
                                    <tr>
                                        <td width="25%">
                                            <label class="form-label">Date From:</label>
                                            <input type="date" name="date_from" class="input-text" value="<?php echo $date_from; ?>">
                                        </td>
                                        <td width="25%">
                                            <label class="form-label">Date To:</label>
                                            <input type="date" name="date_to" class="input-text" value="<?php echo $date_to; ?>">
                                        </td>
                                        <td width="25%">
                                            <label class="form-label">Report Type:</label>
                                            <select name="report_type" class="box">
                                                <option value="all" <?php echo ($report_type=='all')?'selected':''; ?>>All Reports</option>
                                                <option value="appointments" <?php echo ($report_type=='appointments')?'selected':''; ?>>Appointments Only</option>
                                                <option value="doctors" <?php echo ($report_type=='doctors')?'selected':''; ?>>Doctors Only</option>
                                                <option value="patients" <?php echo ($report_type=='patients')?'selected':''; ?>>Patients Only</option>
                                            </select>
                                        </td>
                                        <td width="25%">
                                            <label class="form-label">&nbsp;</label>
                                            <input type="submit" value="Generate Report" class="login-btn btn-primary btn" style="width:100%">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- Summary Statistics -->
                <tr>
                    <td colspan="4">
                        <div class="report-card">
                            <h2 style="color: var(--primarycolor);">Summary Statistics</h2>
                            <p style="color: #6c757d;">Period: <?php echo $date_from; ?> to <?php echo $date_to; ?></p>
                            <div style="text-align: center;">
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $total_doctors; ?></div>
                                    <div class="stat-label">Total Doctors</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $total_patients; ?></div>
                                    <div class="stat-label">Total Patients</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $total_appointments; ?></div>
                                    <div class="stat-label">Total Appointments</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $total_sessions; ?></div>
                                    <div class="stat-label">Total Sessions</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $completed_appointments; ?></div>
                                    <div class="stat-label">Completed</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-number"><?php echo $upcoming_appointments; ?></div>
                                    <div class="stat-label">Upcoming</div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Doctor Performance Report -->
                <?php if($report_type == 'all' || $report_type == 'doctors'): ?>
                <tr>
                    <td colspan="4">
                        <div class="report-card">
                            <div class="report-header">
                                <h3 style="color: var(--primarycolor);">Doctor Performance Report</h3>
                                <a href="?export=doctors&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                    <button class="export-btn">Export to CSV</button>
                                </a>
                            </div>
                            <div class="abc scroll">
                                <table width="100%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Doctor Name</th>
                                            <th class="table-headin">Specialty</th>
                                            <th class="table-headin">Total Sessions</th>
                                            <th class="table-headin">Total Appointments</th>
                                            <th class="table-headin">Avg. Appointments/Session</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $doctor_report = $database->query("
                                            SELECT d.docname, sp.sname,
                                            COUNT(DISTINCT s.scheduleid) as total_sessions,
                                            COUNT(a.appoid) as total_appointments,
                                            ROUND(COUNT(a.appoid)/COUNT(DISTINCT s.scheduleid), 2) as avg_appointments
                                            FROM doctor d
                                            LEFT JOIN specialties sp ON d.specialties = sp.id
                                            LEFT JOIN schedule s ON d.docid = s.docid AND s.scheduledate BETWEEN '$date_from' AND '$date_to'
                                            LEFT JOIN appointment a ON s.scheduleid = a.scheduleid
                                            GROUP BY d.docid
                                            ORDER BY total_appointments DESC
                                        ");

                                        while($row = $doctor_report->fetch_assoc()){
                                            echo '<tr>
                                                <td>'.$row['docname'].'</td>
                                                <td>'.$row['sname'].'</td>
                                                <td style="text-align:center;">'.$row['total_sessions'].'</td>
                                                <td style="text-align:center;">'.$row['total_appointments'].'</td>
                                                <td style="text-align:center;">'.($row['total_sessions'] > 0 ? $row['avg_appointments'] : '0').'</td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <!-- Patient Activity Report -->
                <?php if($report_type == 'all' || $report_type == 'patients'): ?>
                <tr>
                    <td colspan="4">
                        <div class="report-card">
                            <div class="report-header">
                                <h3 style="color: var(--primarycolor);">Patient Activity Report</h3>
                                <a href="?export=patients&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                    <button class="export-btn">Export to CSV</button>
                                </a>
                            </div>
                            <div class="abc scroll">
                                <table width="100%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Patient Name</th>
                                            <th class="table-headin">Email</th>
                                            <th class="table-headin">Total Appointments</th>
                                            <th class="table-headin">Last Appointment</th>
                                            <th class="table-headin">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $patient_report = $database->query("
                                            SELECT p.pname, p.pemail,
                                            COUNT(a.appoid) as total_appointments,
                                            MAX(s.scheduledate) as last_appointment
                                            FROM patient p
                                            LEFT JOIN appointment a ON p.pid = a.pid
                                            LEFT JOIN schedule s ON a.scheduleid = s.scheduleid AND s.scheduledate BETWEEN '$date_from' AND '$date_to'
                                            GROUP BY p.pid
                                            ORDER BY total_appointments DESC
                                        ");

                                        while($row = $patient_report->fetch_assoc()){
                                            $status = $row['total_appointments'] > 0 ? 'Active' : 'Inactive';
                                            $status_color = $row['total_appointments'] > 0 ? 'green' : 'gray';
                                            echo '<tr>
                                                <td>'.$row['pname'].'</td>
                                                <td>'.$row['pemail'].'</td>
                                                <td style="text-align:center;">'.$row['total_appointments'].'</td>
                                                <td style="text-align:center;">'.($row['last_appointment'] ? $row['last_appointment'] : 'N/A').'</td>
                                                <td style="text-align:center;color:'.$status_color.';font-weight:bold;">'.$status.'</td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <!-- Appointment Details Report -->
                <?php if($report_type == 'all' || $report_type == 'appointments'): ?>
                <tr>
                    <td colspan="4">
                        <div class="report-card">
                            <div class="report-header">
                                <h3 style="color: var(--primarycolor);">Appointment Details Report</h3>
                                <a href="?export=appointments&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                    <button class="export-btn">Export to CSV</button>
                                </a>
                            </div>
                            <div class="abc scroll">
                                <table width="100%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Appt. Number</th>
                                            <th class="table-headin">Patient</th>
                                            <th class="table-headin">Doctor</th>
                                            <th class="table-headin">Session</th>
                                            <th class="table-headin">Date</th>
                                            <th class="table-headin">Time</th>
                                            <th class="table-headin">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $appointment_report = $database->query("
                                            SELECT a.apponum, p.pname, d.docname, s.title, s.scheduledate, s.scheduletime
                                            FROM appointment a
                                            INNER JOIN patient p ON a.pid = p.pid
                                            INNER JOIN schedule s ON a.scheduleid = s.scheduleid
                                            INNER JOIN doctor d ON s.docid = d.docid
                                            WHERE a.appodate BETWEEN '$date_from' AND '$date_to'
                                            ORDER BY a.appodate DESC
                                        ");

                                        $today = date('Y-m-d');
                                        while($row = $appointment_report->fetch_assoc()){
                                            $status = $row['scheduledate'] < $today ? 'Completed' : 'Upcoming';
                                            $status_color = $row['scheduledate'] < $today ? 'green' : 'orange';
                                            echo '<tr>
                                                <td style="text-align:center;font-weight:bold;">'.$row['apponum'].'</td>
                                                <td>'.$row['pname'].'</td>
                                                <td>'.$row['docname'].'</td>
                                                <td>'.$row['title'].'</td>
                                                <td style="text-align:center;">'.$row['scheduledate'].'</td>
                                                <td style="text-align:center;">'.$row['scheduletime'].'</td>
                                                <td style="text-align:center;color:'.$status_color.';font-weight:bold;">'.$status.'</td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

                <!-- Specialty Distribution -->
                <tr>
                    <td colspan="4">
                        <div class="report-card">
                            <h3 style="color: var(--primarycolor);">Specialty Distribution</h3>
                            <div class="abc scroll">
                                <table width="100%" class="sub-table scrolldown" border="0">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">Specialty</th>
                                            <th class="table-headin">Number of Doctors</th>
                                            <th class="table-headin">Total Sessions</th>
                                            <th class="table-headin">Total Appointments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $specialty_report = $database->query("
                                            SELECT sp.sname,
                                            COUNT(DISTINCT d.docid) as doctor_count,
                                            COUNT(DISTINCT s.scheduleid) as session_count,
                                            COUNT(a.appoid) as appointment_count
                                            FROM specialties sp
                                            LEFT JOIN doctor d ON sp.id = d.specialties
                                            LEFT JOIN schedule s ON d.docid = s.docid AND s.scheduledate BETWEEN '$date_from' AND '$date_to'
                                            LEFT JOIN appointment a ON s.scheduleid = a.scheduleid
                                            GROUP BY sp.id
                                            ORDER BY appointment_count DESC
                                        ");

                                        while($row = $specialty_report->fetch_assoc()){
                                            echo '<tr>
                                                <td>'.$row['sname'].'</td>
                                                <td style="text-align:center;">'.$row['doctor_count'].'</td>
                                                <td style="text-align:center;">'.$row['session_count'].'</td>
                                                <td style="text-align:center;">'.$row['appointment_count'].'</td>
                                            </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>