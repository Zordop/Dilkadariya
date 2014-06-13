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

		}

	}