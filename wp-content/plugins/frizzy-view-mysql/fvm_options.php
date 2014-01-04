<?php
//add the admin options page
add_action('admin_menu','fvm_admin_add_page');

function fvm_admin_add_page()
{
	add_options_page('Frizzy View MySQL Options','Frizzy View MySQL','manage_options','fvm_options','fvm_options_page');
}

function fvm_options_page()
{
	?>

	<h2>Frizzy View MySQL Options</h2>
	Configure the plugin here
	<form action="options.php" method="post">
	<?php 
		settings_fields('fvm_options');
		do_settings_sections('fvm1');
		do_settings_sections('fvm2');
		do_settings_sections('fvm3');
	?>
	<br />
	<input name="Submit" type="submit"  value="<?php esc_attr_e('Save Changes'); ?>" />
	<?php
	$options = get_option('fvm_options');
	//test connection
	$test = new wpdb($options['fvm_user'],$options['fvm_pwd'],$options['fvm_db'],$options['fvm_host']);
	
	if(!$test->dbh)
	{
		echo "<br /><strong>Unable to connect to your external database</strong><br />Check your Database Settings";		
	}
	//visible in dashboard?
	if($options['fvm_editor'] != 'yes' && $options['fvm_admin'] != 'yes')
	{
			echo '<br /><strong>No one will see this in the Dashboard</strong><br />Check the Admin Settings';
	}
	?>
	</form></div>	
	<?php
	

	
}

//add the admin settings host, database, user, password
add_action('admin_init','fvm_admin_init');
function fvm_admin_init()
{
	register_setting('fvm_options','fvm_options','fvm_options_validate');
	add_settings_section('fvm_main', 'Database Settings','fvm_db_section_text','fvm1');
	add_settings_field('fvm_host','Host','display_fvm_host','fvm1','fvm_main');
	add_settings_field('fvm_db','Database','display_fvm_db','fvm1','fvm_main');
	add_settings_field('fvm_user','Username','display_fvm_user','fvm1','fvm_main');
	add_settings_field('fvm_pwd','Password','display_fvm_pwd','fvm1','fvm_main');
	add_settings_section('fvm_main', 'Admin Settings','fvm_ad_section_text','fvm2');
	add_settings_field('fvm_admin', 'Admin Access', 'display_fvm_admin','fvm2','fvm_main');
	add_settings_field('fvm_editor', 'Editor Access', 'display_fvm_editor','fvm2','fvm_main');
	add_settings_section('fvm_main', 'Display Settings','fvm_ds_section_text','fvm3');
	add_settings_field('fvm_cols', 'Default number of search results to display at a time', 'display_fvm_cols','fvm3','fvm_main');
	add_settings_field('fvm_friendly','Enter a friendly name to be displayed for the database (leave blank to display actual name','display_fvm_friendly','fvm3','fvm_main');
}

function display_fvm_friendly()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_friendly' name='fvm_options[fvm_friendly]' size='40' type='text' value='{$options['fvm_friendly']}' />"; 	
}

function display_fvm_cols()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='1' ".($options['fvm_cols']=='1'?'checked':'')."/>1
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='2' ".($options['fvm_cols']=='2'?'checked':'')."/>2
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='3' ".($options['fvm_cols']=='3'?'checked':'')."/>3
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='4' ".($options['fvm_cols']=='4'?'checked':'')."/>4
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='5' ".($options['fvm_cols']=='5'?'checked':'')."/>5
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='6' ".($options['fvm_cols']=='6'?'checked':'')."/>6
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='7' ".($options['fvm_cols']=='7'?'checked':'')."/>7
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='8' ".($options['fvm_cols']=='8'?'checked':'')."/>8
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='9' ".($options['fvm_cols']=='9'?'checked':'')."/>9
		  <input id='fvm_cols' name='fvm_options[fvm_cols]'  type='radio' value='10' ".($options['fvm_cols']=='10'?'checked':'')."/>10";
}

function display_fvm_editor()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_editor' name='fvm_options[fvm_editor]'  type='checkbox' value='yes' ".($options['fvm_editor']=='yes'?'checked':'')." />"; 
}

function display_fvm_admin()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_admin' name='fvm_options[fvm_admin]'  type='checkbox' value='yes' ".($options['fvm_admin']=='yes'?'checked':'')." />"; 
}


function display_fvm_host()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_host' name='fvm_options[fvm_host]' size='40' type='text' value='{$options['fvm_host']}' />"; 
}

function display_fvm_db()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_db' name='fvm_options[fvm_db]' size='40' type='text' value='{$options['fvm_db']}' />"; 
}

function display_fvm_user()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_user' name='fvm_options[fvm_user]' size='40' type='text' value='{$options['fvm_user']}' />"; 
}

function display_fvm_pwd()
{
	$options = get_option('fvm_options');
	echo "<input id='fvm_pwd' name='fvm_options[fvm_pwd]' size='40' type='password' value='{$options['fvm_pwd']}' />"; 
}


function fvm_options_validate($input)
{
	
	$output = $input;
	return $output;
}

function fvm_db_section_text()
{
?>
	<p>Enter the values to enable connection to your chosen database</p>
<?php
}

function fvm_ad_section_text()
{
?>
	<p>Who has access to the Frizzy View MySQL Dashboard Widget?</p>
<?php
}

function fvm_ds_section_text()
{
?>
	<p>Frizzy View MySQL displays best in a one column layout.  If you are using more than one column you may want to change how some elements are displayed</p>
<?php
}





?>