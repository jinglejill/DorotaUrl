<?php
    include_once('dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));
    
    
    
    if (
        isset ($_POST["countProduct"])
        )
    {
        $countProduct = $_POST["countProduct"];
        for($i=0; $i<$countProduct; $i++)
        {
            $productID[$i] = $_POST["productID".sprintf("%02d", $i)];
            $remark[$i] = $_POST["remark".sprintf("%02d", $i)];
            $receiptProductItemID[$i] = $_POST["receiptProductItemID".sprintf("%02d", $i)];
            $customerReceiptID[$i] = $_POST["customerReceiptID".sprintf("%02d", $i)];
        }
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
        
        
        
        for($i=0; $i<$countProduct; $i++)
        {
            //query statement
            $sql = "update product set Status = 'P', Remark = '$remark[$i]' where ProductID = $productID[$i]";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
        }
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from product where `ProductID` in ('$productID[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$productID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tProduct';
        $action = 'u';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //-----
        
        
        for($i=0; $i<$countProduct; $i++)
        {
            //query statement
            $sql = "update receiptproductitem set ProductType = 'P' where ReceiptProductItemID = $receiptProductItemID[$i]";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
        }
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from receiptproductitem where `ReceiptProductItemID` in ('$receiptProductItemID[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$receiptProductItemID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tReceiptProductItem';
        $action = 'u';
        $ret = doPushNotificationTask($con,$_POST["modifiedUser"],$_POST["modifiedDeviceToken"],$selectedRow,$type,$action);
        if($ret != "")
        {
            putAlertToDevice($_POST["modifiedUser"]);
            echo json_encode($ret);
            exit();
        }
        
        //----
        
        
        
        for($i=0; $i<$countProduct; $i++)
        {
            //query statement
            $sql = "update customerreceipt set trackingNo = '' where CustomerReceiptID = $customerReceiptID[$i]";
            $ret = doQueryTask($con,$sql,$_POST["modifiedUser"]);
            if($ret != "")
            {
                putAlertToDevice($_POST["modifiedUser"]);
                echo json_encode($ret);
                exit();
            }
            
        }
        
        
        //select row ที่แก้ไข ขึ้นมาเก็บไว้
        $sql = "select * from customerreceipt where `CustomerReceiptID` in ('$customerReceiptID[0]'";
        for($i=1; $i<$countProduct; $i++)
        {
            $sql .= ",'$customerReceiptID[$i]'";
        }
        $sql .= ")";
        $selectedRow = getSelectedRow($sql);
        
        
        //broadcast ไป device token อื่น
        $type = 'tCustomerReceipt';
        $action = 'u';
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
