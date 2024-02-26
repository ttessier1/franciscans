/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

jQuery(document).ready(function($){
    $('#show-contents').click(function(){
        $('#admintools-file-contents').slideToggle("slow");
        return false;
    });

    $('#show-diff').click(function(){
        $('#admintools-diff-contents').slideToggle("slow");
        return false;
    });
});
