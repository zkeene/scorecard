<div class="selector">
    <?php
    if (isset($_GET['sl'])) {
        $service_line_id = htmlspecialchars($_GET['sl']);
    } else {
        $service_line_id = 1;
    }
    if (isset($_GET['year'])) {
        $year_sel = htmlspecialchars($_GET['year']);
    } else {
        $year_sel = date('Y');
    }
    if (isset($_GET['quarter'])) {
        $quarter_sel = htmlspecialchars($_GET['quarter']);
    } else {
        $quarter_sel = 1;
    }
    ?>
    <form action="index.php" method="get">
        <select name="sl" id="sl">
            <?php
                $service_lines = getServiceLines();
                foreach ($service_lines as $service_line) {
                    if ($service_line['id'] == $service_line_id) {
                        echo '<option value="'.$service_line['id'].'" selected>'.$service_line['service_line']."</option>\n";
                    } else {
                        echo '<option value="'.$service_line['id'].'">'.$service_line['service_line']."</option>\n";
                    }
                }
            ?>
        </select>
        <select name="year" id="year">
            <?php
                $years = getYears();
                foreach ($years as $year) {
                    if ($year == $year_sel) {
                        echo '<option value="'.$year.'" selected>'.$year."</option>\n";
                    } else {
                        echo '<option value="'.$year.'">'.$year."</option>\n";
                    }
                }
            ?>
        </select>
        <select name="quarter" id="quarter">
            <?php
            foreach ([1,2,3,4] as $qtr){
                if ($qtr == $quarter_sel) {
                    echo '<option value="'.$qtr.'" selected>'.$qtr."</option>\n";
                } else {
                    echo '<option value="'.$qtr.'">'.$qtr."</option>\n";
                }
            }
            ?>
            </select>
        <input type="submit">
    </form>
</div>