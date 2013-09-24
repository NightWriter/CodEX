<?php
if(!function_exists('message_output'))
{
    function message_output($type,$message)
    {
        $output = '';
        switch($type){
            case 'success':
                $prefix = '<div class="alert alert-success">';
                $suffix = '</div>';
            break;
            case 'info':
                $prefix = '<div class="alert alert-info">';
                $suffix = '</div>';
            break;
            case 'failure':
                $prefix = '<div class="alert alert-error">';
                $suffix = '</div>';
            break;
            default:
                $prefix = '<div class="alert alert-info">';
                $suffix = '</div>';
            break;
        }
        if(is_array($message)){
            foreach($message as $m){
                $output .= $prefix.$m.$suffix."\n";
            }
        }
        else{
            $output .= $prefix.$message.$suffix."\n";
        }
        return $output;
    }
}
?>
            <div id="messages"> 

                <?php
                if(!empty($messages))
                    if(count($messages) > 0){
                        foreach($messages as $type=>$message){
                            echo message_output($type,$message);
                        }
                    }

                ?> 
            </div>

