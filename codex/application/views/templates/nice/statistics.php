<script language="JavaScript" src="<?=base_url()?>public/js/FusionCharts.js"></script>
<script language="JavaScript">
function drawChart(chartSWF, strXML, chartdiv) {
    var chart = new FusionCharts(chartSWF, "chart1Id", "750", "350", "0", "0"); 
        chart.setTransparent(true);
        chart.setDataXML(strXML);
        chart.render(chartdiv);
}
//
function change_date(field_num,method,chartdiv){
    var date_start  = $('#date_start'+field_num).val();
    var date_finish = $('#date_finish'+field_num).val();
    $.post('<?=site_url('/statistics')?>/'+method,{date_start:date_start,date_finish:date_finish}, function(data) {
        drawChart("<?=base_url()?>charts/FCF_MSLine.swf", data, chartdiv);
    });
}
//
$(document).ready(function(){
    $.post('<?=site_url('/statistics/getUsers/')?>', function(data) {
        drawChart("<?=base_url()?>charts/FCF_MSLine.swf", data, "chart1div");
    });
    $.post('<?=site_url('/statistics/getSales/')?>', function(data) {
        drawChart("<?=base_url()?>charts/FCF_MSLine.swf", data, "chart2div");
    });
    $.post('<?=site_url('/statistics/getProfit/')?>', function(data) {
        drawChart("<?=base_url()?>charts/FCF_MSLine.swf", data, "chart3div");
    });
});
<?php
$js ="$(document).ready(function() {
                $('#date_start1').datepicker({dateFormat: 'yy-mm-dd'});
                $('#date_start2').datepicker({dateFormat: 'yy-mm-dd'});
                $('#date_start3').datepicker({dateFormat: 'yy-mm-dd'});
                $('#date_finish1').datepicker({dateFormat: 'yy-mm-dd'});
                $('#date_finish2').datepicker({dateFormat: 'yy-mm-dd'});
                $('#date_finish3').datepicker({dateFormat: 'yy-mm-dd'});
              });";
$this->codextemplates->inlineJS('js-init',$js); 
?>
</script>
<div id="codex-table">
    <table id="main-table">
      <thead>
          <tr id="header-row">
            <th style="text-align:center">Поставщик</th>
            <th style="text-align:center">Бренды (кол-во)</th>
            <th style="text-align:center">До</th>
            <th style="text-align:center">После</th>
            <th style="text-align:center">Разница</th>
            <th style="text-align:center">Время начала</th>
            <th style="text-align:center">Время окончания</th>
            <th style="text-align:center">Время выполнения</th>
          </tr>
        <?if(!empty($stats_import)):?>
            <?foreach($stats_import as $key=>$row):?>
              <tr bgcolor="<?=$row['bgcolor']?>">
                <th><?=$row['name']?></th>
                <th style="text-align:center">
                    <div style="overflow:scroll;text-align:right;width:350px;height:200px;position:absolute;display:none;background-color:#f8f8f8" id="<?=$row['key']?>">
                        <a href="#" onClick="$('#<?=$row['key']?>').hide()">Закрыть</a>
                        <div style="width:100%;text-align:left;">
                            <table>
                              <tr>
                                <?php
                                $i=0;
                                    foreach($row['brands'] as $item)
                                    {
                                        $i++;
                                        echo '<th '.(($i==2)?'style="text-align:right"':'').'>'.$item->title.'  ('.$item->cnt.')</th>';
                                        if($i == 2)
                                        {
                                            $i = 0;
                                            echo '</tr><tr>';
                                        }
                                    }
                                ?>
                              </tr>
                            </table>
                        </div>
                    </div>
                    <a href="#" onClick="$('#<?=$row['key']?>').show()">Показать</a>
                </th>
                <th style="text-align:center"><?=$row['before']?></th>
                <th style="text-align:center"><?=$row['after']?></th>
                <th style="text-align:center"><?=$row['difference']?></th>
                <th style="text-align:center"><?=$row['start_time']?></th>
                <th style="text-align:center"><?=$row['end_time']?></th>
                <th style="text-align:center"><?=$row['difference_date']?></th>
              </tr>
              <?endforeach?>
            <?endif?>
      </thead>
    </table>
</div>
<br />
<form method="post">
    <div id="statistic">
        <div id="template-chooser">
            От: <input type="text" id="date_start1">
            До: <input type="text" id="date_finish1">
            <input type="button" value="Применить" onclick="change_date(1,'getUsers','chart1div')">
        </div>
    </div>

    <div id="chart1div" align="left"></div>
        
    <div id="statistic">
        <div id="template-chooser">
            От: <input type="text" id="date_start2">
            До: <input type="text" id="date_finish2">
            <input type="button" value="Применить" onclick="change_date(2,'getSales','chart2div')">
        </div>
    </div>

    <div id="chart2div" align="left"></div>

    <div id="statistic">
        <div id="template-chooser">
            От: <input type="text" id="date_start3">
            До: <input type="text" id="date_finish3">
            <input type="button" value="Применить" onclick="change_date(3,'getProfit','chart3div')">
        </div>
    </div>

    <div id="chart3div" align="left"></div>
</form>
