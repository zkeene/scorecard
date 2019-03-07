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

    $sql = "LOAD DATA LOCAL INFILE '$filepath' into table performances".
    ' FIELDS TERMINATED BY \',\' OPTIONALLY ENCLOSED BY \'"\' '.
    '(@var1, numerator, denominator) '.
    'SET provider_id = (select id from providers where '.$prov_type.'=@var1)'.
    ',year = '.$_POST['year_sel'].
    ',quarter = '.$_POST['quarter'].
    ',metric_id = '.$_POST['metric'];

    if ($conn->query($sql)) {
        echo "Success";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Performance Upload</title>
</head>
<body>
<form enctype="multipart/form-data" action="upload.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
    Year: <input name="year_sel" type="text" size="4" /><br/>
    Quarter: <select name="quarter">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select><br/>
    Metric: <select name="metric">
    <?php
        $sql = "select id, metric from metrics";
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