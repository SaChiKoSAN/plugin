<?php
/**
 * @package Notice To Fill Out Form
 * @version 1.0.0
 */
/*
Plugin Name: Notice To Fill Out Form
Plugin URI: 
Description: このプラグインが有効にされると、投稿時にタイトルが未入力、カテゴリが未選択の場合、警告文が表示されます。
Version: 1.0.0
*/

//javascriptsの読み込み
add_action('admin_footer', 'notice_to_fill_out_form');

function notice_to_fill_out_form(){
	if(get_post($_REQUEST[post])->post_type == "post" ):
?>
<script type="text/javascript">
<?php if($_SERVER[SCRIPT_URL] == "/wp-admin/post-new.php"): ?>
	document.getElementById("save-post").onclick = NoticeToFillOutForm;
<?php endif ?>
	document.getElementById("publish").onclick = NoticeToFillOutForm;
	document.getElementById("post-preview").onclick = NoticeToFillOutForm;
	
	function NoticeToFillOutForm(){

		if(!document.post.title.value){
			alert('タイトルを入力してください');
			return false;
		}

		var category = document.getElementsByName( 'post_category[]' );
		var length = category.length;
		var flag = 0;
		for ( var i = 0; i < length; i++ ) {
			if( category[i].checked == true ) {
				flag++;
			}
		}
		if( flag == 0 ){
				alert('カテゴリを選択してください');
				return false;
		}else if( flag == 1){
				var categoryName;
				(function($){jQuery(document).ready( function() {
					categoryName = $.trim( $('#category-1').text() );
				} );})(jQuery);
				if ( ( document.getElementById( 'in-category-1' ).checked == true ) && ( categoryName == '未分類' ) ) {
					alert('未分類以外のカテゴリを選択してください');
				}
				return false;
		}
		return;
	}
</script>
<?php
	endif;
}
