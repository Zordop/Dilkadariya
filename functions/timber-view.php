<?php

/*

$view = new TimberView();
$view->context['post'] = new TimberPost();
$view->cache(600);
echo $timber->render('single.twig');


*/

class TimberView {

	private $cache_mode = TimberLoader::CACHE_USE_DEFAULT;
	private $expires = false;
	private $loader;
	
	public function __construct($context = 'default'){
		$this->context = array();
		if ($context){
			$this->context = apply_filters('timber/context/'.$context, $this->context);
		}
	}

	public function cache($expires = false, $cache_mode = TimberLoader::CACHE_USE_DEFAULT) {
		$this->cache_mode = $cache_mode;
		$this->expires = $expires;
	}

	public function compile($templates, $locations = 'default', $via_render = false){
		$caller = false;
		if ($locations == 'default'){
			$caller = TimberLoader::get_calling_script_dir(2);
		}
        $loader = new TimberLoader($caller);
        if ($locations != 'default'){
        	$loader->set_locations($locations);
        }
        $file = $loader->choose_template($templates);
        $output = '';
        $data = $this->context;
        if (is_null($data)){
            $data = array();
        }
        $action = 'compile';
        if (strlen($file)) {
            if ($via_render){
            	$action = 'render';
            }
            $file = apply_filters('timber/view/'.$action.'/file', $file);
       		$data = apply_filters('timber/view/'.$action.'/context', $data);
            $output = $loader->render($file, $data, $this->expires, $this->cache_mode);
        }
        do_action('timber_compile_done');
        return $output;
	}

	public function compile_string($string){
		$this->loader = new TimberLoader(false);
        $twig = $this->loader->get_twig();
        return $twig->render($string, $this->context);
	}

	public function render_string($string){
		echo $this->compile_string($templates, $locations);
	}

	public function render($templates, $locations){
		echo $this->compile($templates, $locations);
	}

}