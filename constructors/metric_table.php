<div class="metric_table">
    <table class="metric_table"><tr><td>Q1</td><td>Q2</td><td>Q3</td><td>Q4</td></tr>
        <tr>
        <?php
        if ($specificmetrics[$i]['is_calculated_metric']) {
            for ($j=1; $j <= 4; $j++) {
                if (($j<count($metric_perf)+1) && (isset($metric_perf[$j]['performance'])) && ($j <=$quarter_sel)) {
                    if (isset($metric_perf[$j]['performance'])) {
                        echo '<td>'.$metric_perf[$j]['performance'].'</td>';
                    } else {
                        echo '<td>No Data</td>';
                    }
                } else {
                    echo '<td></td>';
                }
            }
        } else {
            for ($j=1; $j <= 4; $j++) {
                if (($j<count($metric_perf)+1) && ($j <=$quarter_sel)) {
                    if (isset($metric_perf[$j]['performance'])) {
                        echo '<td>'.$metric_perf[$j]['performance'].'% ('.($metric_perf[$j]['numerator']+0).'/'.$metric_perf[$j]['denominator'].')</td>';
                    } else {
                        echo '<td>No Data</td>';
                    }
                } else {
                    echo '<td></td>';
                }
            }
        }
        if (($contract['incentive'] != 0) && (!$specificmetrics[$i]['is_beta_metric'])) {
            echo '</tr><tr>';
            for ($j=1; $j <= 4; $j++) {
                if (($j<count($metric_perf)+1) && ($j <=$quarter_sel)) {
                    echo '<td>'.curr_format($inc_array[$j]).' ('.number_format($percent_incentive[$j], 0).'%)</td>';
                } else {
                    echo '<td></td>';
                }
            }
        }
        ?>
        </tr>
    </table>
</div>
