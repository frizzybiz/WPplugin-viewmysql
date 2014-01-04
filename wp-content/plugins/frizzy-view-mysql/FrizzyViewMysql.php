<?php
/*
Plugin Name: Edit Any Table
Plugin URI: http://redeyedmonster.co.uk/edit-any-table/
Description: Dashboard widget which allows the editing of all tables in any database
Version: 1.3.1
Author: Nigel Bachmann
Author URI: http://redeyedmonster.co.uk
License: GPL2

Copyright 2012  Nigel Bachmann  (email : nigel@redeyedmonster.co.uk)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//load the options page
require('fvm_options.php');


// main function for dashboard widget
function FrizzyViewMysql()
{

	require('fvm_scripts.js');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_style("wp-jquery-ui-dialog");
	
	$options = get_option('fvm_options');
	$fvm_db = new wpdb($options['fvm_user'],$options['fvm_pwd'],$options['fvm_db'],$options['fvm_host']);
	
	if(!$fvm_db->dbh)
	{
			echo "<strong>Unable to connect to database, check your settings</strong>";
			return;
	}
		
	$result = $fvm_db->get_col($fvm_db->prepare("show tables",null));
	
	?>
	
	<!-- Store the number of columns to be displayed which can be passed across to the next page -->
	<input type="hidden" id="fvm_cols" value="<?php echo $options['fvm_cols']; ?>" />
	<!-- get and store the plugin path so that it is accessable -->
	<input type="hidden" id="fvm_path" value="<?php echo plugin_dir_url(__FILE__); ?>" />
	
	
	<button class="button-primary" title="Open selected table" id="buttonGo">Open</button>
	<input type="text" id="selectedTable" value="<?php echo plugin_dir_url(__FILE__); ?>" />
	<select id="selectedTable">
			<option value="NONE">*Choose Table to Edit*&nbsp;&nbsp;</option>
			
	<?php
	
	foreach($result as $table)
	{
		?>
		<option value="<?php echo $table; ?>"><?php echo $table; ?></option>
		<?php
	}
	
	?>
	</select>
	on database: <strong><?php echo ($options['fvm_friendly']==""?$options['fvm_db']:$options['fvm_friendly']) ?></strong>
	<div id="outputDiv"></div>
	
	<?php
	
}



//PHP functions to handle the Ajax requests
add_action('wp_ajax_GetRecords','ReturnRecords');
function ReturnRecords()
{
	$table2Edit = $_POST['table2Edit'];
	$keys = $_POST['keys'];
	$values = $_POST['values'];
	$offSet = $_POST['offSet'];
	$fvm_cols = $_POST['fvm_cols'];
	$fuzzy = $_POST['fuzzy'];
	
	?>
	<!-- Store the values we need but don't want to show in hidden fields which can be passed across to the next page -->
	<input type="hidden" id="fvm_cols" value="<?php echo $fvm_cols; ?>" />	
	<input type="hidden" id="keys" value="<?php echo $keys ?>" />
	<input type="hidden" id="values" value="<?php echo $values ?>" />
	<input type="hidden" id="offSet" value="<?php echo $offSet ?>" />
	<input type="hidden" id="fuzzy" value="<?php echo $fuzzy ?>" />
	
		
	<?php
	
	// get the users data
	$keysArray = explode("~", $keys);
	$valsArray = explode("~", $values);
	//Connect to the database
	$options = get_option('fvm_options');
	$fvm_db = new wpdb($options['fvm_user'],$options['fvm_pwd'],$options['fvm_db'],$options['fvm_host']);
	
	//Get a single record for column info
	$sql = $fvm_db->prepare("select * from ".$table2Edit." LIMIT 1",null);
	//echo $sql."<br />";
	$records = $fvm_db->get_results($sql,'ARRAY_N');
	
	//get column information
	$cols = $fvm_db->get_col_info('name',-1);
	$types = $fvm_db->get_col_info('type',-1);
	$primary = $fvm_db->get_col_info('primary_key',-1);
	$numeric = $fvm_db->get_col_info('numeric',-1);
		
	//build where
	$where = "";
	$vals = array();
	for($i = 0;$i < count($keysArray); $i++)
	{
	
		//need to find out if the value is for a numeric field or not
		$isNumeric = 0;
		for($in = 0; $in < count($cols); $in++)
		{
			if($cols[$in] == $keysArray[$i])
			{
				$isNumeric = $numeric[$in] == 1;
			}
		}
	
		if($keysArray[$i] != "")
		{
			if($i != 0)
			{
				$where = $where." and ";
			}
			
			if($isNumeric)
			{
				$where = $where.$keysArray[$i]." = %d";
				$vals[] = sanitize_text_field($valsArray[$i]);
			}
			else
			{
				if($fuzzy == "checked")
				{
					$where = $where.$keysArray[$i]." like %s";
					$vals[] = sanitize_text_field('%'.$valsArray[$i].'%');
				}
				else
				{
					$where = $where.$keysArray[$i]." = %s";
					$vals[] = sanitize_text_field($valsArray[$i]);
				}
			}
			
			
		}
	}
		
	//Get the records
	if(count($vals)>0)	
	{
		$sql = $fvm_db->prepare("select * from ".$table2Edit." where ".$where." LIMIT ".$offSet.", ".$fvm_cols."",$vals);
	}	
	else	
	{			
		$sql = $fvm_db->prepare("select * from ".$table2Edit." LIMIT ".$offSet.", ".$fvm_cols."",null);
	}
	$records = $fvm_db->get_results($sql,'ARRAY_N');
	
	//lets work out how many columns we're going to display (max from options)
	$numCols = $fvm_db->num_rows;
	?>
	<hr>
	<?php
	if($offSet > 0)
	{
	?>
	<button class="button" id="buttonPrev">&lt;&lt; Previous <?php echo $fvm_cols ?></button>&nbsp;
	<?php
	}
	if($numCols == (int)$fvm_cols)
	{
	?>
	<button class="button" id="buttonNext">Next <?php echo $fvm_cols ?> &gt;&gt;</button>
	<?php
	}
	if($numCols > 0)
	{
			
		?>
		<div style="overflow: auto">
			<table id="tableCols">
				<tr>
					<td><strong>Column</strong></td>
			<?php
			for($i = 0; $i < $numCols; $i++)
			{
				
				
				?>
				<td></td>
				<?php
				
			}
				?>
				</tr>
				<?php
			//need to write the results vertically
			for($i = 0; $i < count($cols); $i++)
			{
				?>
				<tr>
					<td><?php echo $cols[$i]; ?></td>
				<?php
				
				for($in = 0; $in < $numCols; $in++)
				{
					$row = $records[$in];
					if($primary[$i] == 1)
					{
						?>
						<td style="background-color:white" id="PRIMARY:<?php echo $cols[$i]; ?>"><?php echo $row[$i]; ?></td>
						<?php
					}
					else
					{
						?>
						<td id="<?php echo $cols[$i]; ?>"><input type="text" value="<?php echo sanitize_text_field($row[$i]); ?>" /></td>
						<?php
					}
				}
				?>
				</tr>
				<?php
			}
			?>
				<tr>
					<td></td>
				<?php
				for($i = 0; $i < $numCols; $i++)
				{
					?>
					<td></td>
					<?php
				}
				?>
				</tr>
			</table>
		</div>
		<?php
		
	}
	else
	{
		echo "No Results Found";
	}
	
	die();
}


add_action('wp_ajax_GetTable','TableDetails');
function TableDetails()
{
	//Get required values
	$table2Edit = $_POST['table2Edit'];
	$fvm_cols = $_POST['fvm_cols'];
	
	//connect to the database
	$options = get_option('fvm_options');
	$fvm_db = new wpdb($options['fvm_user'],$options['fvm_pwd'],$options['fvm_db'],$options['fvm_host']);
		
	//get a sample row
	$result = $fvm_db->get_results("select * from ".$table2Edit." LIMIT 0, 1");
	
	//get column information
	$cols = $fvm_db->get_col_info('name',-1);
	$types = $fvm_db->get_col_info('type',-1);
	$primary = $fvm_db->get_col_info('primary_key',-1);
	
	
	//build the table
	//if($fvm_db->num_rows > 0) Removed for 1.3.0
	//{
		?>
		<hr>
		<div>
			<button class="button-primary" title="Find records matching the values entered" id="buttonFind">Find</button>
			&nbsp;
			<input type="checkbox" id="fuzzy" />&nbsp;Fuzzy
			&nbsp;
			<button class="button" title="Clear all the values" id="buttonReset">Reset</button>
		</div>
		<hr>
		<div style="overflow: auto">
			<table id="tableCols">
				<tr>
					<td><strong>Column</strong></td>
					<td><strong>Value</strong></td>
				</tr>
			<?php
				for($i=0;$i<count($cols);$i++)
				{
				?>
					<tr>
						<td>
							<?php 
								echo $cols[$i]." (".$types[$i].")"; 
								if($primary[$i]==1)
								{
									echo " [PRIMARY]";
								}
							?>
							
						</td>
						<td>
							<input type="text" id="<?php echo sanitize_text_field($cols[$i]); ?>" />
						</td>
						
					</tr>
				<?php
				}
				?>
			</table>
		</div>
		<?php
	//}

	die();
}

//hook it up
function add_dashboard_widget_fvm()
{
	$options = get_option('fvm_options');
	if((current_user_can('administrator') && $options['fvm_admin']=='yes')||((current_user_can('administrator') || current_user_can('editor')) && $options['fvm_editor']=='yes'))
	{
		wp_add_dashboard_widget('fvm', 'Frizzy View MySQL', 'FrizzyViewMysql');
	}
}
add_action('wp_dashboard_setup','add_dashboard_widget_fvm');

// Add settings link on plugin page
function your_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=fvm_options.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );

?>