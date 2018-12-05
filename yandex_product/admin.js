// The "Upload" button
jQuery(document).ready(function($){   

$('.upload_image_link').click(function() {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    wp.media.editor.send.attachment = function(props, attachment) {
        $(button).parent().parent().prepend('<img src="'+attachment.url+'" width="115px" height="115px" />');
        $(button).prev().val(attachment.id);        
        wp.media.editor.send.attachment = send_attachment_bkp;
    }
    wp.media.editor.open(button);
    return false;
});

// The "Remove" button (remove the value from input type='hidden')
$('.remove_image_link').click(function() {
    var answer = confirm('Are you sure?');
    if (answer == true) {        
        $(this).parent().prev().remove();
        $(this).prev().val('remove_img');
        //$(this).prev().prev().val('');
    }
    return false;
});

});