<?php
	# manage site

	/*
	this page handles both gets and sets.
	ideally we could check for post vars, process set request, then location change to a get
	however we also need to show invalid post requests? maybe we could just do that client side.
	*/

	$S_BASE_PATH = str_replace($_SERVER['SCRIPT_NAME'],"",str_replace("\\","/",$_SERVER['SCRIPT_FILENAME'])).'/';
	require_once($S_BASE_PATH.'flot_flot/core/flot.php');


	$flot = new Flot;
	$admin_ui = new AdminUI;


	if(!$flot->b_is_user_admin()){
		# forward them to login page
		$flot->_page_change("/flot_flot/admin/login.php");
	}


	$html_main_admin_content = "";
	$html_main_admin_content_menu = "";
	$s_body_class = "";

	$s_section = "";
		
	if($flot->b_post_vars()){
		#
		# handle post request
		#
		$s_action = $flot->s_post_var_from_allowed("action", array("edit"), "edit");		
		$s_section = $flot->s_post_var_from_allowed("section", array("items", "pictures", "menus", "settings"), "items");

		switch($s_section){
			case "items":
				switch ($s_action) {
					case 'edit':
						# get the id, find the item, then try replacing the attributes
						$item_id = $flot->s_post_var("item_id", false);
						if($item_id){
							// we have an item id, now we'll try and get the corresponding item information
							$o_item = $flot->datastore->get_item_data($item_id);
							$o_full_item = $flot->datastore->o_get_full_item($item_id);

							if($o_item && $o_full_item){
								$Item = new Item($o_item);

								$Item->_set_full_item($o_full_item);
								$Item->update_from_post();

								# persist (or not) the item
								$Item->save();

								
								# change location to view the item
								$flot->_page_change("/flot_flot/admin/index.php?section=items&oncology=page&action=list");
							}
						}
						break;
				}
				break;
			case "menus":
				switch ($s_action) {
					case 'edit':
						# get the id, find the item, then try replacing the attributes
						$menu_id = $flot->s_post_var("menu_id", false);
						if($menu_id){

							$o_menu = $flot->datastore->get_menu_data($menu_id);

							if($o_menu){
								$Menu = new Menu($o_menu);

								$Menu->update_from_post();

								# persist (or not) the item
								$Menu->save();


								# change location to view the item
								$flot->_page_change("/flot_flot/admin/index.php?section=menus&action=list");
							}
						}
						break;
				}
				break;
			case "settings":
				switch ($s_action) {
					case 'edit':		
						//print_r($datastore)				
						foreach ($_POST as $param_name => $param_val) {
							if(isset($flot->datastore->settings->{$param_name})){
								// the posted variable already exists, so we'll update it
								$flot->datastore->settings->{$param_name} = $param_val;
								$flot->datastore->b_save_datastore("settings");
							}
						}

						# change location to view the item
						$flot->_page_change("/flot_flot/admin/index.php?section=settings");
						break;
				}
				break;
		}

		# location change to corresponding get
	}else{
		#
		# no post vars, this is a GET request ?
		#

		$s_section = $flot->s_get_var_from_allowed("section", array("items", "pictures", "menus", "settings", "errors"), "items");

		switch($s_section){
			case "items":
				$s_action = $flot->s_get_var_from_allowed("action", array("edit", "list", "new", "delete"), "list");

				switch ($s_action) {
					case 'edit':
						$s_page_id = $flot->s_get_var('item', false);
						# menu items; purge from cache, preview, regenerate, delete
						
						if($s_page_id){
							# get the item
							$o_item = $flot->datastore->get_item_data($s_page_id);

							$o_full_item = $flot->datastore->o_get_full_item($s_page_id);

							# get the oncology

							# render a form
							$Item = new Item($o_item);
							$Item->_set_full_item($o_full_item);

							$html_main_admin_content .= $Item->html_edit_form();

							// make left menu smaller, to give more focus to editing
							$s_body_class = "smaller_left";
						}
						break;
					
					case 'list':
						# list all pages that can be edited (pagination ?)
						$oa_pages = $flot->oa_pages();
		         		$hmtl_pages_ui = "";
						$hmtl_pages_ui .= '<a class="btn btn-default btn-sm" href="/flot_flot/admin/index.php?section=items&oncology=page&action=new"><i class="glyphicon glyphicon-plus"></i> add a new page</a><hr/>';

		         		if(count($oa_pages) > 0)
		         		{
		         			$hmtl_pages_ui .= '<table class="table table-hover"><thead><tr><th>page name</th><th>Url</th><th>last changed</th><th>author</th><th>published</th><th>delete</th></tr></thead><tbody>';
			         		foreach ($oa_pages as $o_page) {
			         			//
			         			// get data
			         			//
								$s_id = urldecode($o_page->id);
								$s_title = urldecode($o_page->title);
								$s_url = urldecode($o_page->url);
								$s_author = urldecode($o_page->author);
								$s_date_modified = urldecode($o_page->date_modified);
								$s_published = (urldecode($o_page->published) === "true" ? '<i class="green glyphicon glyphicon-ok"></i>' : '<i class="red glyphicon glyphicon-remove"></i>');

								//
								// sanaitise data if necessary
								//
								$s_date_modified = explode('-', $s_date_modified);

								$s_date_modified = date("D jS M Y", mktime(0, 0, 0, $s_date_modified[1], $s_date_modified[0], $s_date_modified[2]));
								$s_url_text = $s_url;
								if(substr($s_url, 0,1) !== '/')
									$s_url = '/'.$s_url;
								if($s_url === "/index.html"){
									// homepage
									$s_url = '/';
									$s_url_text = ' <i class="glyphicon glyphicon-home"></i> Homepage';
								}
								$s_link_class = '';
								if(urldecode($o_page->published) !== "true"){
									$s_link_class = ' style="display:none;"';
								}


			         			# code...
			         			$hmtl_pages_ui .= '<tr><td><a href="/flot_flot/admin/index.php?section=items&oncology=page&item='.$s_id.'&action=edit">';
			         			$hmtl_pages_ui .= $s_title;
			         			$hmtl_pages_ui .= '</a></td><td><a target="_blank" href="'.$s_url.'" '.$s_link_class.'>'.$s_url_text.'</a></td><td>'.$s_date_modified.'</td><td>'.$s_author.'</td><td>'.$s_published.'</td><td><a href="/flot_flot/admin/index.php?section=items&oncology=page&item='.$o_page->id.'&action=delete" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> delete</a></td></tr>';
			         		}
			         		$hmtl_pages_ui .= '</tbody></table>';
			         	}else{
			         		$hmtl_pages_ui .= "no pages..";
			         	}

			         	$html_main_admin_content = $hmtl_pages_ui;
						break;
					
					case 'new':
						# create the new item, then do a page change to be editing it

						$s_newitem_id = $flot->datastore->s_new_item("page");


						$s_new_page = "/flot_flot/admin/index.php?section=items&oncology=page&item=".$s_newitem_id."&action=edit";
						$flot->_page_change($s_new_page);
						break;
					
					case 'delete':
						# create the new item, then do a page change to be editing it
						$s_page_id = $flot->s_get_var('item', false);
						if($s_page_id){
							// delete 'physical' copy on disk
							$o_item = $flot->datastore->get_item_data($s_page_id);
							$Item = new Item($o_item);
							$Item->delete();
							// remove from datastore
							$flot->datastore->_delete_item($s_page_id);

							$s_new_page = "/flot_flot/admin/index.php?section=items&oncology=page&action=list";
							$flot->_page_change($s_new_page);
						}
						break;
				}

	     		
				break;
			case "menus":
				$s_action = $flot->s_get_var_from_allowed("action", array("edit", "list", "new", "delete"), "list");

				switch ($s_action) {
					case 'edit':
						$s_menu_id = $flot->s_get_var('menu', false);
						# menu items; purge from cache, preview, regenerate, delete
						
						if($s_menu_id){
							# get the item
							$o_menu = $flot->datastore->get_menu_data($s_menu_id);

							# get the oncology

							# render a form
							$Menu = new Menu($o_menu);

							$html_main_admin_content .= $Menu->html_edit_form();

							// make left menu smaller, to give more focus to editing
							$s_body_class = "smaller_left";
						}else{
							$html_main_admin_content .= "flot couln't find that menu :(";
						}
						break;					
					case 'list':
						# list all pages that can be edited (pagination ?)
						$oa_menus = $flot->oa_menus();
		         		$hmtl_menus_ui = "";
						$hmtl_menus_ui .= '<a class="btn btn-default btn-sm" href="/flot_flot/admin/index.php?section=menus&action=new"><i class="glyphicon glyphicon-plus"></i> add a new menu</a><hr/>';

		         		if(count($oa_menus) > 0)
		         		{
		         			$hmtl_menus_ui .= '<table class="table table-hover"><thead><tr><th>menu name</th><th>delete</th></tr></thead><tbody>';
			         		foreach ($oa_menus as $o_menu) {
								$s_id = urldecode($o_menu->id);
								$s_title = urldecode($o_menu->title);

			         			# code...
			         			$hmtl_menus_ui .= '<tr><td><a href="/flot_flot/admin/index.php?section=menus&menu='.$s_id.'&action=edit">';
			         			$hmtl_menus_ui .= $s_title;
			         			$hmtl_menus_ui .= '</a></td>';

								$hmtl_menus_ui .= '<td><a href="/flot_flot/admin/index.php?section=menus&menu='.$o_menu->id.'&action=delete" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> delete</a></td></tr>';
			         		}
			         		$hmtl_menus_ui .= '</tbody></table>';
			         	}else{
			         		$hmtl_menus_ui .= "no menus..";
			         	}

			         	$html_main_admin_content = $hmtl_menus_ui;
						break;
					
					case 'new':
						
						# create the new item, then do a page change to be editing it

						$s_new_menu_id = $flot->datastore->s_new_menu();


						$s_new_menu = "/flot_flot/admin/index.php?section=menus&menu=".$s_new_menu_id."&action=edit";
						$flot->_page_change($s_new_menu);
						
						break;
					
					case 'delete':
						
						# create the new item, then do a page change to be editing it
						$s_menu_id = $flot->s_get_var('menu', false);
						if($s_menu_id){
							$flot->datastore->_delete_menu($s_menu_id);

							$s_new_page = "/flot_flot/admin/index.php?section=menus&action=list";
							$flot->_page_change($s_new_page);
						}
						
						break;
				}

	     		
				break;
			case "pictures":
				$s_action = $flot->s_get_var_from_allowed("action", array("select", "browse"), "browse");

				#
				# top menu
				#
				$html_main_admin_content .= '';


				$o_FileBrowser = new FileBrowser($s_action);

				$html_main_admin_content .= $o_FileBrowser->html_make_browser();

				if($s_action === "select"){
					echo $admin_ui->html_admin_headers_base();
					echo $admin_ui->html_admin_headers_pictures();
					echo $html_main_admin_content;
					exit();
				}

				break;
			case "settings":
				$html_main_admin_content = $admin_ui->html_make_settings_form($flot->datastore->settings);
				break;
			case "errors":
				$s_action = $flot->s_get_var_from_allowed("action", array("view", "clear"), "view");

				switch($s_action){
					case "clear":
						// clear log
						$fu_FileUtil = new FileUtilities;
						$fu_FileUtil->_wipe_errors();

						// reload to view						
						$flot->_page_change("/flot_flot/admin/index.php?section=errors&action=view");

						break;
					case "view":
						$html_main_admin_content = '<a class="btn btn-default btn-sm" href="/flot_flot/admin/index.php?section=errors&action=clear"><i class="glyphicon glyphicon-trash"></i> clear/delete log</a><hr/>';
						$html_main_admin_content .= $admin_ui->html_make_error_page();
						break;
				}
				break;
		}
	}

	#
	# if we're still here, render a page for the user
	#

	$admin_ui->html_make_admin_page($admin_ui->s_admin_header($s_section), $admin_ui->html_make_left_menu($s_section), $html_main_admin_content, $html_main_admin_content_menu, $s_body_class);
?>