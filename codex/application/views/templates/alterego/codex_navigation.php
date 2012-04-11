<?php
        //If the user wants to automatically 
        //generate CRUDS for the tables, then get
        //the list of the tables not excluded
        //and pass them to the header view

        $auto_generated_links = array();
        $user_level   = $this->codexsession->userdata('user_level');
        if($this->config->item('codex_auto_generate_crud')){
            // по БД
            if($this->config->item('codex_auto_generate_menu') == 'db'){
                $tables = $this->db->list_tables();
                foreach($tables as $table){
                    if(!in_array($table,$this->config->item('codex_exclude_tables')))
                        $auto_generated_links[$table] = '?c=crud&amp;m=index&amp;t='.$table;
                }
            }
            // по yml файлам
            if($this->config->item('codex_auto_generate_menu') == 'files'){
                
                $path = 'codex/application/'.$this->config->item('codex_definitions_dir');
                $tables = array();
                
                $dh = opendir($path);
                while(gettype( $file = readdir($dh)) != @boolean){
                    
                    if($file == '..' || $file == '.')
                        continue;
                    
                    if( is_file($path . $file) && $file != '')
                        if(strstr($file,'.yml'))
                            $tables[] = str_replace('.yml','',$file);
                }   
                @closedir($dh);
                
                foreach($tables as $table){
                    if(!in_array($table,$this->config->item('codex_exclude_tables'))){
                        
                        $table_config = $this->spyc->YAMLLOAD($this->codexadmin->getDefinitionFileName($table));
                        
                        $access_level = 0;
                        
                        if(isset($table_config['access_level']))
                            $access_level = intval($table_config['access_level']);
                        
                        if(!$this->codexmodel->check_access('?c=crud&amp;m=index&amp;t='.$table))
                            if($user_level != 3 && ($user_level < $access_level || $access_level == 0))
                                continue;
                        
                        $table_name = $table;
                        $groups = 'main';
                        
                        if(isset($table_config['page_header']))
                            $table_name = $table_config['page_header'];
                        if(isset($table_config['groups']))
                            $groups = $table_config['groups'];
                        
                        if(!empty($table_config['db_table']))
                            $table = $table_config['db_table'];
                        $auto_generated_links[$table_name] = array('key'=>$table_name, 'groups'=>$groups,'link'=>'?c=crud&amp;m=index&amp;t='.$table);
                    }
                }
            }
            $_access_level = $this->config->item('codex_navigation_access');
            foreach($this->config->item('codex_navigation') as $name=>$key){
                
                $access_level = 0;
                
                if(isset($_access_level[$name]))
                    $access_level = $_access_level[$name];
                    
                if(!$this->codexmodel->check_access($key['link']))
                    if($user_level != 3 && ($user_level < $access_level || $access_level == 0))
                        continue;
                
                $auto_generated_links[$name] = array('key'=>$name, 'groups'=>$key['groups'], 'link'=>$key['link']);
            }
            ksort($auto_generated_links);
            
            $_auto_generated_links = array();
            foreach($auto_generated_links as $k=>$v){
                foreach($auto_generated_links as $_k=>$_v){
                    if($v['groups'] == $_v['groups']){
                            $_auto_generated_links[$v['groups']][$_v['key']] = $_v;
                    }
                }
            }
            $auto_generated_links = $_auto_generated_links;
        }
?>



<div class="accordion" id="accordion-02" style="margin-right:20px">

            <?php 
            $active = 0;
            $i=-1;
            $temp = '';
            
            $main_url = explode('/',$_SERVER['REQUEST_URI']);
            if(empty($main_url[2]))
                $main_url = range(0,3);
            if(!empty($main_url[2]) && !empty($main_url[3]))
                $main_url[2] = $main_url[2].'/'.$main_url[3];
            
            //echo $this->page_header;
            
            foreach($auto_generated_links as $key=>$val){
                if(is_array($val)){
                    foreach($val as $_key=>$_val){
                        if($temp != $_val['groups']){
                            $i++;
                            echo '
                            <div class="accordion-group">
                                <div class="accordion-heading">
                                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-02" href="#collapse-'.$i.'">
                                        '.mb_ucfirst($_val['groups'],'UTF8').'
                                    </a>
                                </div>
                                <div id="collapse-'.$i.'" class="accordion-body collapse '.((strstr($_val['link'],$main_url[2])) || (strcmp(humanize($_val['key']),humanize($this->page_header)) == 0)?'in':'').'">
                                    <div class="accordion-inner" style="background-color:whiteSmoke;">
                                        <ul class="nav nav-list">';
                            $temp = $_val['groups'];
                        }
            ?>
                                        <?php echo '<li';
                                        if((strstr($_val['link'],$main_url[2])) || (strcmp(humanize($_val['key']),humanize($this->page_header)) == 0)){ echo ' class="active"';$active=$i;}
                                            echo '>'.codexAnchor($_val['link'],$_val['key'])."</li>\n"; 
                    }
                                        echo '</ul>
                                    </div>
                                </div>
                            </div>';
            ?>
            <?php
                }else{
                    echo '<h3><a href="#">Развернуть</a></h3>
                        <div>
                            <p';
                    if((strstr($val,$main_url[2])) || (strcmp(humanize($key),humanize($this->page_header)) == 0)){ echo ' id="active-page"';$active=$i;}
                    echo '>'.codexAnchor($val,$key)."</p></div>\n"; 
                      //echo '>'.codexAnchor($link,humanize($name))."</li>\n"; 
                }
            } 
            ?>




 
</div>


