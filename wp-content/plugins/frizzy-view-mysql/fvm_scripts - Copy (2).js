<script id="eatScript" type="text/javascript">
jQuery(document).ready(function($){
	
	$('#buttonGo').click(function(){
			
				getTable();
			
		});
		
		function ShowLoading()
		{
			$('#outputDiv').prepend('<br /><img id="loading" src="' + $("input#eat_path").val() + 'progress.gif" />');	
		}
		
		function GetFilterData(offSet)
		{
			var filterData = 
			{
				action: 'GetRecords',
				table2Edit: $('#selectedTable').val(),
				eat_cols: $("input#eat_cols").val(),
				keys: $("input#keys").val(),
				values: $("input#values").val(),
				offSet: offSet,								fuzzy: $("input#fuzzy").val()
			}
			return filterData;
		}
		
		// This creates the event handlers for the next/previous buttons & also the save & delete
		function NextPrev()
		{
			
			var offSet = $("input#offSet").val();
			var eat_cols = $("input#eat_cols").val()						
			$('#buttonNext').click(function()
			{
				ShowLoading();
				
				offSet = parseInt(offSet) + parseInt(eat_cols);
				
				var filterData = GetFilterData(offSet);
					
				jQuery.post(ajaxurl,filterData,function(response){$("#outputDiv").html(response);}).complete(function(){NextPrev()});
				
			});
			
			$('#buttonPrev').click(function()
			{
				ShowLoading();
				
				offSet = parseInt(offSet) - parseInt(eat_cols);
				var filterData = GetFilterData(offSet);
					
				jQuery.post(ajaxurl,filterData,function(response){$("#outputDiv").html(response);}).complete(function(){NextPrev()});
				
			});
			
			$("[id^=save]").click(function()
			{
				ShowLoading();
				var dlg = jQuery("<div id='messageDiv' title='Update Record?' STYLE='padding: 10px'  />").html("Are you sure you want to update this record?");
				//we need to pick up all the primary keys (id='PRIMARY:<column name>') for the update command so cycle through the table
				//using the column number identified by the button id e.g. delete1 (extract the 1 and cast)
				var keys ="";
				var values ="";
				//for the update vals i.e. non-primary
				var keysU = "";
				var valuesU = "";
				var column = parseInt(this.id.substring(4));
				//loop through table rows
				var rows = $('#tableCols tr:gt(0)'); //skip header
				rows.each(function(index)
				{
					var key = $(this).find("td").eq(column).attr('id');
					
					if(key != undefined && key.substring(0,7) == "PRIMARY")
					{
						var value = $(this).find("td").eq(column).text();
						//add this pair
						keys += (keys==""?"":"~") + key.substring(8);
						values += (values==""?"":"~") + value;
					}
					else if(key != undefined)
					{
						var valueU = $(this).find("td").eq(column).find('input').val();;
						//add this pair for updating
						keysU += (keysU==""?"":"~") + key;
						valuesU += (valuesU==""?"":"~") + valueU;
					}
					
				});
				dlg.dialog({
							'dialogClass' : 'wp-dialog',
							'modal' : true,
							'autoOpen' : false,
							'closeOnEscape' : true,
							'buttons' : [
							{
							'text' : 'Yes',
							'class' : 'button-primary',
							'click' : function() {
								var filterData = 
								{
									action: 'UpdateRecord',
									table2Edit: $('#selectedTable').val(),
									keys: keys,
									values: values,
									keysU: keysU,
									valuesU: valuesU
									
								}
									
								$(this).dialog('close');
								jQuery.post(ajaxurl,filterData,function(response){$("#outputDiv").html(response);});
							}
							},
							{
							'text' : 'No',
							'class' : 'button-primary',
							'click' : function() {
								$(this).dialog('close');
							}
							}
							]
							}).dialog('open');
			});
			
			$("[id^=delete]").click(function()
			{
				ShowLoading();
				var dlg = jQuery("<div id='messageDiv' title='DELETE?' STYLE='padding: 10px'  />").html("Are you sure you want to delete this record?");
				//we need to pick up all the primary keys (id='PRIMARY:<column name>') for the delete command so cycle through the table
				//using the column number identified by the button id e.g. delete1 (extract the 1 and cast)
				var keys ="";
				var values ="";
				var column = parseInt(this.id.substring(6));
				//loop through table rows
				var rows = $('#tableCols tr:gt(0)'); //skip header
				rows.each(function(index)
				{
					var key = $(this).find("td").eq(column).attr('id');
					var value = $(this).find("td").eq(column).text();
					if(key != undefined && key.substring(0,7) == "PRIMARY")
					{
						//add this pair
						keys += (keys==""?"":"~") + key.substring(8);
						values += (values==""?"":"~") + value;
					}
				});
				dlg.dialog({
							'dialogClass' : 'wp-dialog',
							'modal' : true,
							'autoOpen' : false,
							'closeOnEscape' : true,
							'buttons' : [
							{
							'text' : 'Yes',
							'class' : 'button-primary',
							'click' : function() {
								var filterData = 
								{
									action: 'DeleteRecord',
									table2Edit: $('#selectedTable').val(),
									keys: keys,
									values: values
									
								}
									
								$(this).dialog('close');
								jQuery.post(ajaxurl,filterData,function(response){$("#outputDiv").html(response);});
							}
							},
							{
							'text' : 'No',
							'class' : 'button-primary',
							'click' : function() {
								$(this).dialog('close');
							}
							}
							]
							}).dialog('open');
			
		
			});
										
		}
		
				
		function getTable()
		{
		
			ShowLoading();
			
			var table2Edit = $('#selectedTable').val();
			
			if(table2Edit != "NONE")
			{			
				//Return the table fields
				var data = 
				{
					action: 'GetTable',
					table2Edit: $('#selectedTable').val(),
					eat_cols: $("input#eat_cols").val()
				};
				
				jQuery.post(ajaxurl, data, function(response){$("#outputDiv").html(response);})
					.complete(function()
					{
						//Make the key/value pairs available to search and add button clicks
						var keys ="";
						var values ="";
						
						//Function to build key/value pairs
						function BuildKeyValuePairs()
						{
							//loop through table rows
							var rows = $('#tableCols tr:gt(0)'); //skip header
							rows.each(function(index)
							{
								var key = $(this).find("td").eq(1).find('input').attr('id');
								var value = $(this).find("td").eq(1).find('input').val();
								if(value != "")
								{
									//add this pair
									keys += (keys==""?"":"~") + key;
									values += (values==""?"":"~") + value;
								}
							});
						}
						
						//Find Button
						$('#buttonFind').click(function()
						{
							//first build key/value pairs
							BuildKeyValuePairs();
							/*
							if(keys.length > 0) 
							{*/
								ShowLoading();
								var filterData =
								{
									action: 'GetRecords',
									table2Edit: $('#selectedTable').val(),
									eat_cols: $("input#eat_cols").val(),
									keys: keys,
									values: values,
									offSet: 0,																		fuzzy: $("input#fuzzy").attr('checked')
								}
								jQuery.post(ajaxurl,filterData,function(response){$("#outputDiv").html(response);})
									.complete(function()
									{
										//create handlers for next & previous buttons 
										NextPrev()
									});/*
							}							
							else
							{
								ShowMessage('You must enter a value in at least one of the fields','Nothing Entered');
							}*/							
						});
						
						//Add Button
						$('#buttonAdd').click(function()
						{
							ShowLoading();
							//Get the key value pairs
							BuildKeyValuePairs();
							var filterData =
							{
								action: 'AddRecord',
								table2Edit: $('#selectedTable').val(),
								eat_cols: $("input#eat_cols").val(),
								keys: keys,
								values: values,
							}
							jQuery.post(ajaxurl,filterData,function(response){$("#outputDiv").html(response);});
							
						});
						
						//Reset Button
						$('#buttonReset').click(function()
						{
						
							var rows = $('#tableCols tr:gt(0)'); //skip header
							rows.each(function(index)
							{
								$(this).find("td").eq(1).find('input').val("");
							});
						
						});
					});

			}
			else
			{
				$("#outputDiv").html("");
				ShowMessage("You must choose a table to edit.","Select Table");
			}
		}
		
		function ShowMessage(message, title)
		{
			var dlg = jQuery("<div id='messageDiv' title='" + title + "' STYLE='padding: 10px'  />").html(message);
			
			dlg.dialog({
						'dialogClass' : 'wp-dialog',
						'modal' : true,
						'autoOpen' : false,
						'closeOnEscape' : true,
						'buttons' : [
						{
						'text' : 'Close',
						'class' : 'button-primary',
						'click' : function() {
						$(this).dialog('close');
						}
						}
						]
						}).dialog('open');
			
		}
		
		
		
});

</script>