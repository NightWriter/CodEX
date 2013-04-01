<?php 
$j=0;foreach($entries as $entry): 
    if($j % 2 == 0)                 
        echo '<tr class="even">';
    else
        echo '<tr class="odd">';
?>
        <td class="first"><input class="edit-button" type="checkbox" value="<?php echo $entry[$this->codexadmin->primary_key]?>" name="selected_rows[]"/></td>
           <?php $i = 0; 
               foreach($this->codexadmin->display_fields as $field=>$foo)
               {
                    if(!empty($this->codexforms->objects[$field]->params['not_display_in_list'])){
                            continue;
                    }
                   echo '<td align="center"  onClick="document.location=\''.site_url(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->edit_link)).'\'">';
                       if($i == 0){
                            $anchor = $entry[$field];
                            
                            if(empty($anchor) && $anchor !=0)
                                $anchor = $this->lang->line('codex_click_to_edit');

                            echo codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->edit_link),$anchor,array('title'=>'')).'</td>';
                            $i++; 
                       }
                       else
                           echo $entry[$field],'</td>',"\n";
                  }
                  
                  if(!empty($this->user_link))
                  {
                    if(!empty($this->user_link['title']))
                    {
                        if(isset($entry[$this->user_link['title']]))
                            $title = $entry[$this->user_link['title']];
                          else
                            $title = $this->user_link['title'];
                        
                        $link = str_replace('__','&',$this->user_link['link']);
                            
                        echo '<td>'.codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$link),$title,array('title'=>$title)).'</td>'."\n"; 
                    }else{
                        foreach($this->user_link as $link){
                              
                              if(isset($entry[$link['title']]))
                                $title = $entry[$link['title']];
                              else
                                $title = $link['title'];
                            
                            $link = str_replace('__','&',$link['link']);
                                
                            echo '<td>'.codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$link),$title,array('title'=>$title)).'</td>'."\n"; 
                          }
                    }
                  } 
            ?>

    </tr>
<?php $j++; endforeach; ?>