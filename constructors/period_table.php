<div class="metric_table">
    <table class="metric_table">
        <tr><td colspan="2">Period</td></tr>
        <tr><td>P1</td><td>P2</td></tr>
        <tr>
        <?php
        $index = 1;
        while ($index < 3) {
            if (isset($period_metric_perf[$index]['performance'])) {
                echo '<td>'.$period_metric_perf[$index]['performance'].
                '%'.'</td>';
            } else {
                echo '<td></td>';
            }
            $index++;
        }
        ?>
        </tr>
    </table>
</div>
