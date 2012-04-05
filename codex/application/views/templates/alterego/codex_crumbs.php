<div id="codex-crumbs">
    <ul class="ul_style">
        <?php $i = 0; $first = true; $prev_selected = false; foreach($crumbs as $value=>$link):
            if(empty($link))
                $anchor = $value;
            else
                $anchor = codexAnchor($link,$value);

            if($value == $selected){
                $prev_selected = true;
                if(!$first)
                   echo '<li class="codex-crumbs-separator"></li>';
               echo '<li class="codex-crumbs-selected'; if($first) echo ' first'; echo '">'.$anchor."</li>\n"; 
            }

            else{
                if(!$first)
                   echo '<li class="codex-crumbs-separator"></li>';
               echo '<li'; if($first) echo ' class="first"'; echo '>'.$anchor."</li>\n"; 
            }

            $i++;

            if($first) 
                $first = false;

        endforeach; ?>
    </ul>
    <a class="preview" href="<?php echo base_url().'index.php'; ?>" target="_blank">Просмотр сайта</a>
</div>
