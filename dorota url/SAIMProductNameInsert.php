
<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if(isset($_POST["count"])
       )
    {
        $count = intval($_POST["count"]);
        
        
        for($i=0; $i<$count; $i++)
        {
            $productNameID[$i] = $_POST["productNameID".sprintf("%02d", $i)];
            $productCategory2[$i] = $_POST["productCategory2".sprintf("%02d", $i)];
            $productCategory1[$i] = $_POST["productCategory1".sprintf("%02d", $i)];
            $code[$i] = $_POST["code".sprintf("%02d", $i)];
            $name[$i] = $_POST["name".sprintf("%02d", $i)];
            $detail[$i] = $_POST["detail".sprintf("%02d", $i)];
            $active[$i] = $_POST["active".sprintf("%02d", $i)];

        }
    }
    else
    {
        $count = 0;
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
        
        
        for($i=0; $i<$count; $i++)
        {
            //query statement
            $sql = "insert into `productname` (`ProductNameID`,`ProductCategory2`, `ProductCategory1`, `Code`, `Name`, `Detail`, `Active`) values('$productNameID[$i]','$productCategory2[$i]', '$productCategory1[$i]', '$code[$i]', '$name[$i]', '$detail[$i]', $active[$i])";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
            
            
            //select row ที่แก้ไข ขึ้นมาเก็บไว้
            $sql = "select * from productname where ProductNameID='$productNameID[$i]'";
            $selectedRow = getSelectedRow($sql);
            
            
            //broadcast ไป device token อื่น
            $type = 'tProductName';
            $action = 'i';
            $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
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
