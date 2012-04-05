<?php 
$j=0;foreach($entries as $entry): 
    if($j % 2 == 0)                 
        echo '<tr class="even">';
    else
        echo '<tr class="odd">';
?>
        <td class="first"><input class="edit-button" type="checkbox" value="<?php echo $entry[$this->codexadmin->primary_key]?>" name="selected_rows[]"/></td>
           <?php $i = 0; foreach($this->codexadmin->display_fields as $field=>$foo){
                   echo '<td align="center">';
                       if($i == 0){
                            $anchor = $entry[$field];

                            if(empty($anchor))
                                $anchor = 'Click to edit';

                            echo codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->edit_link),$anchor,array('title'=>'')).'</td>';
                            $i++; 
                       }
                       else
                           echo $entry[$field],'</td>'."\n";
                  } 
            ?>

    </tr>
<?php $j++; endforeach; ?>