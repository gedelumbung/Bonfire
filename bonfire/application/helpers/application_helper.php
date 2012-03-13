<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Copyright (c) 2011 Lonnie Ezell

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

/*
	File: Application Helper

	Pulls together various helper functions from across the core modules
	to ease editing and minimize physical files that need loaded.
*/

//--------------------------------------------------------------------

//--------------------------------------------------------------------
// !USER HELPERS
//--------------------------------------------------------------------

/*
	Function: gravatar_link()

	Creates an image link based on Gravatar for the specified email address.
	It will default to the site's generic image if none is found for
	the user.

	Note that if gravatar does not have an image that matches the criteria,
	it will return a link to an image under *your_theme/images/user.png*.
	Also, by explicity omitting email you're denying http-req to gravatar.com

	Parameters:
		$email	- The email address to check for. If null, defaults to theme img.
		$size	- The width (and height) of the resulting image to grab.
		$alt	- Alt text to be put in the link tag.
		$title	- The title text to be put in the link tag.
		$class	- Any class(es) that should be assigned to the link tag.
		$id		- The id (if any) that shoudl put in the link tag.

	Return:
		The resulting image tag.
 */
if ( ! function_exists('gravatar_link') )
{
	function gravatar_link($email=null, $size=48, $alt='', $title='', $class=NULL, $id=NULL)
	{
		// Set our default image based on required size.
		$default_image = Template::theme_url('images/user.png');

		// Set our minimum site rating to PG
		$rating = 'PG';

		// Border color
		$border = 'd6d6d6';

		// If email null, means we don't want gravatar.com HTTP request
		if ( $email )
		{

			// Check if HTTP or HTTPS Request should be used
			$http_protocol = ( isset($_SERVER['HTTPS']) ) ? 'https://secure.' : 'https://www.';

			// URL for Gravatar
			$gravatarURL =  $http_protocol . "gravatar.com/avatar.php?gravatar_id=%s&amp;default=%s&amp;size=%s&amp;border=%s&amp;rating=%s";
			$avatarURL = sprintf(
								$gravatarURL,
								md5($email),
								$default_image,
								$size,
								$border,
								$rating
							);
		} else {
			$avatarURL = $default_image;
		}

		$id    = ( $id    === NULL ) ? '' : ' id="' . $id . '" ';
		$class = ( $class === NULL ) ? '' : ' class="' . $class . '" ';

		return '<img src="'. $avatarURL .'" width="'.	$size .'" height="'. $size . '" alt="'. $alt .'" title="'. $title .'" '. $class . $id . ' />';
	}
}
//---------------------------------------------------------------

/*
	Method: logit()

	Logs an error to the Console (if loaded) and to the log files.

	Parameters:
		$message	- The string to write to the logs.
		$level		- The log level, as per CI log_message method.
*/
if ( ! function_exists('logit') )
{
	function logit($message='', $level='debug')
	{
		if (empty($message))
		{
			return;
		}

		if (class_exists('Console'))
		{
			Console::log($message);
		}

		log_message($level, $message);
	}
}

//--------------------------------------------------------------------
// !MODULE HELPERS
//--------------------------------------------------------------------

/*
	Function: module_folders();

	Returns an array of the folders that modules are allowed to be stored in.
	These are set in *bonfire/application/third_party/MX/Modules.php*.
*/
if ( ! function_exists('module_folders') )
{
	function module_folders()
	{
		return array_keys(modules::$locations);
	}
}

//--------------------------------------------------------------------

/*
	Function: module_list()

	Returns a list of all modules in the system.
*/
if ( ! function_exists('module_list') )
{
		function module_list($exclude_core=false)
		{
				if (!function_exists('directory_map'))
				{
						$ci =& get_instance();
						$ci->load->helper('directory');
				}

				$map = array();

				foreach (module_folders() as $folder)
				{
						// If we're excluding core modules and this module
						// is in the 'core_modules' folder... ignore it.
						if ($exclude_core && strpos($folder, 'core_modules') !== false)
						{
								continue;
						}

						$map = array_merge($map, directory_map($folder, 1));
				}

				// Clean out any html or php files
				if ($count = count($map))
				{
						for ($i=0; $i < $count; $i++)
						{
								if (strpos($map[$i], '.html') !== false || strpos($map[$i], '.php') !== false)
								{
										unset($map[$i]);
								}
						}
				}

				return $map;
		}
}

//--------------------------------------------------------------------

