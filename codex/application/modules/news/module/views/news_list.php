<div class="blogs">
    <div class="blogs-title"><?=$category->title?></div>
    
    <div class="clear"></div>
    
    
    <!-- Список постов: BEGIN -->
    
    <div class="posts-list inner-list">
        
        <?php
        if(!empty($news)):
             foreach($news as $row): ?>
                <!-- Пост: BEGIN -->
                <div class="posts-list-item"><div class="posts-list-item-wrapper">
                    
                    <? if(!empty($row->preview)): ?>
                        <a class="posts-list-item-cover pied" href="<?=site_url('news/show/'.$category->alias.'/'.$row->alias)?>.html">
                            <img src="<?= thumb_path('uploads/news/'.$row->id,$row->preview,111,87)?>" class="pied" alt="">
                        </a>
                    <? endif; ?>
                    
                    <div class="posts-list-item-content">
                        <div class="posts-list-item-content-from"> <?=date('d.m.Y H:i',$row->date_cr)?></div>     
                        <div class="posts-list-item-content-category">
                            
                            <a href="<?=site_url('news/index/'.$category->alias)?>.html"><?=$category->title?></a>
                            
                        </div> 
                        <div class="clear"></div>
                        
                        
                        <a href="<?=site_url('news/show/'.$category->alias.'/'.$row->alias)?>.html" class="posts-list-item-content-title"><?=$row->title?></a>
                        
                        <div class="posts-list-item-content-text"><?=str_crop(strip_tags($row->text),200)?></div>
                        
                        
                    </div>
                </div></div>
                <!-- Пост: END -->
                
            <? endforeach; ?>
        
        <? endif; ?>
        
        
        <? if(!empty($pagination)): ?>
            <div class="pagination">
                
                <?=$pagination?>

            </div> 
        <? endif; ?>

    </div>
    <!-- Список постов: END -->

</div>