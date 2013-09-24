<table>
<?php
if(!empty($row))
{
    foreach($row as $k=>$v)
    {
        ?>
            <tr>
                <td><?=$k?></td>
                <td><?=$v?></td>
            </tr>
        <?
    }
}
echo '</table>';