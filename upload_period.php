<?php
include('functions/db.php');
global $conn;

if ($_FILES) {
    if ($_POST['prov_type']==1) {
        $prov_type = 'SER';
    } elseif ($_POST['prov_type']==2) {
        $prov_type = 'NPI';
    }
    $filepath = $_FILES['userfile']['tmp_name'];
    if (strtoupper(substr(PHP_OS,0,3)) == 'WIN') {
        $filepath = str_replace('\\', '/', $_FILES['userfile']['tmp_name']);
    }

    $metric = $_POST['metric'];
    $sql_metric = "select is_calculated_metric from metrics where id=$metric";
    $result_metric = $conn->query($sql_metric);
    if ($result_metric) {
        while ($row = $result_metric->fetch_assoc()) {
            $calculated = $row['is_calculated_metric'];
        }
    }

    if($calculated){
        $sql_load = "LOAD DATA LOCAL INFILE '$filepath' into table performances".
        ' FIELDS TERMINATED BY \',\' OPTIONALLY ENCLOSED BY \'"\' LINES TERMINATED BY \'\\r\\n\''.
        '(@var1, numerator) '.
        'SET provider_id = (IFNULL((select id from providers where '.$prov_type.'=@var1), NULL))'.
        ',import_error = (IF((select id from providers where '.$prov_type.'=@var1), NULL, @var1))'.
        ',year = '.$_POST['year_sel'].
        ',quarter = '.$_POST['quarter'].
        ',metric_id = '.$_POST['metric'].
        ',period_performance = 1';
    } else {
        $sql_load = "LOAD DATA LOCAL INFILE '$filepath' into table performances".
    ' FIELDS TERMINATED BY \',\' OPTIONALLY ENCLOSED BY \'"\' LINES TERMINATED BY \'\\r\\n\''.
    '(@var1, numerator, denominator) '.
    'SET provider_id = (IFNULL((select id from providers where '.$prov_type.'=@var1), NULL))'.
    ',import_error = (IF((select id from providers where '.$prov_type.'=@var1), NULL, @var1))'.
    ',year = '.$_POST['year_sel'].
    ',quarter = '.$_POST['quarter'].
    ',metric_id = '.$_POST['metric'].
    ',period_performance = 1';
    }

    $allowed_sql = "select * from period_locks where year=$year and quarter=$quarter";

    if ($conn->query($allowed_sql)->num_rows==0) {
        if ($conn->query($sql_load)) {
            echo 'Success <a href=upload_errors.php>Errors</a>';
        } else {
            echo "Error: " . $sql_load . "<br>" . $conn->error;
        }
    } else {
        echo 'Period Locked: Unable to Upload New Perfomance Data';
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Performance Upload</title>
</head>
<body>
<form enctype="multipart/form-data" action="upload_period.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    Year: <input name="year_sel" type="text" size="4" /><br/>
    Period: <select name="quarter">
                <option value="1">1</option>
                <option value="2">2</option>
            </select><br/>
    Metric: <select name="metric">
    <?php
        $sql = "select id, metric from metrics order by metric";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo '<option value="'.$row['id'].'">'.$row['metric'].'</option>';
            }
        }
    ?></select><br/>
    Provider ID Type: <select name="prov_type">
                        <option value="1">SER</option>
                        <option value="2">NPI</option>
                    </select><br/>
    File: <input name="userfile" type="file" /><br/>
    <input type="submit" value="Send File" />
</form>
</body>
</html>