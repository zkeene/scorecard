<?php

require_once('db.php');

function getSpecificMetrics($service_line_id, $year, $provider_level)
{
    $sql = 'SELECT sm.id AS id, metric_id, metric, metric_def, is_calculated_metric, threshold_direction, is_gateway_metric, is_beta_metric, is_tbd_metric 
    from specific_metrics sm, metrics
    where sm.metric_id=metrics.id AND ';

    if ($provider_level) {
        $sql .= 'sm.is_service_line_metric=0 AND ';
    }

    $sql .= 'sm.year='.$year.' and service_line_id='.$service_line_id.' order by metric_order';

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

function getPerformancesByProvider($provider_id, $year)
{
    $sql = "select metric_id, quarter, sum(numerator) as numerator, sum(denominator) as denominator
        from performances 
        where provider_id=$provider_id and year=$year
        group by metric_id, quarter order by metric_id, quarter";
    global $conn;
    $performances = array();
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $performances[]=$row;
        }  
    }
    return $performances;
}

function getPerformacesByServiceLine($service_line_id, $year)
{
    $sql = "select metric_id, quarter, sum(numerator) as numerator, sum(denominator) as denominator
        from performances, locations
        where performances.location_id = locations.id and locations.service_line_id=$service_line_id and year=$year
        group by metric_id, quarter order by metric_id, quarter";
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

function getServiceLineName($service_line_id)
{
    $sql = "select service_line from service_lines where id=$service_line_id";
    global $conn;
    $result = $conn->query($sql);
    $service_line = '';
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $service_line=$row['service_line'];
        }   
    }
    return $service_line;
}

function getServiceLines()
{
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

function getYears()
{
    $sql = "select distinct year from specific_metrics";
    global $conn;
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $years[] = $row['year'];
        }
        return $years;
    }
}

function getCorrectThresholdValue($valarr, $performance, $direction)
{
    $thresholds = array_keys($valarr);
    if ($direction == 0) {
        $thresholds1 = array_filter(
        $thresholds,
        function ($n) use ($performance) {
            return $performance >= $n;
        }
        );
        return $valarr[max($thresholds1)];
    } elseif ($direction == 1) {
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

function getProvidersByServiceLine($service_line_id)
{
    $sql = "select id, provider_name from providers where service_line_id=$service_line_id";
    global $conn;
    $result = $conn->query($sql);
    $providers = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $providers[]=$row;
        }
    }
    return $providers;
}

function getContract($provider_id)
{
    $sql = "SELECT total_incentive_amount, effective_quality_date, default_expire_date 
        FROM contracts 
        WHERE active=1 AND provider_id=$provider_id";
    global $conn;
    $result = $conn->query($sql);
    $contract = array('incentive'=>null, 'effective'=>null, 'default_expire'=>null);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contract['incentive'] = $row['total_incentive_amount'];
            $contract['effective'] = $row['effective_quality_date'];
            $contract['default_expire'] = $row['default_expire_date'];
        }
    }
    return $contract;
}

function curr_format($amount) {
    return '$'.number_format($amount, 0);
}

function getQuarterFromDate ($date_to_check) {
    return ceil(date('n', strtotime($date_to_check))/ 3);
}

function day_diff ($date1, $date2) {
   return date_diff($date1, $date2) -> format("%r%a");
}

function getContractStatusArray ($effective_str, $default_expire_str, $year_sel) {
    $effective = strtotime($effective_str);
    $default_expire = strtotime($default_expire_str);
    $year_start = strtotime($year_sel.'-1-1');
    $year_end = strtotime($year_sel.'-12-31');
    $quarter_start = array(1=>strtotime($year_sel.'-1-1'),2=>strtotime($year_sel.'-4-1'),3=>strtotime($year_sel.'-7-1'),4=>strtotime($year_sel.'-10-1'));
    $quarter_end = array(1=>strtotime($year_sel.'-3-31'),2=>strtotime($year_sel.'-6-30'),3=>strtotime($year_sel.'-9-30'),4=>strtotime($year_sel.'-12-31'));
    $quarter_status = array_fill(1, 4, null);

    //full eligbile (performance based): effective date at beginning of year or prior AND default expired prior to beginning of year
    if (($effective <= $year_start) && ($default_expire < $year_start)) {
        for ($m=1; $m < 5; $m++) {
            $quarter_status[$m] = 'eligible';
        }
        //full default (max possible): effective date at beginning of year or prior AND (default expires after year end OR no default expiration)
    } elseif (($effective <= $year_start) && (($default_expire >= $year_end) || (is_null($default_expire)))) {
        for ($m=1; $m < 5; $m++) {
            $quarter_status[$m] = 'default';
        }
        //full ineligible (zero): effective date is after year end OR effective date doesn't exist
    } elseif (($effective > $year_end) || (is_null($effective))) {
        for ($m=1; $m < 5; $m++) {
            $quarter_status[$m] = 'ineligible';
        }
        //not full year: will evaluate each quarter and each time period in quarter (first eval quarter to apply full logic if applicable)
    } else {
        for ($m=1; $m < 5; $m++) {
            if (($effective <= $quarter_start[$m]) && ($default_expire < $quarter_start[$m])) {
                $quarter_status[$m] = 'eligible';
            } elseif (($effective <= $quarter_start[$m]) && (($default_expire >= $quarter_end[$m]) || (is_null($default_expire)))) {
                $quarter_status[$m] = 'default';
            } elseif (($effective > $quarter_end[$m]) || (is_null($effective))) {
                $quarter_status[$m] = 'ineligible';
            } else {
                $quarter_status[$m] = 'partial';
            }
        }
    }
    return $quarter_status;
}

