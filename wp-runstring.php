<?php
/**
 * Plugin Name: My develop runstring
 * Description: Add a run string before content page.
 * Version: 1.0
 * Author: lubomyrivna26
 * Author URI: http://wordpr.gardens.biz.ua
 */
 
register_activation_hook( __FILE__, 'lb_runstring_install' ); 
function wp_runstring_install(){
		global $wpdb;
	$table_name = $wpdb->prefix . 'run_string';
	$sql = "CREATE TABLE $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			strcont text NOT NULL default '',
			priory int(11) unsigned NOT NULL default '2',
			publ int(2) unsigned NOT NULL default '1',
			UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET $wpdb->charset COLLATE $wpdb->collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	}

register_deactivation_hook( __FILE__, 'drop_plugin_runstring_table');
function drop_plugin_runstring_table()
{
	//drop a custom db table
	global $wpdb;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'run_string' );
}
add_filter( 'wp_head', 'lb_sign_content' );
function lb_sign_content(){	
    if(get_option('lb_enabled') == 'on')
	{
		global $wpdb;
		$table_name = $wpdb->prefix .'run_string';	$lb_strrun = '';
		$lb_symv = get_option('lb_symvol_discriminant');
		$res = $wpdb->get_col($wpdb->prepare("SELECT strcont FROM $table_name WHERE publ = %d ORDER BY priory DESC", 1));
		foreach($res as $row)
		{
			$lb_strrun .= $row.' '.$lb_symv.' ';
			}
			$lb_sign = "<div style='width:100%; position: relative; top:0;'>
			<marquee><font style='font-size:20pt; font-bold: 25px segoe print; color: #3cad00; width: 100%;'>".
			$lb_strrun."</font></marquee></div>";
			echo $lb_sign;
	}
}
add_action('admin_menu', 'lb_runstring_admin_pages');
function lb_runstring_admin_pages()
{
	add_options_page('Run string', 'Add run string', 8, 'runstring', 'lb_options_runstrinpage');
}
function lb_options_runstrinpage()
{
	echo '<h2>Add run string for site wordpress</h2>';
	add_option('lb_symvol_discriminant', ' @ ');
	add_option('lb_enabled', 'on');
	echo '<h3>Basic settings</h3>';
	lb_form_generate();
	echo '<h3>Create new string for add</h3>';
	lb_form_string_generate();
	echo '<h3>List string to add</h3>';
	lb_list_string();
}
function lb_list_string()
{
	global $wpdb;
	$table_name = $wpdb->prefix .'run_string';
	if(isset($_POST['save_change_btn']))
	{
		$arrid = [];
		$col = count($_POST)-1;
		for($t=1; $t<=$col; $t++)
		{			
			if(empty($_POST['name'.$t]))
			{
				break;
			}
			$arrid[] = $_POST['name'.$t];
		}
		foreach($arrid as $id)
		{
			$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id= %d", $id));
			if($_POST['delete'.$id] == 'on')
			{
				$wpdb->delete($table_name, array('id' => $id));
			} else {
				$lb_textrunstring = $_POST['textstring'.$id];
				$lb_priory = $_POST['priory'.$id];
				$lb_publication = $_POST['publ'.$id];
				$wpdb->update($table_name,
				array('strcont' => $lb_textrunstring, 'priory' => $lb_priory, 'publ' => $lb_publication),
				array('id' => $id)
				);
			}
		}
	}
	$result = $wpdb->get_results("SELECT * FROM $table_name");
	echo "<table style='max-width:100%; border: 1px #222 solid; margin-right: 20px;'>";?>
	<style>
	td{
		padding: 5px;
	}</style>
	<thead style="background: #C6DF90"><td>Text</td><td>Priorytet</td><td>Publication</td><td>Delete</td>
	</thead>
	<?php echo "<form method='post' name='lb_edit_delete_runstring' action='".$_SERVER['PHP_SELF']."?page=runstring&amp;updated=true'>";
	$item =1;
	foreach($result as $row)
	{
		echo "<tr><td><input type='text' name='textstring".$row->id."' value='".$row->strcont."' size ='250'></td>
		<input type='hidden' name='name".$item."' value='".$row->id."'>
		<td><select name='priory".$row->id."' >";
		$item++;
		for($k=1; $k<=10; $k++)
		{
			if($k == $row->priory)
			{
				echo "<option value='".$k."' selected>".$k."</option>";
			} else {
				echo "<option value='".$k."'>".$k."</option>";
			}
		}
		echo "</select></td><td><select name='publ".$row->id."'>";
		
		if($row->publ == 1)
		{
			echo "<option value='1' selected>Yes</option><option value='2'>No</option>";
		} else {
			echo "<option value='1'>Yes</option><option value='2' selected>No</option>";
		} echo "</select></td>";
		echo "<td><input type='checkbox' name='delete".$row->id."'></td></tr>";
	}
	echo "<tr><td></td><td><input type='submit' name ='save_change_btn' value='Save change' style='width:140px; height:25px; background: #ccc'></td></tr>
	</form></table>";
}
function lb_form_string_generate()
{
	global $wpdb;
	$table_name = $wpdb->prefix .'run_string';
	if(isset($_POST['lb_save_string_btn']))
	{
		if(function_exists('current_user_can') && !current_user_can('manage_options'))
		{
			die ( _e('Hacker?', 'wp_runstring'));
		}
		$lb_add_string = $_POST['lb_new_string_add'];
		$lb_priorytet_string = $_POST['priory'];
		$lb_publication_string = $_POST['publ'];
		$wpdb->insert($table_name,
		array('strcont' => $lb_add_string, 'priory' => $lb_priorytet_string, 'publ' => $lb_publication_string),
		array('%s', '%d', '%d')
		);
	}
	?>
	<form name="lb_base_string_setup" method="post" action="<?php $_SERVER['PHP_SELF']?>?page=runstring&amp;updated=true">
	<table>
	<?php if(function_exists('wp_nonce_field'))
	{
		wp_nonce_field('lb_base_string_setup_form');
	}
	?>
	<tr><td>New String</td><td><input type="text" size="250" name="lb_new_string_add"></td></tr>
	<tr><td>Priorytet</td><td>
	<select name="priory" ><option value="1">1</option>
	<option value="2" selected>2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="8">8</option>
	<option value="9">9</option>
	<option value="10">10</option>
	</select>
	</td></tr>
		<tr><td>Publication</td><td><select  name="publ"><option value="1" selected>Yes</option>
	<option value="2">No</option>
	
	</select></td></tr>
	<tr><td></td><td><input type="submit" name="lb_save_string_btn" value="Save string" style="width:140px; height:25px; background: #ccc"></td></tr>
	</table>
	</form>
	<?php
}
function lb_form_generate()
{
	if(isset($_POST['lb_save_btn']))
	{
		if(function_exists('current_user_can') && !current_user_can('manage_options'))
		{
			die ( _e('Hacker?', 'wp_runstring'));
		}
		$lb_symvol_discriminant = $_POST['lb_symvol_discriminant'];
		$lb_enabled =$_POST['lb_enabled'];
		
		update_option('lb_symvol_discriminant', $lb_symvol_discriminant);
		update_option('lb_enabled', $lb_enabled);
	}
	?>
	<form name="lb_base_setup" method="post" action="<?php $_SERVER['PHP_SELF']?>?page=runstring&amp;updated=true">
	<table>
	<?php 
	if(function_exists('wp_nonce_field'))
	{
		wp_nonce_field('lb_base_setup_form');
	}
	?>
	<tr><td>Separating character</td><td><input type="text" size="3" name="lb_symvol_discriminant" value="<?php echo get_option('lb_symvol_discriminant'); ?>"></td></tr>
	<tr><td>Show string</td><td>
	<?php if(get_option('lb_enabled') == 'on'){
		echo "<input type='checkbox' name='lb_enabled' checked/>";
	} else { 
	echo "<input type='checkbox' name='lb_enabled'/>";
	}
		?></td></tr>
	<tr><td></td><td><input type="submit" name="lb_save_btn" value="Save" style="width:140px; height:25px; background: #ccc"></td></tr>
	</table>
	</form>
	<?php
}