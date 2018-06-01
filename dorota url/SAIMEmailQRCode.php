<?php
    include_once('./dbConnect.php');
    setConnectionValue($_POST['dbName']);
    writeToLog("file: " . basename(__FILE__));    
    printAllPost();
    
    function makeFirstLetterUpperCase($text)
    {
        return strtoupper(substr($text,0,1)) . substr($text,1,strlen($text)-1);
    }
    
    function makeAllLetterLowerCase($text)
    {
        return strtolower($text);
    }
    
    /** Include path **/
    ini_set('include_path', ini_get('include_path').';./../PHPExcel/Classes/');
    
    /** PHPExcel */
    include './../PHPExcel/Classes/PHPExcel.php';
    
    /** PHPExcel_Writer_Excel2007 */
    include './../PHPExcel/Writer/Excel2007.php';
    
//    include_once('SAIM/dbConnect.php');
    
//    setConnectionValue('DOROTA');
    
    
    
    
    if (isset ($_POST["countData"]))
    {
        
        $countData = $_POST["countData"];
        for($i=0; $i<$countData; $i++)
        {
            $codeWithoutNo[$i] = $_POST["codeWithoutNo".sprintf("%02d", $i)];
            $productName[$i] = $_POST["productName".sprintf("%02d", $i)];
            $color[$i] = $_POST["color".sprintf("%02d", $i)];
            $size[$i] = $_POST["size".sprintf("%02d", $i)];
            $price[$i] = $_POST["price".sprintf("%02d", $i)];
            $qty[$i] = $_POST["qty".sprintf("%02d", $i)];
        }
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    $sql = "SELECT ifnull(max(RunningID),0) as RunningID FROM `itemrunningid`";
    $selectedRow = getSelectedRow($sql);
    $maxProductID = $selectedRow[0]['RunningID'];
    
    
    
    
    $startingID = $maxProductID+1;
    $cellValue = [];
    $step = 1;
    for($k=0; $k<$countData; $k++)
    {
        for($i=1; $i<=$qty[$k]; $i++)
        {
            $qrCodeFormat = "SAIM " . makeFirstLetterUpperCase(makeAllLetterLowerCase($_POST['dbName'])) . "\n%s\nEnd";
            $code = $codeWithoutNo[$k] . sprintf("%06d", $maxProductID + $i);
            
            $fullCode = sprintf($qrCodeFormat,$code);
            
            $cellValue[$step+$i]['A'] = $fullCode;
            $cellValue[$step+$i]['B'] = $productName[$k];
            $cellValue[$step+$i]['C'] = $color[$k];
            $cellValue[$step+$i]['D'] = $size[$k];
            $cellValue[$step+$i]['E'] = $price[$k];
            if($i == $qty[$k])
            {
                $maxProductID = $maxProductID + $i;
                $step = $step+$i;
            }
        }
    }
    
    $sql2 = "";
    for($j=$startingID; $j<=$maxProductID; $j++)
    {
        $sql2 .= "INSERT INTO `itemrunningid`( `RunningID`) VALUES (".$j.");";
    }
    
    $result2 = mysqli_multi_query($con2,$sql2);
    if(result2)
    {
        
        //success
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Thidaporn Kijkamjai");
        $objPHPExcel->getProperties()->setLastModifiedBy("SAIM");
        $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
        $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
        
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->SetCellValue('A1','Code');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Style');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Color');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Size');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Price');
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        
        $objPHPExcel->getActiveSheet()->setTitle('Product item');
        for($j=2; $j<=$step; $j++)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$j, $cellValue[$j]['A']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$j, $cellValue[$j]['B']);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$j, $cellValue[$j]['C']);
            //                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$j, $cellValue[$j]['D']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$j, $cellValue[$j]['D'], PHPExcel_Cell_DataType::TYPE_STRING);
            $priceValue = number_format($cellValue[$j]['E']). " Baht";
            $objPHPExcel->getActiveSheet()->getStyle('E'.$j)->getNumberFormat()->setFormatCode('###,###,###');
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$j, $priceValue, PHPExcel_Cell_DataType::TYPE_STRING);
        }
        
        
        
        $fileName = $_POST['downloadLink'];
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('./../' . $fileName);
        

    }
    
    
    exit();
?>
