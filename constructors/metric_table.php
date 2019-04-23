<div class="metric_table">
    <table class="metric_table"><tr><td>Q1</td><td>Q2</td><td>Q3</td><td>Q4</td></tr>
        <?php
        if (!$specificmetrics[$i]['is_tbd_metric']) {
            if ($specificmetrics[$i]['is_calculated_metric']) {
                echo '<tr>';
                for ($j=1; $j <= 4; $j++) {
                    if (($j<count($metric_perf)+1) && ($j <=$quarter_sel)) {
                        if (isset($metric_perf[$j]['performance']) && ($metric_perf[$j]['denominator']!=0)) {
                            echo '<td>'.$metric_perf[$j]['performance'].'</td>';
                        } elseif (isset($metric_perf[$j]['denominator']) && ($metric_perf[$j]['denominator']==0)){
                            echo '<td>(0/0)</td>';
                        }else {
                            echo '<td>No Data</td>';
                        }
                    } else {
                        echo '<td></td>';
                    }
                }
                echo '</tr>';
            } else {
                echo '<tr>';
                for ($j=1; $j <= 4; $j++) {
                    if (($j<count($metric_perf)+1) && ($j <=$quarter_sel)) {
                        if (isset($metric_perf[$j]['performance']) && ($metric_perf[$j]['denominator']!=0)) {
                            echo '<td>'.$metric_perf[$j]['performance'].'% ('.($metric_perf[$j]['numerator']+0).'/'.$metric_perf[$j]['denominator'].')</td>';
                        } elseif (isset($metric_perf[$j]['denominator']) && ($metric_perf[$j]['denominator']==0)) {
                            echo '<td>Zero</td>';
                        } else {
                            echo '<td>No Data</td>';
                        }
                    } else {
                        echo '<td></td>';
                    }
                }
                echo '</tr>';
            }
        }
        if (($contract['incentive'] != 0) && (!$specificmetrics[$i]['is_beta_metric'])) {
            echo '<tr>';
            for ($j=1; $j <= 4; $j++) {
                if (($j<count($metric_perf)+1) && ($j <=$quarter_sel)) {
                    echo '<td>'.curr_format($inc_array[$j]).' ('.number_format($percent_incentive[$j], 0).'%)</td>';
                } else {
                    echo '<td></td>';
                }
            }
            echo '</tr>';
        }
        ?>
    </table>
</div>
