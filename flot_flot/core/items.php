<?php
	# handles everything to do with the items, initiating and rendering

	# properties: url, private, oncology, template, dynamic/static
	# methods: rebuild, update, add, edit, delete


    # initiate an item from the data in urls datastore
	
	# call its render method

	class Item {

		public $o_loaded_item_object;
		public $html_page;
		public $s_base_path;
		public $o_oncology;

		function __construct($o_item) {
			$this->o_loaded_item_object = $o_item;
			$this->s_base_path = str_replace($_SERVER['SCRIPT_NAME'],"",str_replace("\\","/",$_SERVER['SCRIPT_FILENAME'])).'/';
			# set a reference to my oncology
			$datastore = new DataStore;
			$this->o_oncology = $datastore->get_oncology($o_item->oncology);
		}

		function rebuild() {
			# render, and rebuild dependent items
		}
		function update() {

			$item_url = new ItemURL($this->o_loaded_item_object);


			# create any directories for the file if neccesary
			if($item_url->has_dirs()){
				# make dirs
				mkdir($this->s_base_path.$item_url->dir_path(), 0777, true);
			}

			# write the file itself
			file_put_contents($item_url->writing_file_path($this->s_base_path), $this->html_page);

			echo "just rendered item: ".$item_url->writing_file_path($this->s_base_path);
		}
		function delete() {
			# delete the file

			# if it was the last file in folder, delete folder, repeat this recursively until back to root
		}
		function render() {
			$this->datastore = new DataStore;
			# get template
			$template = file_get_contents($this->s_base_path.'/flot_flot/themes/'.$this->datastore->settings->theme.'/flot_template.html');

			# parse in data
			$sa_keys = array_keys(get_object_vars($this->o_loaded_item_object));

			foreach ($sa_keys as $key) {
				if($this->o_loaded_item_object->$key !== null)
					$template = str_replace("{{item:".$key."}}", $this->o_loaded_item_object->$key, $template);
			}
			# general parsing
			$template = str_replace("{{flot:theme_dir}}", '/flot_flot/themes/'.$this->datastore->settings->theme.'/', $template);

			# minify etc
			$search = array(
		        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
		        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
		        '/(\s)+/s'       // shorten multiple whitespace sequences
		    );

		    $replace = array(
		        '>',
		        '<',
		        '\\1'
		    );

		    $template = preg_replace($search, $replace, $template);

			# serve to user

			//ob_start("ob_gzhandler");
			$this->html_page = $template;


			# store to disk
			$this->update();
		}

		function save(){
			# update the datastore

			# re-render the page
			$this->render();
		}

		#
		# content generation
		#
		function make_header(){
			# spit out content type (settings? content type, or not to display)

			# keywords etc, generate if necessary

			# open graph stuff
		}

		#
		# editing
		#
		function html_edit_form(){
			$html_form = "";

			$html_form .= '<form role="form" method="post" action="index.php">';
			# title
			$html_form .= '<div class="form-group">';
			$html_form .= '<input type="text" class="form-control" name="title" placeholder="page title" value="'.$this->o_loaded_item_object->title.'">';
			$html_form .= '</div>';
			# content
			$html_form .= '<div class="form-group">';
			$html_form .= '<textarea class="form-control" name="content" rows="12">'.$this->o_loaded_item_object->content.'</textarea>';
			$html_form .= '</div>';
			# save
			$html_form .= '<div class="form-group">';
			$html_form .= '<input value="save" type="submit" class="form-control btn btn-success">';
			$html_form .= '</div>';

			# hidden elements

			$html_form .= '<input type="hidden" name="section" value="items">';
			$html_form .= '<input type="hidden" name="item_id" value="'.$this->o_loaded_item_object->id.'">';

			$html_form .= '</form>';
			return $html_form;
		}
		function update_from_post(){
			# update the item from post variables
			print_r($this->o_oncology);
			# we can find out what post variables to look for by checking our oncology
		}
	}
?>