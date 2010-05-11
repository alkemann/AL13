<?php

namespace al13_helpers\tests\cases;

use \al13_helpers\extensions\helper\Lists;
use \lithium\tests\mocks\template\helper\MockFormRenderer;

class ListsTest extends \lithium\test\Unit {

	public function setUp() {
		$this->lists = new Lists(array('context' => new MockFormRenderer()));
	}

	public function stestList() {
		$this->lists->add('main', array('Home','/'));
		$result = $this->lists->generate('main');

		$this->assertTags($result, array(
			array('li' => array()),
			array('input' => array('type' => 'text', 'name' => 'name')),
			'/li',
			array('li' => array()),
			array('label' => array('for' => 'present')),
				'Present',
			'/label',
			array('input' => array('type' => 'checkbox', 'name' => 'present')),
			'/li',
		));
	}

    function testNestedWithOne() {
    	$this->lists->add('main', array('Home','/',array('title' => 'Go Home')));
    	$result = $this->lists->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
	    	array('a' => array('href' => '/', 'title' => 'Go Home')) , 'Home', '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testNestedWithTwo() {
    	$this->lists->add('main', array('Home','/'));
    	$this->lists->add('main', array('About','/about'));
    	$result = $this->lists->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li', array('a' => array('href'=>'/', 'title' => 'Home'))		 , 'Home' , '/a', '/li',
	    	'<li', array('a' => array('href'=>'/about', 'title' => 'About')) , 'About' , '/a', '/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testUlAttributes() {
    	$this->lists->add('main', array('Home','/'));
    	$result = $this->lists->generate('main', array(
    		'id' 	=> 'menu',
    		'class'	=> 'nese'
    	));
    	$expected = array(
	    	array('ul' => array('id'=>'menu', 'class'=>'nese')),
	    	'<li',
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testAattributes() {
    	$this->lists->add(
    		'main',
    		array(
    			'Home',
    			'/',
    			array('id' => 'home', 'class' => 'homeclass', 'style' => 'text-decoration:none;')
    		)
    	);
    	$result = $this->lists->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
    			'<li',
	    			array('a' => array('href'=>'/', 'title' => 'Home','id'=>'home', 'class'=>'homeclass', 'style' => 'text-decoration:none;')),
	    				'Home',
	    			'/a',
	    		'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);

    }

    function testLiAttributes() {
    	$this->lists->add('main', array('Home','/'),
    		array('li' => array('id' => 'homeli', 'class' => 'homeliclass', 'style' => 'width:100%'))
    	);
    	$result = $this->lists->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	array('li' => array('id'=>'homeli', 'class'=>'homeliclass', 'style' => 'width:100%')),
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testADiv() {
    	$this->lists->add('main', array('Home','/'),array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')));
    	$result = $this->lists->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
    		array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')),
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a',
	    	'/div',
	    	'/li',
	    	'/ul',
    	);
    	$this->assertTags($result, $expected);

    }

    function testUlDiv() {
    	$this->lists->add('main', array('Home','/'));
    	$result = $this->lists->generate('main',array('div' => TRUE));
    	$expected = array(
    		'<div',
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a',
	    	'/li',
	    	'/ul',
	    	'/div'
    	);
    	$this->assertTags($result, $expected);
    	$result = $this->lists->generate('main',array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')));
    	$expected = array(
    		array('div' => array('id'=>'divven','class'=>'divs','style'=>'width:50%;')),
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
	    	array('a' => array('href'=>'/', 'title' => 'Home')) , 'Home' , '/a',
	    	'/li',
	    	'/ul',
	    	'/div'
    	);
    	$this->assertTags($result, $expected);
    }

    function testAllAttributes() {
    	$this->lists->add('main', array('Home', '/', array('class' => 'link')),
    		array(
    			'div' => array('id'=>'diven','class'=>'divs','style'=>'width:50%;'),
    			'li' => array('id'=>'mainli','class'=>'lis','style'=>'width:100%;')
    		));
    	$this->lists->add('main', array('About', '/about', array('title'=>'About us', 'style' => 'display:block;')),
    		array(
    			'div' => array('id'=>'divto','class'=>'divs','style'=>'width:50%;'),
    			'li' => array('class'=>'lis','style'=>'width:100%;')
    		));
    	$this->lists->add('main', array('Words',	array('controller'=>'words','action'=>'index'), array('class'=>'link')),
    		array(
    			'div' => array('id'=>'divtre','class'=>'divs','style'=>'width:50%;'),
    			'li' => array('class'=>'lis','style'=>'width:100%;')
    		));
    	$result = $this->lists->generate('main', array(
    		'id'    => 'menu',
    		'class' => 'uls',
    		'ul'    => array('class' => 'uls'),
    		'div'   => array('id' => 'menudiv', 'style' => 'width:800px')
    	));
    	$expected = array(
    		array('div' => array('id' => 'menudiv', 'style' => 'width:800px')),
    			array('ul' => array('class' => 'uls','id' => 'menu')),

    				array('li' => array('id' => 'mainli', 'class'=>'lis','style'=>'width:100%;')),
    					array('div' => array('id' => 'diven', 'class'=>'divs','style'=>'width:50%;')),
	   	 					array('a' => array('href'=>'/', 'class'=> 'link', 'title' => 'Home')) , 'Home' , '/a',
	    				'/div',
	    			'/li',

    				array('li' => array('class'=>'lis','style'=>'width:100%;')),
    					array('div' => array('id' => 'divto', 'class'=>'divs','style'=>'width:50%;')),
	   	 					array('a' => array('href'=>'/about', 'title' => 'About us', 'style' => 'display:block;')) , 'About' , '/a',
	    				'/div',
	    			'/li',

    				array('li' => array('class'=>'lis','style'=>'width:100%;')),
    					array('div' => array('id' => 'divtre', 'class'=>'divs','style'=>'width:50%;')),
	   	 					array('a' => array('href'=>'/words/', 'class'=> 'link', 'title' => 'Words')) , 'Words' , '/a',
	    				'/div',
	    			'/li',

	    		'/ul',
	   	 	'/div',
    	);
    	$this->assertTags($result, $expected);

    }

    function testFunkyURL() {
    	$this->lists->add('main', array('About us','/pages/about/me',array('title' => 'About')));
    	$this->lists->add('main', array('Example','http://example.org'));
    	$this->lists->add('main', array('delete',array('controller'=>'users','action'=>'delete',4,'admin'=>true)));
    	$result = $this->lists->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'<li',
	    	array('a' => array('href' => '/pages/about/me', 'title' => 'About')) , 'About us', '/a',
	    	'/li',
	    	'<li',
	    	array('a' => array('href' => 'http://example.org', 'title' => 'Example')) , 'Example', '/a',
	    	'/li',
	    	'<li',
	    	array('a' => array('href' => '/admin/users/delete/4', 'title' => 'delete')) , 'delete', '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testAddElement() {
    	$this->lists->addElement('main','<p>Stuff</p>');
    	$this->lists->addElement('main','<div><p>stuff</p></div>');
    	$result = $this->lists->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    		'<li',
		    		'<p',
		    			'Stuff',
		    		'/p',
	    		'/li',
	    		'<li',
	    			'<div',
		    			'<p',
	    					'stuff',
		    			'/p',
	    			'/div',
	    		'/li',
    		'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testAddElementToSubMenu() {
    	$this->lists->addElement(array('main','sub','subsub'),'<p>Stuff</p>');
    	$this->lists->addElement(array('main','sub'),'<div><p>stuff</p></div>');
    	$result = $this->lists->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    		'<li',
			    	array('ul' => array('class' => 'menu_sub')),
			    		'<li',
					    	array('ul' => array('class' => 'menu_subsub')),
					    		'<li',
						    		'<p',
						    			'Stuff',
						    		'/p',
					    		'/li',
					    	'/ul',
			    		'/li',
			    		'<li',
			    			'<div',
				    			'<p',
			    					'stuff',
				    			'/p',
			    			'/div',
			    		'/li',
			    	'/ul',
	    		'/li',
    		'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testOneLevelTarget() {
    	$this->lists->add('sub', array('Home','/',array('title' => 'Go Home')));
    	$result = $this->lists->generate('sub');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_sub')),
	    	'<li',
	    	array('a' => array('href' => '/', 'title' => 'Go Home')) , 'Home', '/a',
	    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    	$result = $this->lists->generate('main');
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testTwoLevelTarget() {
    	$this->lists->add('main', array('Home','/'));
    	$this->lists->add(array('main', 'sub'), array('About','/about'));
    	$result = $this->lists->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
		    	'<li',
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')),
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testTwoLevelMultiTarget() {
    	$this->lists->add('main', array('Home','/'));
    	$this->lists->add(array('main', 'sub'), array('About','/about'));
    	$this->lists->add(array('main', 'side'), array('Contact','/contact'));
    	$result = $this->lists->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
		    	'<li',
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')),
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_side')),
		    			'<li',
		    				array('a' => array('href' => '/contact', 'title' => 'Contact')) , 'Contact', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testFourLevelMultiTarget() {
    	$this->lists->add('main', array('Home','/'));
    	$this->lists->add(array('main', 'sub'), array('About','/about'));
    	$this->lists->add(array('main', 'sub','subsub'), array('About','/about'));
    	$this->lists->add(array('main', 'side'), array('Home','/'));
    	$this->lists->add(array('main', 'side', 'sub'), array('About','/about'));
    	$this->lists->add(array('main', 'side', 'sub', 'sidesubsub'), array('About','/about'));
    	$result = $this->lists->generate();
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main')),
		    	'<li',
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')),
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
				    	'<li',
				    		array('ul' => array('class' => 'menu_subsub')),
				    			'<li',
				    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
				    			'/li',
				    		'/ul',
				    	'/li',
		    		'/ul',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_side')),
		    			'<li',
		    				array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    			'/li',
				    	'<li',
				    		array('ul' => array('class' => 'menu_sub')),
				    			'<li',
				    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
				    			'/li',
						    	'<li',
						    		array('ul' => array('class' => 'menu_sidesubsub')),
						    			'<li',
						    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
						    			'/li',
						    		'/ul',
						    	'/li',
				    		'/ul',
				    	'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testTwoLevelId() {
    	$this->lists->add('main', array('Home','/'));
    	$this->lists->add(array('main', 'sub'), array('About','/about'));
    	$result = $this->lists->generate('main',array('id' => 'main_menu_id'));
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main','id' => 'main_menu_id')),
		    	'<li',
		    		array('a' => array('href' => '/', 'title' => 'Home')) , 'Home', '/a',
		    	'/li',
		    	'<li',
		    		array('ul' => array('class' => 'menu_sub')),
		    			'<li',
		    				array('a' => array('href' => '/about', 'title' => 'About')) , 'About', '/a',
		    			'/li',
		    		'/ul',
		    	'/li',
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testForcedErrors() {
    	$this->assertFalse($this->lists->generate('sub'));
    	$this->assertFalse($this->lists->generate('side'));
    	$this->assertFalse($this->lists->generate(array('side')));
    	$this->assertFalse($this->lists->generate(array('side','sub')));
    	$this->assertFalse($this->lists->generate(array('side','sub','subs','sub')));
    	$result = $this->lists->generate('main',array('id' => 'main_menu_id'));
    	$expected = array(
	    	array('ul' => array('class' => 'menu_main','id' => 'main_menu_id')),
	    	'/ul'
    	);
    	$this->assertTags($result, $expected);
    }

    function testFunkyParams() {
    	$this->assertFalse($this->lists->add(34,FALSE));
    	$this->assertFalse($this->lists->add(34,array(FALSE)));
    	$this->assertFalse($this->lists->addElement(array(FALSE,Array('null'),array(FALSE))));
    	$res = $this->lists->generate(34);
    }

}

?>