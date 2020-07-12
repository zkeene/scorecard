<!DOCTYPE html>
<html lang="en">

<head>
    <title>KPN Quality Scorecard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <?php
            include('functions/methods.php');
            include('functions/graph.php');
            include('constructors/selector.php');
            
    $metrics_per_row = 2;

    $specificmetrics = getSpecificMetrics($service_line_id, $year_sel, TRUE);
    //metric count for paging purposes
    $metriccount = count($specificmetrics);
    //total incentive weight
    $incentive_metric_count = 0;
    foreach ($specificmetrics as $specificmetric) {
        $add_value = 0;
        if (!$specificmetric['is_beta_metric']){
            if ($specificmetric['weight'] != NULL) {
                $add_value = $specificmetric['weight'];
            } else {
                $add_value = 1;
            }
            $incentive_metric_count = $incentive_metric_count + $add_value;
        }
    }

    $providers = getProvidersByServiceLine($service_line_id);

    $comp_report = array();

    foreach ($providers as $provider) {

        $contract = getContract($provider['id']);
        if ($contract['total_incentive_amount']) {
            $qtr_incentive = $contract['total_incentive_amount']/4;
        } else {
            $qtr_incentive = 0;
        }

        $qtr_incentive_per_metric = ($incentive_metric_count > 0 ? ($qtr_incentive/$incentive_metric_count):0);

        $quarter_status = getContractStatusArray($contract['effective_quality_date'],$contract['default_expire_date'],$contract['inactive_date'],$year_sel);

        $performances = getPerformancesByProvider($provider['id'], $year_sel);

        if (isServiceLinePeriodBased($service_line_id)) {
            $period_performances = getPerformancesByProvider($provider['id'], $year_sel, 1);
        } else {
            $period_performances = array();
        }

        $no_data_metrics = getNoDataMetrics($specificmetrics, $performances, $quarter_sel);
        
        $gateway_status = getGatewayStatus($specificmetrics, $performances);

        foreach($quarter_status as $qtr => $qtr_status) {
            if ($gateway_status[$qtr]==0 && $quarter_status[$qtr]=='eligible') {
                $quarter_status[$qtr] = 'ineligible';
            }
        }

        $total_incentive = 0;

        $page = 0;
        $i = 0;
        echo '<div class="provider">';
        while ($page < ($metriccount/(2*$metrics_per_row))) {
            echo '
            <div class="page">
            <div class="row">
                <div class="logo">
                    <img src="logo.png" height="33.5">
                </div>
                <div class="scorecard_title">
                Quality Scorecard
                </div>';

            echo '<div class="identity">';
            echo getServiceLineName($service_line_id).' - ';
            echo $provider['provider_name'];
            echo "</div><div id='incentive'></div></div>\n";
            $row=0;
            while ($i < $metriccount && $row < 2) {
                echo '<div class="row">';
                $k=0;
                while ($i < $metriccount && $k < $metrics_per_row) {
                    $specific_metric = $specificmetrics[$i];
                    echo '<div class="metric">
                        <div class="metric_title">';
                    echo $specific_metric['metric'];
                    echo "</div>\n";

                    //build metric performance array
                    
                    $metric_perf = getMetricPerformanceArray($performances,$specific_metric);
                    
                    if (isServiceLinePeriodBased($service_line_id)) {
                        $period_metric_perf = getMetricPerformanceArray($period_performances,$specific_metric, TRUE);
                    } else {
                        $period_metric_perf = array();
                    }
                    
                    //color array
                    if (array_key_exists('thresholds', $specific_metric)) {
                        if (getNumOfThresholdQuarters($specific_metric['id'])>1){
                            $colors = array();
                            foreach ($specific_metric['thresholds'] as $threshold) {
                                $colors[$threshold['quarter']][$threshold['threshold']] = $threshold['color_hex'];
                            }
                        } else {
                            $colors = array_column($specific_metric['thresholds'], 'color_hex', 'threshold');
                        }
                    } else {
                        if (!$specific_metric['threshold_direction']) {
                            $colors = array(0=>'#4488ff');
                        } else {
                            $colors = array(100=>'#4488ff');
                        }
                    }
                    //perf array to pass to create graph
                    $perfarr = array();
                    foreach ($metric_perf as $key => $quarter) {
                            $perfarr[$key] = $quarter['performance'];
                    }

                    $period_perfarr = array();
                    foreach ($period_metric_perf as $key => $quarter) {
                        $period_perfarr[$key] = $quarter['performance'];
                    }

                    if (!$specific_metric['is_tbd_metric']) {
                        echo '<div class="metric_graph">';
                        if (getNumOfThresholdQuarters($specific_metric['id'])>1) {
                            $floating_threshold = 1;
                        } else {
                            $floating_threshold = 0;
                        }
                        if (isServiceLinePeriodBased($service_line_id)) {
                            createGraph($perfarr, $specific_metric['threshold_direction'], $colors, $quarter_sel, $period_perfarr, $floating_threshold);
                        } else {
                            createGraph($perfarr, $specific_metric['threshold_direction'], $colors, $quarter_sel, array(), $floating_threshold);
                        }
                        echo "</div>\n";
                    }
                    
                    //comp info array population
                    $inc_array = array_fill(1, 4, null);
                    $percent_incentive = array_fill(1, 4, null);

                    $specific_metric_overrides = getSpecificMetricOverrides($specific_metric['id']);

                    if (array_key_exists('thresholds', $specific_metric)) {
                        if (getNumOfThresholdQuarters($specific_metric['id'])==0) {
                            $thresh_percent_arr = array_column($specific_metric['thresholds'], 'threshold_incentive_percent', 'threshold');
                        } else {
                            $thresh_percent_arr = array();
                            foreach ($specific_metric['thresholds'] as $threshold) {
                                $thresh_percent_arr[$threshold['quarter']][$threshold['threshold']] = $threshold['threshold_incentive_percent'];
                            }
                        }
                    } else {
                        if (!$specific_metric['threshold_direction']) {
                            $thresh_percent_arr = array(0=>100);
                        } else {
                            $thresh_percent_arr = array(100=>100);
                        }
                    }

                    if ($specific_metric['weight'] != NULL) {
                        $metric_weight = $specific_metric['weight'];
                    } else {
                        $metric_weight = 1;
                    }

                    for ($m=1; $m <= $quarter_sel; $m++) {
                        if ($specific_metric['is_beta_metric']){
                            $percent_incentive[$m] = 0;
                            $inc_array[$m] = 0;
                        } else {
                            
                            $isOverriden = false;
                            foreach ($specific_metric_overrides as $override) {
                                if ($override['time_frame']==0){
                                    $isOverriden = true;
                                }
                                if ($override['time_frame']==1 && $override['target_quarter']==$m) {
                                    $isOverriden = true;
                                }
                            }

                            if (in_array($specific_metric['metric_id'], $no_data_metrics[$m], true) || $isOverriden) {
                                if ($quarter_status[$m]=='eligible') {
                                    if ($m<count($metric_perf)+1) {
                                        $percent_incentive[$m] = 100;
                                        $inc_array[$m] = $qtr_incentive_per_metric*$metric_weight;
                                    }
                                } elseif ($quarter_status[$m]=='default') {
                                    $percent_incentive[$m] = 100;
                                    $inc_array[$m] = $qtr_incentive_per_metric*$metric_weight;
                                } elseif ($quarter_status[$m]=='ineligible') {
                                    $percent_incentive[$m]=0;
                                } elseif ($quarter_status[$m]=='partial') {
                                    $partial_qtr_percent = getPartialQuarterPercent($m, $contract['effective_quality_date'], $contract['default_expire_date'], $contract['inactive_date'], $year_sel);
                                    if($gateway_status[$m]==0) {
                                        $partial_quarter_percent['eligible']=0;
                                    }
                                    $percent_incentive[$m] = $partial_qtr_percent['default'] + $partial_qtr_percent['eligible'];
                                    $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric*$metric_weight;
                                }
                            } else {
                                if ($quarter_status[$m]=='eligible') {
                                    if ($m<count($metric_perf)+1) { //this will need fixed to eval if it is a floating threshold and specify the qtr to send to getCorrectThreshold
                                        if (getNumOfThresholdQuarters($specific_metric['id'])==0) {
                                            $percent_incentive[$m] = getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m], $specific_metric['threshold_direction']);
                                        } else {
                                            $percent_incentive[$m] = getCorrectThresholdValue($thresh_percent_arr[$m], $perfarr[$m], $specific_metric['threshold_direction']);
                                        }
                                        $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric*$metric_weight;
                                    }
                                } elseif ($quarter_status[$m]=='default') {
                                    $percent_incentive[$m] = 100;
                                    $inc_array[$m] = $qtr_incentive_per_metric*$metric_weight;
                                } elseif ($quarter_status[$m]=='ineligible') {
                                    $percent_incentive[$m]=0;
                                } elseif ($quarter_status[$m]=='partial') {
                                    $partial_qtr_percent = getPartialQuarterPercent($m, $contract['effective_quality_date'], $contract['default_expire_date'], $contract['inactive_date'], $year_sel);
                                    if($gateway_status[$m]==0) {
                                        $partial_quarter_percent['eligible']=0;
                                    }
                                    if (getNumOfThresholdQuarters($specific_metric['id'])==0) {
                                        $percent_incentive[$m] = $partial_qtr_percent['default'] + ($partial_qtr_percent['eligible']/100*getCorrectThresholdValue($thresh_percent_arr, $perfarr[$m], $specific_metric['threshold_direction']));
                                    } else {
                                        $percent_incentive[$m] = $partial_qtr_percent['default'] + ($partial_qtr_percent['eligible']/100*getCorrectThresholdValue($thresh_percent_arr[$m], $perfarr[$m], $specific_metric['threshold_direction']));
                                    }
                                    $inc_array[$m] = $percent_incentive[$m]/100*$qtr_incentive_per_metric*$metric_weight;
                                }
                            }
                        }
                    }

                    if (($contract['pay_cycle_id']==3) && ($quarter_sel==4)){
                        $total_incentive = $total_incentive + array_sum($inc_array);
                    } else {
                        $total_incentive = $total_incentive + $inc_array[$quarter_sel];
                    }

                    include('constructors/metric_table.php');

                    if(isServiceLinePeriodBased($service_line_id)) {
                        include ('constructors/period_table.php');
                    }

                    echo '<div class="metric_message">';
                    if (array_key_exists('thresholds', $specific_metric)) {
                        $messages = array_column($specific_metric['thresholds'], 'message', 'threshold');
                    } else {
                        if (!$specific_metric['threshold_direction']) {
                            $messages = array(0=>null);
                        } else {
                            $messages = array(100=>null);
                        }
                    }
                    if (!in_array($specific_metric['metric_id'], $no_data_metrics[$quarter_sel], true)) {
                        echo getCorrectThresholdValue($messages, $metric_perf[$quarter_sel]['performance'], $specific_metric['threshold_direction']);
                    }
                    echo "</div>\n";

                    echo '<div class="metric_def">';
                    echo $specific_metric['metric_def'];
                    echo "</div>\n";

                    echo "</div>\n";
                    $i++;
                    $k++;
                }
                echo "</div>\n";
                $row++;
            } ?>
        <?php        
        if (!($page+1 < ($metriccount/(2*$metrics_per_row)))){
            if (($contract['total_incentive_amount'] != 0) && ($contract['pay_cycle_id']==2)) {
                echo '<div class="incentive">Total Quality Incentive: '.curr_format($total_incentive).'</div>';
                $comp_report[] = array('provider_name'=>$provider['provider_name'],'badge_num'=>$provider['badge_num'],'incentive'=>$total_incentive, 'status'=>$quarter_status[$quarter_sel]);
            }
            if (($contract['total_incentive_amount'] != 0) && ($contract['pay_cycle_id']==3) && ($quarter_sel == 4)) {
                echo '<div class="incentive">Total Quality Incentive: '.curr_format($total_incentive).'</div>';
                $comp_report[] = array('provider_name'=>$provider['provider_name'],'badge_num'=>$provider['badge_num'],'incentive'=>$total_incentive, 'status'=>$quarter_status[$quarter_sel]);
            }
        } 
        ?>
        <div class="disclaimer">
        CONFIDENTIAL PEER REVIEW DOCUMENT<br>
        This document contains privileged and confidential information for exclusive use in the peer review and quality control functions of the Kettering Health Network and Kettering Physician Network. This information is legally protected by Ohio Revised Code Sections 2305.25, 2305.251 and 2305.252. Further review, dissemination, distribution or copying of this information is strictly prohibited.
        </div>
        <?php
        $page++;
        echo '</div>';
        if (!($page < ($metriccount/(2*$metrics_per_row))) && ($page%2==1)) {
            echo '<div class="page">&nbsp</div>';
        }
        }
        echo '</div>';
    }
    ?>
    </div>
    <div class="report">
    <table><tr><th>Provider</th><th>Badge #</th><th>Incentive</th><th>Status</th></tr>
    <?php
    foreach ($comp_report as $report_row) {
        echo '<tr>';
        echo '<td>'.$report_row['provider_name'].'</td><td>'.$report_row['badge_num'].'</td><td>'.curr_format($report_row['incentive']).'</td><td>'.$report_row['status'].'</td>';
        echo "</tr>\n";
    }
    ?>
    </table>
    </div>
</body>
</html>