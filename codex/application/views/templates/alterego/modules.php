<div id="codex-table">

    <table id="main-table" class="table table-bordered">
        <thead>
            <tr id="header-row">
                <th style="white-space:nowrap" id="">Alias</th>
                <th style="white-space:nowrap" id="">Name</th>
                <th style="white-space:nowrap" id="">Description</th>
                <th style="white-space:nowrap" id="">Access to write</th>
                <th style="white-space:nowrap" id="">Status</th>
            </tr>
        </thead>
        <tbody>
            <? if(!empty($rows)): ?>
                <? foreach($rows as $row): ?>
                    <tr id="header-row">
                        <td style="white-space:nowrap"><?=$row->alias?></td>
                        <td style="white-space:nowrap"><?=$row->title?></td>
                        <td style="white-space:nowrap"><?=$row->description?></td>
                        <td style="white-space:nowrap"><?=$row->access?></td>
                        <td style="white-space:nowrap">
                            <? if(!$row->status): ?>
                                Disabled <a href="<?=site_url('modules/install/'.$row->alias)?>">Install module</a>
                            <? elseif(!empty($module_install_list[$row->alias])): ?>
                                Date instaled <?=date('Y-m-d H:i:s',$module_install_list[$row->alias])?>
                            <? endif; ?>
                        </td>
                    </tr>
                <? endforeach; ?>
            <? endif; ?>
        </tbody>
        <? if(!empty($pagination)): ?>
        <tfoot>
            <tr>    
                <td colspan="5">
                    <?=$pagination?>
                </td>
            </tr>
        </tfoot>
        <? endif; ?>
    </table>
</div>