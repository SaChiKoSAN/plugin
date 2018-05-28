<?php
/**
 * @package Additional Images
 * @version 1.0.0
 */
/*
Plugin Name: Additional Images
Plugin URI: 
Description: 画像のサイズを追加します。
Version: 1.0.0
*/

add_action('admin_menu', 'additional_images');



function additional_images_data(){

	$data = get_option("additional_images");

	//データベースが空だった場合、デフォルト値をセットする
	if(!is_array($data)){
		$data[1] = array("suffix" => "k_thumb" , "width" => "96", "height" => "96" , "crop" => "checked", "quality" => "");
		$data[2] = array("suffix" => "k_200" , "width" => "200", "height" => "" , "crop" => "", "quality" => "");
		update_option("additional_images",$data);
	}
	return $data;
}


function additional_images(){
	add_options_page("追加画像の管理", "追加画像の管理", 'edit_users', "additional_images", "additional_images_admin");
}


function additional_images_admin(){

	$data = additional_images_data();
?>

<?php if($_REQUEST['update']): ?>

<?php		
	foreach ($_REQUEST as $key => $value){
		//フォームの名前を _ で分割する
		unset($keys);
		$keys = explode("_", $key);
		if($keys[1]):
			$name = $keys[0];
			$no = $keys[1];
			//チェックボックスの場合、「on」で受けた値を「checked」に変更しておく
			$value = $name=="crop" && $value=="on" ? "checked" : $value;
			$reqs[$no][$name] = $value;
		endif;
	}

	//検査の開始（値を取り出した後の方が楽）
	if(!is_array($reqs)) $reqs[]=$reqs;
	foreach($reqs as $req):
		$i++;
		if($req['suffix']){
		//suffixが入力された場合
			if(!$req['width']) $err_msg .= "widthは入力必須です。<br>\n";
			if(!preg_match("/^[0-9]+$/", $req['width'])) $err_msg .= "widthに数値以外の文字が入力されています。<br>\n";
			if(($req['height'])&&(!preg_match("/^[0-9]+$/", $req['height']))) $err_msg .= "heightに数値以外の文字が入力されています。<br>\n";
			if(($req['quality'])&&(!preg_match("/^[0-9]+$/", $req['quality']))) $err_msg .= "qualityに数値以外の文字が入力されています。<br>\n";
			//suffixに追加、変更があった場合は画像キャッシュを削除する
			if($req['suffix'] != $data[$i]['suffix']) $delsfxs[] = $req['suffix'];
			if($req['width'] != $data[$i]['width']) $delsfxs[] = $req['suffix'];
			if($req['heigh'] != $data[$i]['heigh']) $delsfxs[] = $req['suffix'];
			if($req['crop'] != $data[$i]['crop']) $delsfxs[] = $req['suffix'];
			if($req['quality'] != $data[$i]['quality']) $delsfxs[] = $req['suffix'];
		}else{
		//suffixが未入力の場合
			//width、heighまたはqualityが入力された場合
			if($req['width'] || $req['height'] || $req['quality']) $err_msg .= "suffixは入力必須です。<br>\n";
		}
	endforeach;unset($i);

	if($err_msg):
		$msg = "データベースは更新されませんでした。";
	else:
		//空の配列を削除
		if(!is_array($reqs)){$reqs[] = $reqs;}
		foreach($reqs as $req):
			if ($req["suffix"] || $req["width"] || $req["height"] || $req["crop"] || $req["quality"]){
				$i++;
				$result[$i] = $req;
			}
		endforeach;unset($i);
		update_option( "additional_images", $result);
		$msg = "データベースが更新されました。";


		if($delsfxs){
			//配列要素の重複削除
			$delsfxs = array_unique($delsfxs);

			//wpdb宣言
			global $wpdb;
			//キャッシュファイルの削除
			//全attachmentの取得（一瞬）
			$all_att = $wpdb->get_results("SELECT ID, post_parent FROM $wpdb->posts WHERE post_type = 'attachment'");
			foreach( (array) $all_att as $att){
				if(wp_attachment_is_image($att->ID)){
				//メディアが画像の場合
					//画像ファイルの付加情報を取得
					$meta = wp_get_attachment_metadata($att->ID);
					$imagepath = pathinfo($meta['file']);
					$_extension = '.'.$imagepath['extension'];
					$subdir = $imagepath['dirname'];
					foreach($delsfxs as $delsfx){
						//消去対象のファイル名を定義
						$delimg = preg_replace("/".$_extension."$/","",$imagepath['basename'])."-".$delsfx.$_extension;
						//削除
						$upload = wp_upload_dir();
						$path = $upload['basedir'];
						$unlink = $path.'/'.$subdir.'/'.$delimg;
						if (file_exists($unlink)){
							unlink($unlink);
						}
					}
				}
			}
		//削除対象がある場合の終わり
		}
	endif;
?>
<?php endif ?>

<?php // この時点でデータベースを再取得する。// ?>
<?php $data = additional_images_data() ?>

<style>
.addtable tr{height:32px}
</style>

<script type="text/javascript">
/* appendで要素を追加する */
function addTr(){
        n = jQuery('.addtable tr').size();
        document.getElementById('count').value = n;
	jQuery('.addtable').append('<tr><td>' + n + '</td><td><input type="text" name="suffix_' + n + '" value="" size="10" /></td><td><input type="text" name="width_' + n + '" value="" size="10" /></td><td><input type="text" name="height_' + n + '" value="" size="10" /></td><td width="50" align="center"><input type="checkbox" name="crop_' + n + '" /></td><td><input type="text" name="quality_' + n + '" value="" size="1" /></td></tr>');
}
</script>

<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2>追加画像の管理</h2>

<?php if ( $err_msg) : ?>
<div id="notice" class="error"><p><?php echo $err_msg ?></p></div>
<?php endif; ?>
<?php if ( $msg ) : ?>
<div id="message" class="updated"><p><?php echo $msg; ?></p></div>
<?php endif; ?>

<?php //フォームの呼び出し先（アクション）は自分自身 ?>
<form name="additional_image_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<?php //updateに値を入れて、次に自分を呼び出したときに初回呼び出しではないことを自分自身にアピールする ?>
<input type="hidden" name="update" value="true">
<h4>追加画像</h4>
<div style="float:left;margin:0 20px">
<table class="addtable" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="30">No.</td>
		<td>接尾辞</td>
		<td>横幅</td>
		<td>縦幅</td>
		<td width="50" align="center">ﾄﾘﾐﾝｸﾞ</td>
		<td>品質</td>
		<td width="50"></td>
	</tr>
<?php foreach( $data as $value){ ?>
	<?php $i++ ?>
	<tr>
		<td><?php echo $i ?></td>
		<td><input type="text" name="suffix_<?php echo $i ?>" value="<?php echo $value['suffix'] ?>" size="10" /></td>
		<td><input type="text" name="width_<?php echo $i ?>" value="<?php echo $value['width'] ?>" size="10" /></td>
		<td><input type="text" name="height_<?php echo $i ?>" value="<?php echo $value['height'] ?>" size="10" /></td>
		<td width="50" align="center"><input type="checkbox" name="crop_<?php echo $i ?>" <?php echo $value['crop'] ?> /></td>
		<td><input type="text" name="quality_<?php echo $i ?>" value="<?php echo $value['quality'] ?>" size="1" /></td>
	</tr>
<?php } ?>
</table>

<p class="hidden">count:<input type="text" name="count" id="count" value="" /></p>

<script type="text/javascript">document.getElementById('count').value=jQuery('.addtable tr').size();</script>
<input style="margin:0 -15px" type="button" class="button" name="add" value="追加"  onclick="addTr();" />
<p style="text-align:right;padding:0 50px 0 0"><input class="button-primary" type="submit" name="Submit" value="設定を保存" /></p>
</form>
</div>
<br style="clear:both">
</div>

<?php
}
?>
