<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (isset ($_POST["username"])){
        $username = $_POST["username"];
    } else {
        $username = "-";
    }
    
    
    
    {
        // Check connection
        if (mysqli_connect_errno())
        {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        
        
        // Set autocommit to off
        mysqli_autocommit($con,FALSE);
        writeToLog("set auto commit to off");
        
        
        //select row ที่ delete ขึ้นมาเก็บไว้ก่อน
        $sql = "select * from UserAccount where `Username`= '$username'";
        $selectedRow = getSelectedRow($sql);
        
        
        //query statement
        $sql = "delete from `UserAccount` where `Username`= '$username'";
        $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        
        
        //broadcast ไป device token อื่น
        $type = 'tUserAccount';
        $action = 'd';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //-----
        
    }
    
    
    
    //do script successful
    mysqli_commit($con);
    sendPushNotificationToOtherDevices($_POST["modifiedDeviceToken"]);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);

    
    echo json_encode($response);
    exit();
?>
