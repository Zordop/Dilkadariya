<?php

	class TestTimberView extends WP_UnitTestCase {

		function testView(){
			$title = 'TimberView Test Post';
			$post_id = $this->factory->post->create(array('post_title' => $title));
			$post = new TimberPost($post_id);
			$view = new TimberView();
			$view->context['post'] = $post;
			$str = $view->compile('assets/single-post.twig');
			$this->assertEquals('<h1>'.$title.'</h1>', $str);
		}

		function testViewWithCustomLocations(){
			$title = 'TimberView Test Post with Custom Locations';
			$post_id = $this->factory->post->create(array('post_title' => $title));
			$post = new TimberPost($post_id);
			$locations = array(__DIR__.'/assets');
			$view = new TimberView();
			$view->context['post'] = $post;
			$str = $view->compile('single-post.twig', $locations);
			$this->assertEquals('<h1>'.$title.'</h1>', $str);
			$str = $view->compile('assets/single-post.twig');
		}

		function testViewWithMultCustomLocations(){
			$title = 'TimberView Test Post with Mult Custom Locations';
			$post_id = $this->factory->post->create(array('post_title' => $title));
			$post = new TimberPost($post_id);
			$locations = array(__DIR__.'/assets/assets-level-two', __DIR__.'/assets');
			$view = new TimberView();
			$view->context['post'] = $post;
			$str = $view->compile('single-post.twig', $locations);
			$this->assertEquals('I am deeper with <h1>'.$title.'</h1>', $str);
		}

		/**
     	 * @expectedException Twig_Error_Loader
     	*/
    	public function testViewWithCustomLocationsException() {
    		$title = 'TimberView Test Post with Custom Locations';
			$post_id = $this->factory->post->create(array('post_title' => $title));
			$post = new TimberPost($post_id);
			$view = new TimberView();
			$view->context['post'] = $post;
			$str = $view->compile('single-post.twig');
			// should get a load error here since we haven't told Timber to look inside of assets
    	}

	}