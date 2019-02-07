<?php

require_once ('db.php');

function getSpecificMetrics($service_line_id, $year)
{
    $sql = 'SELECT sm.id AS id, metric_id, metric, metric_def, threshold_direction, is_gateway_metric 
from specific_metrics sm, metrics
where sm.metric_id=metrics.id AND
sm.year='.$year.' and service_line_id='.$service_line_id;
    global $conn;
    $result = $conn->query($sql);
    $specmet_array = array();
    if ($result) {
        $arrid = 0;
        while ($row = $result->fetch_assoc()) {
            $specmet_array[$arrid]=$row;

            $sql1 = '
        SELECT smt.id as id, threshold, threshold_incentive_percent, message, color_hex, is_gateway_threshold
        from specific_metric_thresholds smt, threshold_colors colors, messages
        where smt.threshold_color_id=colors.id and smt.message_id=messages.id and specific_metric_id='.$row['id'];

            $result1 = $conn->query($sql1);

            if ($result1) {
                while ($row1 = $result1->fetch_assoc()) {
                    $specmet_array[$arrid]['thresholds'][$row1['id']] = $row1;
                }
            }
            $arrid++;
        }
    }
    return $specmet_array;
}

function getPerformacesByProvider ($provider_id, $year){
    $sql = "select metric_id, quarter, sum(numerator) as numerator, sum(denominator) as denominator
        from performances where provider_id=$provider_id and year=$year group by metric_id, quarter order by metric_id, quarter";
    global $conn;
    $performances = array();
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $performances[]=$row;
        }
        return $performances;
    }
    
}

function getServiceLineName ($service_line_id){
    $sql = "select service_line from service_lines where id=$service_line_id";
    global $conn;
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $service_line=$row['service_line'];
        }
        return $service_line;
    }
    
}

function getServiceLines () {
    $sql = "select id, service_line from service_lines";
    global $conn;
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $service_lines[]=$row;
        }
        return $service_lines;
    }
}

function getYears () {
    $sql = "select distinct year from specific_metrics";
    global $conn;
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $years[]=$row['year'];
        }
        return $years;
    }
}

function getCorrectThresholdValue ($valarr, $performance, $direction){
    $thresholds = array_keys ($valarr);
    if ($direction==0) {
        $thresholds1 = array_filter(
        $thresholds,
        function ($n) use ($performance) {
            return $performance >= $n;
        }
        );
        return $valarr[max($thresholds1)];
    } elseif ($direction==1){
        $thresholds1 = array_filter(
            $thresholds,
            function ($n) use ($performance) {
                return $n >= $performance;
            }
            );
        return $valarr[min($thresholds1)];
    } else {
        return 'An Error Occured Finding Value: Direction Invalid';
    }
}

function getProvidersByServiceLine ($service_line_id) {
    $sql = "select id, provider_name from providers where service_line_id=$service_line_id";
    global $conn;
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $providers[]=$row;
        }
        return $providers;
    }
}