/*
	Function: module_controller_exists()

	Determines whether a controller exists for a module.

	Parameters:
	$controller	- The name of the controller to looke for (without the .php)
	$module		- The name of module to look in.

	Return:
		true/false
 */
if ( ! function_exists('module_controller_exists') )
{
	function module_controller_exists($controller=null, $module=null)
	{
		if (empty($controller) || empty($module))
		{
			return false;
		}

		// Look in all module paths
		foreach (module_folders() as $folder)
		{
			if (is_file($folder . $module .'/controllers/'. $controller .'.php'))
			{
				return true;
			}
		}

		return false;
	}
}

//--------------------------------------------------------------------

/*
	Function: module_file_path()

	Finds the path to a module's file.

	Parameters:
		$module	- The name of the module to find.
		$folder	- The folder within the module to search for the file (ie. controllers)
		$file	- The name of the file to search for.

	Returns:
		A string with the full path to the file.
*/
if ( ! function_exists('module_file_path') )
{
	function module_file_path($module=null, $folder=null, $file=null)
	{
		if (empty($module) || empty($folder) || empty($file))
		{
			return false;
		}

		foreach (module_folders() as $module_folder)
		{
			$test_file = $module_folder . $module .'/'. $folder .'/'. $file;

			if (is_file($test_file))
			{
				return $test_file;
			}

		}

	}
}

//--------------------------------------------------------------------

/*
	Function module_path()

	Returns the path to the module and it's specified folder.

	Parameters:
		$module	- The name of the module (must match the folder name)
		$folder	- The folder name to search for. (Optional)

	Returns:
		A string with the path, relative to the front controller.
*/
if ( ! function_exists('module_path') )
{
	function module_path($module=null, $folder=null)
	{
		foreach (module_folders() as $module_folder)
		{
			if (is_dir($module_folder . $module))
			{
				if (!empty($folder) && is_dir($module_folder . $module .'/'. $folder))
				{
					return $module_folder . $module .'/'. $folder;
				} else {
					return $module_folder . $module .'/';
				}
			}
		}
	}
}

//--------------------------------------------------------------------

/*
	Function: module_files()

	Returns an associative array of files within one or more modules.

	Parameters:
		$module_name	- If not NULL, will return only files from that module.
		$module_folder	- if not NULL, will return only files within that folder of each module (ie 'views')
		$exclude_core	- Whether we should ignore all core modules.

	Return:
		An associative array, like: array('module_name' => array('folder' => array('file1', 'file2')))
*/
if ( ! function_exists('module_files') )
{
	function module_files($module_name=null, $module_folder=null, $exclude_core=false)
	{
		if (!function_exists('directory_map'))
		{
			$ci =& get_instance();
			$ci->load->helper('directory');
		}

		$files = array();

		foreach (module_folders() as $path)
		{
			// If we're ignoring core modules and we find the core_module folder... skip it.
			if ($exclude_core === true && strpos($path, 'core_modules') !== false)
			{
				continue;
			}

			if (!empty($module_name) && is_dir($path . $module_name))
			{
				$path = $path . $module_name;
				$modules[$module_name] = directory_map($path);
			}
			else
			{
				$modules = directory_map($path);
			}

			// If the element is not an array, we know that it's a file,
			// so we ignore it, otherwise it is assumbed to be a module.
			if (!is_array($modules) || !count($modules))
			{
				continue;
			}

			foreach ($modules as $mod_name => $values)
			{
				if (is_array($values))
				{
					// Add just the specified folder for this module
					if (!empty($module_folder) && isset($values[$module_folder]) && count($values[$module_folder]))
					{
						$files[$mod_name] = array(
							$module_folder	=> $values[$module_folder]
						);
					}
					// Add the entire module
					else if (empty($module_folder))
					{
						$files[$mod_name] = $values;
					}
				}
			}
		}

		return count($files) ? $files : false;
	}
}

//--------------------------------------------------------------------

