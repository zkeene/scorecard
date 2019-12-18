<?php
include('functions/db.php');
include('functions/methods.php');
global $conn;

if ($_POST) {
    $dup_year = $_POST['dup_year'];
    $target_year = $_POST['target_year'];

    $sql_specific_metric = "insert into specific_metrics(
        service_line_id,
        metric_id,
        threshold_direction,
        is_gateway_metric,
        year,
        is_beta_metric,
        is_service_line_metric,
        is_tbd_metric,
        metric_order,
        weight,
        round_precision,
        origin_id
	)
    select 
        service_line_id,
        metric_id,
        threshold_direction,
        is_gateway_metric,
        $target_year as year,
        is_beta_metric,
        is_service_line_metric,
        is_tbd_metric,
        metric_order,
        weight,
        round_precision,
        id
    from specific_metrics where year=$dup_year";

    if ($conn->query($sql_specific_metric)) {
        echo 'Success duplicated specific metrics';

        //grab a "translation table" to figure out which thresholds should be copied where

        $sql_origin_target = "select
            id,
            origin_id
        from specific_metrics where origin_id IS NOT NULL";

        $origin_target = $conn->query($sql_origin_target);

        while ($row = $origin_target->fetch_assoc()) {
            $origin = $row['origin_id'];
            $target = $row['id'];
            $sql_threshold = "insert into specific_metric_thresholds (
                specific_metric_id,
                threshold,
                threshold_incentive_percent,
                message_id,
                threshold_color_id,
                is_gateway_threshold
            )
            SELECT 
                $target AS specific_metric_id,
                threshold,
                threshold_incentive_percent,
                message_id,
                threshold_color_id,
                is_gateway_threshold
            from 
                specific_metric_thresholds
            where
                specific_metric_id = $origin";
    
            if ($conn->query($sql_threshold)) {
                echo 'Success duplicated specific metric thresholds for specific metric#'.$target.'<br>';
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
        $sql_remove_origin_data = 'update specific_metrics set origin_id=NULL';
        if ($conn->query($sql_remove_origin_data)) {
            echo 'Origin data removed successfully';
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Duplicate Year</title>
</head>
<body>
<form enctype="multipart/form-data" action="duplicate_year.php" method="POST">
    Year to Duplicate: <input type="text" name="dup_year"><br/>
    Year to Duplicate to: <input type="text" name="target_year"><br/>
    <input type="submit" value="Duplicate" />
</form>
</body>
</html>