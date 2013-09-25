<div class="blogs">
    <div class="blogs-title"> <?=$article->title?></div>
    
    <div class="blog-detail-block-pre"></div>
        <div class="blog-detail-block pied">
    

        <div class="blog-detail-block-stat"> добавлено <?=date('d.m.Y H:i',$article->date_cr)?>  </div>

            <div class="blog-detail-block-category">
               
               &nbsp; 
                
            </div>
        </div>

        <? if(!empty($article->preview)): ?>
            <div class="blog-detail-block-title">
                <img width="640" src="<?= thumb_path('uploads/static_pages/'.$article->id,$article->preview,640,480)?>" >
            </div>
        <? endif; ?>
        
        <div class="blog-detail-block-text">
            <p><?=$article->text?></p>
        </div>

    </div>
    
</div>