/*
	Function: module_config()

	Returns the 'module_config' array from a modules config/config.php file.

	Parameters:
		$module_name	- The name of the module.
		$return_full	- If true, will return the entire config array.
						  If false, will return only the 'module_config' portion.

	The 'module_config' contains more information about a module, and even provide
	enhanced features within the UI. All fields are optional.

	$config['module_config'] = array(
		'name'			=> 'Blog', 			// The name that is displayed in the UI
		'description'	=> 'Simple Blog',	// May appear at various places within the UI
		'author'		=> 'Your Name',		// The name of the module's author
		'homepage'		=> 'http://...',	// The module's home on the web
		'version'		=> '1.0.1',			// Currently installed version
		'menu'			=> array(			// A view file containing an <ul> that will be the sub-menu in the main nav.
			'context'	=> 'path/to/view'
		)
	);

	Author:
		Liam Rutherford (http://www.liamr.com)

	Returns:
		An array of config settings, or an empty array if empty/not found.
*/
if ( ! function_exists('module_config') )
{
	function module_config($module_name=null, $return_full=false)
	{
		$config_param = array();

		$config_file = module_file_path($module_name, 'config', 'config.php');

		if (file_exists($config_file))
		{
			include($config_file);

			/* Check for the optional module_config and serialize if exists*/
			if (isset($config['module_config']))
			{
				$config_param =$config['module_config'];
			}
			else if ($return_full === true && isset($config) && is_array($config))
			{
				$config_param = $config;
			}
		}

		return $config_param;
	}
}
//--------------------------------------------------------------------


/*
	Function: dump()

	Outputs the given variables with formatting and loation.

	Huge props out to Phil Sturgeon for this one (http://philsturgeon.co.uk/blog/2010/09/power-dump-php-applications)

	To use, pass in any number of variables as arguments.
*/
if ( ! function_exists('dump') )
{
	function dump()
	{
		list($callee) = debug_backtrace();
		$arguments = func_get_args();
		$total_arguments = count($arguments);

		echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px">';
		echo '<legend style="background:lightgrey; padding:5px;">'.$callee['file'].' @ line: '.$callee['line'].'</legend><pre>';

		$i = 0;
		foreach ($arguments as $argument)
		{
			echo '<br/><strong>Debug #'.(++$i).' of '.$total_arguments.'</strong>: ';

			if ( (is_array($argument) || is_object($argument)) && count($argument))
			{
				print_r($argument);
			}
			else
			{
				var_dump($argument);
			}
		}

		echo "</pre>";
		echo "</fieldset>";
	}
}
//--------------------------------------------------------------------

/*
	Function: e()

	A convenience function to make sure your output is safe to display.
	Helps to defeat XSS attacks by running the text through htmlentities().

	Should be used anywhere you are displaying user-submitted text.
*/
if ( ! function_exists('e') )
{
	function e($str)
	{
		echo htmlentities($str, ENT_QUOTES, 'UTF-8');
	}
}
//--------------------------------------------------------------------

/*
	Function: array_implode()

	Implode an array with the key and value pair giving a glue,
	a separator between pairs and the array to implode.

	Example:
		// Encode Query Strings
		$query = url_encode( array_implode( '=', '&', $array ) );

	Parameters:
		$glue		- The glue between key and value.
		$separator	- Separator between pairs.
		$array		- The array to implode.

	Returns:
		A string with the combined elements.
*/
if ( ! function_exists('array_implode') )
{
	function array_implode($glue, $separator, $array)
	{
		if ( ! is_array( $array ) )
		{
			return $array;
		}

		$string = array();

		foreach ( $array as $key => $val )
		{
			if ( is_array( $val ) )
			{
				$val = implode( ',', $val );
			}

			$string[] = "{$key}{$glue}{$val}";
		}

		return implode( $separator, $string );
	}
}
//--------------------------------------------------------------------
if ( ! function_exists('obj_value') )
{
	function obj_value($obj, $key, $type='text', $value=0)
	{
		if (isset($obj->$key))
		{
			switch ($type)
			{
				case 'checkbox':
				case 'radio':
					if ($obj->$key == $value)
					{
						return 'checked="checked"';
					}
					break;
				case 'select':
					if ($obj->$key == $value)
					{
						return 'selected="selected"';
					}
					break;
				case 'text':
				default:
					return $obj->$key;
			}
		}

		return null;
	}
}
//--------------------------------------------------------------------

/*
	Function: iif()

	If / Else Statement wrapped in one function, If $expression = true then $returntrue else $returnfalse.


	Example:
		// Check if expression is ok then returns value.
		echo iff ( form_error('formfield'), 'error' , 'success' );


	Parameters:
		$expression  - IF statement to check against.
		$returntrue	 - What to return if expression is true.
		$returnfalse - What to return if expression is false.

	Returns:
		Mixed return based on expression
*/
if ( ! function_exists('iif') )
{
	function iif($expression, $returntrue, $returnfalse = '')
	{
		return ( $expression == 0 ) ? $returnfalse : $returntrue;
	}
}
