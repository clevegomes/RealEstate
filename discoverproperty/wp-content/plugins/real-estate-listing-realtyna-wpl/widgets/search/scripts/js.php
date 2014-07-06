<script type="text/javascript">
wplj(document).ready(function()
{
	wplj(".MD_SEP > .wpl_search_field_container:first-child").click(function()
	{
		wplj(this).siblings(".wpl_search_field_container").slideToggle(400)
	})
});

/** main search function **/
function wpl_do_search_<?php echo $widget_id; ?>()
{
	request_str = '';
	wplj("#wpl_searchwidget_<?php echo $widget_id; ?> input:checkbox").each(function(index, element)
	{
		id = element.id;
		name = element.name;
		if(name.substring(0, 2) == 'sf')
		{
			if(wplj("#wpl_searchwidget_<?php echo $widget_id; ?> #"+id).closest('li').css('display') != 'none')
			{
				if(element.checked) value = element.value; else value = "-1";
				request_str += "&" + element.name.replace('sf<?php echo $widget_id; ?>_', 'sf_') +"=" + value;
			}
		}
	});

	wplj("#wpl_searchwidget_<?php echo $widget_id; ?> input:text").each(function(index, element)
	{
		id = element.id;
		name = element.name;
		if(name.substring(0, 2) == 'sf')
		{
			if(wplj("#wpl_searchwidget_<?php echo $widget_id; ?> #"+id).closest('li').css('display') != 'none')
			{
				value = element.value;
				request_str += "&" + element.name.replace('sf<?php echo $widget_id; ?>_', 'sf_') +"=" + value;
			}
		}
	});

	wplj("#wpl_searchwidget_<?php echo $widget_id;?> input[type=hidden]").each(function(index, element)
	{
		id = element.id;
		name = element.name;
		if(name.substring(0, 2) == 'sf')
		{
			if(wplj("#wpl_searchwidget_<?php echo $widget_id; ?> #"+id).closest('li').css('display') != 'none')
			{
				value = element.value;
				request_str += "&" + element.name.replace('sf<?php echo $widget_id; ?>_', 'sf_') +"=" + value;
			}
		}
	});
	
	wplj("#wpl_searchwidget_<?php echo $widget_id; ?> select, #wpl_searchwidget_<?php echo $widget_id; ?> textarea").each(function(index, element)
	{
		id = element.id;
		name = element.name;
		if(name.substring(0, 2) == 'sf')
		{
			if(wplj(element).closest('li').css('display') != 'none')
			{
				value = wplj(element).val();
				if(value != null) request_str += "&" + element.name.replace('sf<?php echo $widget_id; ?>_', 'sf_') +"=" + value;
			}
		}
	});
	
	/** Adding widget id **/
	request_str = 'widget_id=<?php echo $widget_id; ?>'+request_str;

	/** Create full url of search **/
	search_page = '<?php echo wpl_property::get_property_listing_link($target_id); ?>';
	
    if(search_page.indexOf('?') >= 0) search_str = search_page+'&'+request_str
    else search_str = search_page+'?'+request_str
    
	window.location = search_str;
	return false;
}

function wpl_sef_request<?php echo $widget_id; ?>(request_str)
{
	request_str = request_str.slice(1);
	splited = request_str.split("&");
	sef_str = '';
	unsef_str = '';
	var first_param = true;
	
	for(var i = 0; i < splited.length; i++)
	{
		splited2 = splited[i].split("=");
		key = splited2[0];
		value = splited2[1];
		
		if(key.substring(0, 9) == 'sf_select')
		{
			table_field = splited2[0].replace('sf_select_', '');
			key = wpl_ucfirst(table_field.replace('_', ' '));
			value = splited2[1];
			
			/** for setting text instead of value **/
			if(value != -1 && value != '' && (table_field == 'listing' || table_field == 'property_type'))
			{
				field_type = wplj("#sf<?php echo $widget_id; ?>_select_"+table_field).prop('tagName');
				if(field_type.toLowerCase() == 'select') value = wplj("#sf<?php echo $widget_id; ?>_select_"+table_field+" option:selected").text();
			}
			
			/** set to the SEF url **/
			if(value != -1 && value != '') sef_str += '/'+key+':'+value;
		}
		else
		{
			if(first_param && value != -1 && value != '')
			{
				unsef_str += '?'+key+'='+value;
				first_param = false;
			}
			else if(value != -1 && value != '')
			{
				unsef_str += '&'+key+'='+value;
			}
		}
	}
	
	final_str = sef_str+"/"+unsef_str;
	return final_str.slice(1);
}

function wpl_add_to_multiple<?php echo $widget_id; ?>(value, checked, table_column)
{
	setTimeout("wpl_add_to_multiple<?php echo $widget_id; ?>_do('"+value+"', "+checked+", '"+table_column+"');", 30);
}

function wpl_add_to_multiple<?php echo $widget_id; ?>_do(value, checked, table_column)
{
	var values = wplj('#sf<?php echo $widget_id; ?>_multiple_'+table_column).val();
	values = values.replace(value+',', '');
	
	if(checked) values += value+',';
	wplj('#sf<?php echo $widget_id; ?>_multiple_'+table_column).val(values);
}

