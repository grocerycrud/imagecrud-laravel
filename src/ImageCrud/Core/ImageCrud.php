<?php namespace ImageCrud\Core;

use ImageCrud\Library\ImageUploadHandler;
use ImageCrud\Library\ImageMoo;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Image CRUD for Laravel
 *
 * A Laravel library that creates an instant photo gallery CRUD automatically with just few lines of code.
 *
 * Copyright (C) 2011 through 2014  John Skoumbourdis.
 *
 * LICENSE
 *
 * Image CRUD for Laravel is released with the MIT license (license-mit.txt).
 * Please see the corresponding license file for details of these licenses.
 * You are free to use, modify and distribute this software, but all copyright information must remain.
 *
 * @package    	ImageCrud
 * @copyright  	Copyright (c) 2011 through 2014, John Skoumbourdis
 * @license    	https://github.com/scoumbourdis/image-crud/blob/master/license-image-crud.txt
 * @version    	0.6
 * @author     	John Skoumbourdis <scoumbourdisj@gmail.com>
 */
class ImageCrud {

	protected $table_name = null;
	protected $priority_field = null;
	protected $url_field = 'url';
	protected $title_field = null;
	protected $relation_field = null;
	protected $subject = 'Record';
	protected $image_path = '';
	protected $primary_key = 'id';
	protected $ci = null;
	protected $thumbnail_prefix = 'thumb__';
	protected $views_as_string = '';
	protected $css_files = array();
	protected $js_files = array();

	/* Unsetters */
	protected $unset_delete = false;
	protected $unset_upload = false;

	protected $language = null;
	protected $lang_strings = array();
	protected $default_language_path = null;
    protected $default_view_folder_path = null;
    protected $default_config_path = null;

    protected $public_path = null;

	function __construct() {

	}

	function set_table($table_name)
	{
		$this->table_name = $table_name;

		return $this;
	}

	function set_relation_field($field_name)
	{
		$this->relation_field = $field_name;

		return $this;
	}

	function set_ordering_field($field_name)
	{
		$this->priority_field = $field_name;

		return $this;
	}

	function set_primary_key_field($field_name)
	{
		$this->primary_key = $field_name;
	}

	function set_subject($subject)
	{
		$this->subject = $subject;

		return $this;
	}

	function set_url_field($url_field)
	{
		$this->url_field = $url_field;

		return $this;
	}

	function set_title_field($title_field)
	{
		$this->title_field = $title_field;

		return $this;
	}

	function set_image_path($image_path)
	{
		$this->image_path = $image_path;

		return $this;
	}

	function set_thumbnail_prefix($prefix)
	{
		$this->thumbnail_prefix = $prefix;

		return $this;
	}

	/**
	 * Unsets the delete operation from the gallery
	 *
	 * @return	void
	 */
	public function unset_delete()
	{
		$this->unset_delete = true;

		return $this;
	}

	/**
	 * Unsets the upload functionality from the gallery
	 *
	 * @return	void
	 */
	public function unset_upload()
	{
		$this->unset_upload = true;

		return $this;
	}

	public function set_css($css_file)
	{
		$this->css_files[sha1($css_file)] = base_url().$css_file;
	}

	public function set_js($js_file)
	{
		$this->js_files[sha1($js_file)] = base_url().$js_file;
	}

	protected function _library_view($view, $vars = array(), $return = FALSE)
	{
		$vars = (is_object($vars)) ? get_object_vars($vars) : $vars;

		$file_exists = FALSE;

		$ext = pathinfo($view, PATHINFO_EXTENSION);
		$file = ($ext == '') ? $view.'.php' : $view;

		$view_folder = $this->default_view_folder_path;

		if (file_exists($view_folder.$file))
		{
			$path = $view_folder.$file;
			$file_exists = TRUE;
		}

		if ( ! $file_exists)
		{
			throw new \Exception('Unable to load the requested file: '.$view_folder.$file, 16);
		}

		extract($vars);

		#region buffering...
		ob_start();

		include($path);

		$buffer = ob_get_contents();
		@ob_end_clean();
		#endregion

		if ($return === TRUE)
		{
		return $buffer;
		}

		$this->views_as_string .= $buffer;
	}

