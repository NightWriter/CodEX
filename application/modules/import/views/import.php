<table>
<?php
if(!empty($list))
{
    foreach($list as $row)
    {
        echo '<tr>';
        $id = 0;
        foreach($row as $k=>$v)
        {
            if($k == 'id') $id = $v;
            ?>
                <td><?=$v?></td>
            <?
        }
        echo '<td><a href="'.site_url('import/get/'.$id).'">Перейти</a></td>';
        echo '</tr>';
    }
}
echo '</table>';
if(!empty($pagination))
{
    echo '<hr>';
    echo $pagination;
}
?>