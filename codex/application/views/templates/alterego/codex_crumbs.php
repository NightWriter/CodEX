   <ul class="breadcrumb">
   <?php $i = 0; $first = true; $prev_selected = false; foreach($crumbs as $value=>$link):
            if(empty($link))
                $anchor = $value;
            else
                $anchor = codexAnchor($link,$value);

            if($value == $selected){
                $prev_selected = true;
                   echo '<li class="active">'.$anchor.'</li>';
            } else {
        echo '  <li>
    <a href="#">'.$anchor.'</a> <span class="divider">/</span>
  </li>';    }
         endforeach; ?>

  
</ul>
