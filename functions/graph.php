<?php

function scaleValue ($value, $barheight, $minv, $maxv) {
    $offset = $maxv * .10;
    $bottom = (($minv - $offset) > 0) ? ($minv - $offset) : 0;
    $top = (($maxv + $offset) < 100) ? ($maxv + $offset) : 100;
    return $barheight * (($value - $bottom)/($top-$bottom));
}

function createGraph($p, $d, $c, $q)
{

    $thresharr = array();
    foreach ($c as $threshold => $color){
        if (($d==0 && $threshold!=0) || ($d==1 && $threshold!=100)) {
            $thresharr[] = array($threshold,$color);
        }
    }

    $width = 280;
    $height = 200;
    $fontsize = 10;
    $barpercent = .7;
    $topmargin = 15;

    $valarray = array_merge(array_filter($p),array_map('array_shift',$thresharr));
    $maxv = max($valarray);
    $minv = min($valarray);

    $barheight = $height-$topmargin;

    $spacepercent = (1-$barpercent)/4;
    //$width -15 to give space for the threshold box
    $barwidth = ($width-15)*($barpercent/4);
    $spacewidth = ($width-15)*$spacepercent;

    echo '<svg width="'.$width.'" height="'.$height.'">';
    echo '<line x1="0" x2="'.$width.'" y1="'.($barheight+1).'" y2="'.($barheight+1).'" style="stroke:black;stroke-width:2"/>';
    $i = 0;
    foreach ($p as $key => $val) {
        $xcoord = ($i*$barwidth)+($i*$spacewidth)+($spacewidth/3);
        if (($key <= $q) && (!is_null($val))) {
            $perf_color = getCorrectThresholdValue($c, $val, $d);
            $scaledvalue = scaleValue($val, $barheight, $minv, $maxv);
            echo '<rect width="'.$barwidth.'" height="'.$scaledvalue.'" x="'.$xcoord.'" y="'.($barheight-$scaledvalue).'" style="fill:'.$perf_color.'"/>';
            echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-20).'" font-size="'.$fontsize.'" font-weight="bold">'.$val.'</text>';
        }
        echo '<text x="'.($xcoord+($barwidth*.4)).'" y="'.($height-4).'" font-size="'.$fontsize.'" font-weight="bold">Q'.($i+1).'</text>';
        $i++;
    }

    foreach ($thresharr as $thresh) {
        $ypos = $barheight-scaleValue($thresh[0],$barheight,$minv,$maxv);
        echo '<line x1="0" x2="'.$width.'" y1="'.$ypos.'" y2="'.$ypos.'" style="stroke:'.$thresh[1].';stroke-width:2"/>';
        echo '<rect width="20" height="'.($fontsize+2).'" x="'.($width-20).'" y="'.($ypos).'" style="fill:'.$thresh[1].';"/>';
        echo '<text x="'.($width-20).'" y="'.($ypos + 10).'" font-size="'.$fontsize.'" font-weight="bold" fill="#000000">'.($thresh[0]+0).'</text>';
    }

    echo '</svg>';
}