	public function get_css_files()
	{
		return $this->css_files;
	}

	public function get_js_files()
	{
		return $this->js_files;
	}

	/**
	 *
	 * Load the language strings array from the language file
	 */
	private function _load_language()
	{
		$default_language = Config::get('imagecrud.default_language');

		if($this->language === null)
		{
			$this->language = strtolower($default_language);
		}

		include($this->default_language_path.'/'.$this->language.'.php');

		foreach ($lang as $handle => $lang_string) {
			if (!isset($this->lang_strings[$handle])) {
				$this->lang_strings[$handle] = $lang_string;
			}
		}

	}

	/**
	 *
	 * Just an alias to get_lang_string method
	 * @param string $handle
	 */
	public function l($handle)
	{
		return $this->get_lang_string($handle);
	}

	/**
	 *
	 * Get the language string of the inserted string handle
	 * @param string $handle
	 */
	public function get_lang_string($handle)
	{
		return $this->lang_strings[$handle];
	}

	/**
	 *
	 * Simply set the language
	 * @example english
	 * @param string $language
	 */
	public function set_language($language)
	{
		$this->language = $language;

		return $this;
	}

	protected function get_layout()
	{
		$js_files = $this->get_js_files();
		$css_files =  $this->get_css_files();

		return array('output' => $this->views_as_string, 'js_files' => $js_files, 'css_files' => $css_files);
	}

	protected function _upload_file($upload_dir) {

		$reg_exp = '/(\\.|\\/)(gif|jpeg|jpg|png)$/i';

		$options = array(
				'upload_dir' 		=> $this->public_path.$upload_dir.'/',
				'param_name'		=> 'qqfile',
				'upload_url'		=> base_url().$upload_dir.'/',
				'accept_file_types' => $reg_exp
		);
		$upload_handler = new ImageUploadHandler($options);
		$uploader_response = $upload_handler->post();

		if(is_array($uploader_response))
		{
			foreach($uploader_response as &$response)
			{
				unset($response->delete_url);
				unset($response->delete_type);
			}

			$upload_response = $uploader_response[0];
		} else {
			$upload_response = false;
		}

		if (!empty($upload_response)) {

			$filename = $upload_response->name;

			$path = $this->public_path.$upload_dir.'/'.$filename;

			/* Resizing to 1024 x 768 if its required */
			list($width, $height) = getimagesize($path);
			if($width > 1024 || $height > 768)
			{
				$imageMoo = new ImageMoo();
				$imageMoo->load($path)->resize(1024,768)->save($path,true);
			}
			/* ------------------------------------- */

			return $filename;
		} else {
			return false;
		}

    }

    protected function _changing_priority($post_array)
    {
    	$counter = 1;
		foreach($post_array as $photo_id)
		{
			DB::table($this->table_name)
				->where($this->primary_key,'=',$photo_id)
				->update(array($this->priority_field => $counter));

			$counter++;
		}
    }

    protected function _insert_title($primary_key, $value)
    {
    	DB::table($this->table_name)
    		->where($this->primary_key, "=", $primary_key)
    		->update(array($this->title_field => $value));
    }

    protected function _insert_table($file_name, $relation_id = null)
    {
    	$insert = array($this->url_field => $file_name);
    	if (!empty($relation_id)) {
    		$insert[$this->relation_field] = $relation_id;
    	}

    	DB::table($this->table_name)
    		->insert($insert);
    }

    protected function _delete_file($id)
    {
    	$sql_build = DB::table($this->table_name);
    	$sql_build->where($this->primary_key, '=' ,$id);

    	$result = $sql_build->first();

        $image_file_path = $this->public_path.$this->image_path.'/'.$result->{$this->url_field};
        $thumbnail_file_path = $this->public_path.$this->image_path.'/'.$this->thumbnail_prefix.$result->{$this->url_field};

        if (file_exists($image_file_path)) {
            unlink($image_file_path);
        }

        if (file_exists($thumbnail_file_path)) {
            unlink($thumbnail_file_path);
        }

    	$delete_sql = DB::table($this->table_name);
    	$delete_sql->where($this->primary_key,'=',$id);
    	$delete_sql->delete();
    }