function getPartialQuarterPercent ($quarter, $effective_str, $default_expire_str, $year_sel) {

    $default_days = 0;
    $eligible_days = 0;

    $effective = date_create($effective_str);
    $default_expire = date_create($default_expire_str);
    $quarter_start = array(1=>date_create($year_sel.'-1-1'),2=>date_create($year_sel.'-4-1'),3=>date_create($year_sel.'-7-1'),4=>date_create($year_sel.'-10-1'));
    $quarter_end = array(1=>date_create($year_sel.'-3-31'),2=>date_create($year_sel.'-6-30'),3=>date_create($year_sel.'-9-30'),4=>date_create($year_sel.'-12-31'));
   
    $days_in_quarter = day_diff($quarter_start[$quarter], $quarter_end[$quarter]);
    $qtr_start_to_def_expire = day_diff($quarter_start[$quarter], $default_expire);
    $qtr_start_to_effective = day_diff($quarter_start[$quarter], $effective);
    $def_expire_to_qtr_end = day_diff($default_expire, $quarter_end[$quarter]);
    $effective_to_qtr_end = day_diff($effective, $quarter_end[$quarter]);
    $effective_to_def_expire = day_diff($effective,$default_expire);

    $effective_in_quarter = $effective <= $quarter_end[$quarter] && $effective >= $quarter_start[$quarter];
    $default_expire_in_quarter = $default_expire <= $quarter_end[$quarter] && $default_expire >= $quarter_start[$quarter];

    if ($effective_in_quarter && $default_expire_in_quarter){
        $eligible_days = $def_expire_to_qtr_end;
        $default_days = $effective_to_def_expire;
    } elseif ($effective_in_quarter) {
        $default_days = $effective_to_qtr_end;
    } elseif ($default_in_quarter) {
        $eligible_days = $def_expire_to_qtr_end;
        $default_days = $qtr_start_to_def_expire;
    }

    $partial_qtr_percent = array('default'=>($default_days/$days_in_quarter*100),'eligible'=>($eligible_days/$days_in_quarter*100));
    return $partial_qtr_percent;
}

function getGatewayStatus ($specificmetrics, $performances) {
    $gateway_status = array_fill(1,4,1);
    $gateway_key = array_search(1,array_column($specificmetrics,'is_gateway_metric'));
    if ($gateway_key!="") {
        $gateway_metric_id = $specificmetrics[$gateway_key]['metric_id'];
        $gateway_threshold_key = array_search(1, array_column($specificmetrics[$gateway_key]['thresholds'], 'is_gateway_threshold', 'id'));
        $gateway_threshold = $specificmetrics[$gateway_key]['thresholds'][$gateway_threshold_key]['threshold'];
        $gateway_down = $specificmetrics[$gateway_key]['threshold_direction'];

        $perf_keys = array_keys(array_column($performances, 'metric_id'), $gateway_metric_id);

        $perf_arr = array();

        foreach ($perf_keys as $perf_key) {
            if($specificmetrics[$gateway_key]['is_calculated_metric']){
                $performance = $performances[$perf_key]['numerator'];
            } else {
                $performance = $performances[$perf_key]['numerator']/$performances[$perf_key]['denominator']*100;
            }
            if ($gateway_down) {
                if ($performance >= $gateway_threshold) {
                    $gateway_status[$performances[$perf_key]['quarter']] = 0;
                }
            } else {
                if ($performance <= $gateway_threshold) {
                    $gateway_status[$performances[$perf_key]['quarter']] = 0;
                }
            }
        }
    }
    return $gateway_status;    
}

function getNoDataMetrics ($specificmetrics, $performances, $quarter) {
    $metric_ids = array();
    foreach ($specificmetrics as $specificmetric) {
        $metric_ids[] = $specificmetric['metric_id'];
    }

    $form_perf = array_fill(1,4,array());
    foreach ($performances as $performance) {
        $form_perf[$performance['quarter']][]=$performance['metric_id'];
    }

    $no_data_metrics = array_fill(1,4,array());

    for ($i=1; $i <= $quarter; $i++){
        foreach ($metric_ids as $metric_id) {
            if (!in_array($metric_id, $form_perf[$i],TRUE)){
                $no_data_metrics[$i][] = $metric_id;
            }
        }
    }
    return $no_data_metrics;
}