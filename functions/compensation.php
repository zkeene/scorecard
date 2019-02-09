    <?php
    $contract = getContract($provider['id']);

    //determine single quarter incentive
    if ($contract['incentive']) {
        $qtr_incentive = $contract['incentive']/4;
    } else {
        $qtr_incentive = 0;
    }

    $inc_array = array_fill(1,4,NULL);
    $percent_incentive = array_fill(1,4,NULL);
    $year_start = $year_sel.'-1-1';
    $year_end = $year_sel.'-12-31';

    $thresh_percent_arr = array_column($specificmetrics[$i]['thresholds'], 'threshold_incentive_percent', 'threshold');

    //full eligbile (performance based): effective date at beginning of year or prior AND default expired prior to beginning of year
    if (($contract['effective'] <= $year_start) && ($contract['default_expire'] < $year_start)) {
        $contract_status = 'full_eligible';
        for ($m=1; $m < 5; $m++) {
            if ($m<count($metric_perf)+1) {
                $percent_incentive[$m] = getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m-1], $specificmetrics[$i]['threshold_direction']);
                $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive/$incentive_metric_count;
            }
        }
    //full default (max possible): effective date at beginning of year or prior AND (default expires after year end OR no default expiration)
    } elseif (($contract['effective'] <= $year_start) && (($contract['default_expire'] >= $year_end) || (is_null($contract['default_expire'])))) {
        $contract_status = 'full_default';
        for ($m=1; $m < 5; $m++) {
            $percent_incentive = 100;
            $inc_array[$m] = $qtr_incentive;
        }
    //full ineligible (zero): effective date is after year end OR effective date doesn't exist
    } elseif (($contract['effective'] > $year_end) || (is_null($contract['effective']))) {
        $contract_status = 'full_ineligble';
    //not full year: will evaluate each quarter and each time period in quarter (first eval quarter to apply full logic if applicable)
    } else {
        $contract_status = 'not_full';
    }
    ?>