    protected function _get_delete_url($value)
    {
    	$rsegments_array = Request::segments();
    	return site_url($rsegments_array[0].'/'.$rsegments_array[1].'/delete_file/'.$value);
    }

    protected function _get_photos($relation_value = null)
    {
    	$sql_build = DB::table($this->table_name);
    	if(!empty($this->priority_field))
    	{
    		$sql_build->orderBy($this->priority_field);
    	}
    	if(!empty($relation_value))
    	{
    		$sql_build->where($this->relation_field, '=' ,$relation_value);
    	}

    	$results = $sql_build->get();

    	$thumbnail_url = !empty($this->thumbnail_path) ? $this->thumbnail_path : $this->image_path;

    	foreach($results as $num => $row)
    	{
			if (!file_exists($this->public_path.$this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field})) {
				$this->_create_thumbnail($this->image_path.'/'.$row->{$this->url_field}, $this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field});
			}

    		$results[$num]->image_url = base_url().$this->image_path.'/'.$row->{$this->url_field};
    		$results[$num]->thumbnail_url = base_url().$this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field};
    		$results[$num]->delete_url = $this->_get_delete_url($row->{$this->primary_key});
    	}

    	return $results;
    }

	protected function _convert_foreign_characters($str_i)
	{
		include($this->default_config_path.'translit_chars.php');
		if ( ! isset($translit_characters))
		{
			return $str_i;
		}
		return preg_replace(array_keys($translit_characters), array_values($translit_characters), $str_i);
	}

	protected function _create_thumbnail($image_path, $thumbnail_path)
	{
		$imageMoo = new ImageMoo();
		$imageMoo->load($this->public_path.$image_path)
			->resize_crop(90,60)
			->save($this->public_path.$thumbnail_path,true);

	}

	protected function getState()
	{
		$rsegments_array = Request::segments();

		if(isset($rsegments_array[2]) && is_numeric($rsegments_array[2]))
		{
			$upload_url = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/upload_file/'.$rsegments_array[2]);
			$ajax_list_url  = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/'.$rsegments_array[2].'/ajax_list');
			$ordering_url  = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/ordering');
			$insert_title_url  = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/insert_title');

			$state = array( 'name' => 'list', 'upload_url' => $upload_url, 'relation_value' => $rsegments_array[2]);
			$state['ajax'] = isset($rsegments_array[3]) && $rsegments_array[3] == 'ajax_list'  ? true : false;
			$state['ajax_list_url'] = $ajax_list_url;
			$state['ordering_url'] = $ordering_url;
			$state['insert_title_url'] = $insert_title_url;


			return (object)$state;
		}
		elseif( (empty($rsegments_array[2]) && empty($this->relation_field)) || (!empty($rsegments_array[2]) &&  $rsegments_array[2] == 'ajax_list'))
		{
			$upload_url = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/upload_file');
			$ajax_list_url  = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/ajax_list');
			$ordering_url  = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/ordering');
			$insert_title_url  = site_url($rsegments_array[0].'/'.$rsegments_array[1].'/insert_title');

			$state = array( 'name' => 'list', 'upload_url' => $upload_url);
			$state['ajax'] = isset($rsegments_array[2]) && $rsegments_array[2] == 'ajax_list'  ? true : false;
			$state['ajax_list_url'] = $ajax_list_url;
			$state['ordering_url'] = $ordering_url;
			$state['insert_title_url'] = $insert_title_url;

			return (object)$state;
		}
		elseif(isset($rsegments_array[2]) && $rsegments_array[2] == 'upload_file')
		{
			#region Just rename my file
				$new_file_name = '';
				//$old_file_name = $this->_to_greeklish($_GET['qqfile']);
				$old_file_name = $this->_convert_foreign_characters($_GET['qqfile']);
				$max = strlen($old_file_name);
				for($i=0; $i< $max;$i++)
				{
					$numMatches = preg_match('/^[A-Za-z0-9.-_]+$/', $old_file_name[$i], $matches);
					if($numMatches >0)
					{
						$new_file_name .= strtolower($old_file_name[$i]);
					}
					else
					{
						$new_file_name .= '-';
					}
				}
				$file_name = substr( substr( uniqid(), 9,13).'-'.$new_file_name , 0, 100) ;
			#endregion

			$results = array( 'name' => 'upload_file', 'file_name' => $file_name);
			if(isset($rsegments_array[3]) && is_numeric($rsegments_array[3]))
			{
				$results['relation_value'] = $rsegments_array[3];
			}
			return (object)$results;
		}
		elseif(isset($rsegments_array[2]) && isset($rsegments_array[3]) && $rsegments_array[2] == 'delete_file' && is_numeric($rsegments_array[3]))
		{
			$state = array( 'name' => 'delete_file', 'id' => $rsegments_array[3]);
			return (object)$state;
		}
		elseif(isset($rsegments_array[2]) && $rsegments_array[2] == 'ordering')
		{
			$state = array( 'name' => 'ordering');
			return (object)$state;
		}
		elseif(isset($rsegments_array[2]) && $rsegments_array[2] == 'insert_title')
		{
			$state = array( 'name' => 'insert_title');
			return (object)$state;
		}
	}

    public function pre_render()
    {
        $this->default_language_path = __DIR__.'/../../../Resources/languages';
        $this->default_view_folder_path = __DIR__.'/../../../Resources//views/';
        $this->default_config_path =  __DIR__.'/../../../Resources/config/';

        $this->public_path = public_path()."/";
    }

	public function render()
	{
		$this->pre_render();
		
		$this->_load_language();

		$state_info = $this->getState();

		if(!empty($state_info))
		{
			switch ($state_info->name) {
				case 'list':
					$photos = isset($state_info->relation_value) ? $this->_get_photos($state_info->relation_value) : $this->_get_photos();

					$this->_library_view('list.php',array(
						'upload_url' => $state_info->upload_url,
						'insert_title_url' => $state_info->insert_title_url,
						'photos' => $photos,
						'ajax_list_url' => $state_info->ajax_list_url,
						'ordering_url' => $state_info->ordering_url,
						'primary_key' => $this->primary_key,
						'title_field' => $this->title_field,
						'unset_delete' => $this->unset_delete,
						'unset_upload' => $this->unset_upload,
						'has_priority_field' => $this->priority_field !== null ? true : false
					));

					if($state_info->ajax === true)
					{
						@ob_end_clean();
						$tmp = $this->get_layout();
						echo $tmp['output'];
						die();
					}
					return $this->get_layout();
				break;

				case 'upload_file':
					if($this->unset_upload)
					{
						throw new \Exception('This user is not allowed to do this operation', 1);
						die();
					}

					$file_name = $this->_upload_file( $this->image_path);

					if ($file_name !== false) {
						$relation_value = isset($state_info->relation_value) ? $state_info->relation_value : null;
						$this->_create_thumbnail( $this->image_path.'/'.$file_name , $this->image_path.'/'.$this->thumbnail_prefix.$file_name );
						$this->_insert_table($file_name, $relation_value);

						$result = true;
					} else {
						$result = false;
					}

					@ob_end_clean();
					echo json_encode((object)array('success' => $result));

					die();
				break;

				case 'delete_file':
					@ob_end_clean();
					if($this->unset_delete)
					{
						throw new \Exception('This user is not allowed to do this operation', 1);
						die();
					}
					$id = $state_info->id;

					$this->_delete_file($id);
					die();
				break;

				case 'ordering':
					@ob_end_clean();
					$this->_changing_priority($_POST['photos']);
					die();
				break;

				case 'insert_title':
					@ob_end_clean();
					$this->_insert_title($_POST['primary_key'],$_POST['value']);
					die();
				break;
			}
		}

	}

}

function base_url()
{
	return str_replace( "index.php", "", URL::to('/') );
}

function site_url($url = '')
{
	return url($url);
}

function form_open($action='', $extras = '')
{
	return "<form action='$action' $extras>";
}

function form_close()
{
return "</form>";
}
