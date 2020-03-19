<?php
include('functions/db.php');
include('functions/methods.php');
global $conn;

if ($_POST) {
    $dup_metric = $_POST['dup_metric'];
    $target_sl = $_POST['target_sl'];

    $sql1 = "insert into specific_metrics (
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
        round_precision
    )
    SELECT 
        $target_sl AS service_line_id,
        metric_id,
        threshold_direction,
        is_gateway_metric,
        year,
        is_beta_metric,
        is_service_line_metric,
        is_tbd_metric,
        metric_order,
        weight,
        round_precision
    from 
        specific_metrics
    where
        id = $dup_metric";

    if ($conn->query($sql1)) {
        echo 'Success duplicated specific metric';

        $new_spec_met_id = $conn->insert_id;

        $sql2 = "insert into specific_metric_thresholds (
            specific_metric_id,
            threshold,
            threshold_incentive_percent,
            message_id,
            threshold_color_id,
            is_gateway_threshold
        )
        SELECT 
            $new_spec_met_id AS specific_metric_id,
            threshold,
            threshold_incentive_percent,
            message_id,
            threshold_color_id,
            is_gateway_threshold
        from 
            specific_metric_thresholds
        where
            specific_metric_id = $dup_metric";

        if ($conn->query($sql2)) {
            echo 'Success duplicated specific metric thresholds';
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
<title>Duplicate Specific Metric</title>
</head>
<body>
<form enctype="multipart/form-data" action="duplicate_specific.php" method="POST">

    Specific Metric to Duplicate: <select name="dup_metric">
    <?php
        $sql_spec_met = 'SELECT sm.id as id, service_line, metric, year FROM specific_metrics sm, service_lines sl, metrics m WHERE sm.service_line_id=sl.id AND sm.metric_id=m.id';
        $result = $conn->query($sql_spec_met);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo '<option value="'.$row['id'].'">'.$row['service_line'].' - '.$row['metric'].' - '.$row['year'].'</option>';
            }
        }
    ?>
    </select><br/>
    SL to Duplicate to:
    <select name="target_sl">
    <?php
    $service_lines = getServiceLines();
    foreach ($service_lines as $service_line) {
        echo '<option value='.$service_line['id'].'>'.$service_line['service_line'].'</option>';
    }
    ?>
    </select><br/>
    <input type="submit" value="Duplicate" />
</form>
</body>
</html>