function wpl_select_radio<?php echo $widget_id; ?>(value, checked, table_column)
{
	console.log(value+":"+checked+":"+table_column);
	if(checked) wplj('#sf<?php echo $widget_id;?>_select_'+table_column).val(value);
}

function wpl_do_reset<?php echo $widget_id; ?>(exclude, do_search)
{
    if(!exclude) exclude = new Array();
    if(!do_search) do_search = false;
    
	wplj("#wpl_searchwidget_<?php echo $widget_id; ?>").find(':input').each(function()
    {
        if(exclude.indexOf(this.id) != -1) return;
        
        switch(this.type)
        {
            case 'text':

                elmid = this.id;
                idmin = elmid.indexOf("min");
                idmax = elmid.indexOf("max");
                iddate = elmid.indexOf("date");

                if(idmin != '-1' && iddate == '-1') wplj(this).val('0');
                else if(idmax != '-1' && iddate == '-1') wplj(this).val('1000000');
                else wplj(this).val('');

                break;
            case 'select-multiple':
                
                wplj(this).multiselect("uncheckAll");
                break;
                
            case 'select-one':

                wplj(this).val('');
                wplj(this).trigger("chosen:updated");
                break;
                
            case 'password':
            case 'textarea':
                
                wplj(this).val('');
                break;
                
            case 'checkbox':
            case 'radio':
                
                this.checked = false;
                break;
                
            case 'hidden':

                elmid = this.id;
                idmin = elmid.indexOf("min");
                idmax = elmid.indexOf("max");
                idtmin = elmid.indexOf("tmin");
                idtmax = elmid.indexOf("tmax");
                
                if(idtmin != '-1')
                {
                    var table_column = elmid.split("_tmin_");
                    table_column = table_column[1];
                    var widget_id = elmid.split("_");
                    widget_id = parseInt(widget_id[0].replace("sf", ""));
                }
                else if(idtmax != '-1')
                {
                    var table_column = elmid.split("_tmax_");
                    table_column = table_column[1];
                    var widget_id = elmid.split("_");
                    widget_id = parseInt(widget_id[0].replace("sf", ""));
                }
                else if(idmin != '-1')
                {
                    var table_column = elmid.split("_min_");
                    table_column = table_column[1];
                    var widget_id = elmid.split("_");
                    widget_id = parseInt(widget_id[0].replace("sf", ""));
                }
                else if(idmax != '-1')
                {
                    var table_column = elmid.split("_max_");
                    table_column = table_column[1];
                    var widget_id = elmid.split("_");
                    widget_id = parseInt(widget_id[0].replace("sf", ""));
                }
                
                try
                {
                    var min_slider_value = wplj("#slider"+widget_id+"_range_"+table_column).slider("option", "min");
                    var max_slider_value = wplj("#slider"+widget_id+"_range_"+table_column).slider("option", "max");

                    wplj("#sf"+widget_id+"_tmin_"+table_column).val(min_slider_value);
                    wplj("#sf"+widget_id+"_tmax_"+table_column).val(max_slider_value);
                    wplj("#sf"+widget_id+"_min_"+table_column).val(min_slider_value);
                    wplj("#sf"+widget_id+"_max_"+table_column).val(max_slider_value);

                    wplj("#slider"+widget_id+"_range_"+table_column).slider("values", 0, min_slider_value);
                    wplj("#slider"+widget_id+"_range_"+table_column).slider("values", 1, max_slider_value);

                    wplj("#slider"+widget_id+"_showvalue_"+table_column).html(wpl_th_sep<?php echo $widget_id; ?>(min_slider_value)+" - "+wpl_th_sep<?php echo $widget_id; ?>(max_slider_value));
                }
                catch(err){}
        }
    });
	
	if(do_search) wpl_do_search_<?php echo $widget_id; ?>();
}

function wpl_th_sep<?php echo $widget_id; ?>(num)
{
    sep = ",";
    num = num.toString();
    x = num;
    z = "";

    for (i=x.length-1; i>=0; i--)
        z += x.charAt(i);

    // add seperators. but undo the trailing one, if there
    z = z.replace(/(\d{3})/g, "$1" + sep);

    if (z.slice(-sep.length) == sep)
        z = z.slice(0, -sep.length);

    x = "";
    // reverse again to get back the number
    for (i=z.length-1; i>=0; i--)
        x += z.charAt(i);

    return x;
}

<?php
	$this->create_listing_specific_js();
	$this->create_property_type_specific_js();
?>
wplj(document).ready(function()
{
	wplj("#wpl_searchwidget_<?php echo $widget_id; ?> select").chosen();
    wplj('#wpl_searchwidget_<?php echo $widget_id; ?> input[type="checkbox"]:not(.yesno)').checkbox({cls: 'jquery-safari-checkbox',empty:'<?php echo wpl_global::get_wpl_asset_url('img/empty.png'); ?>'});
    wplj('#wpl_searchwidget_<?php echo $widget_id; ?> input.yesno[type="checkbox"]').checkbox({empty:'<?php echo wpl_global::get_wpl_asset_url('img/empty.png'); ?>'});
    
    /** make the form empty if searched by listing id **/
    wplj("#sf<?php echo $widget_id; ?>_select_mls_id").on("change", function()
    {
        wpl_do_reset<?php echo $widget_id; ?>(new Array("sf<?php echo $widget_id; ?>_select_mls_id"), false);
    });
})
</script>