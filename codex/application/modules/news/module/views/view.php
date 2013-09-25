<div class="blogs">
    <div class="blogs-title"><?=$category->title?> - <?=$news->title?></div>
    
    <div class="blog-detail-block-pre"></div>
        <div class="blog-detail-block pied">
    

        <div class="blog-detail-block-stat"> добавлено <?=date('d.m.Y H:i',$news->date_cr)?> </div>

            <div class="blog-detail-block-category">
                
                <a href="<?=site_url('news/index/'.$category->alias)?>.html"><?=$category->title?></a>
                
            </div>
        </div>

        <? if(!empty($news->preview)): ?>
            <div class="blog-detail-block-title">
                <img width="640" src="<?= thumb_path('uploads/news/'.$news->id,$news->preview,640,480)?>" ><br>
                <?=$news->title?>
            </div>
        <? endif; ?>
        
        <div class="blog-detail-block-text">
            <p><?=$news->text?></p>
        </div>

    </div>
</div>