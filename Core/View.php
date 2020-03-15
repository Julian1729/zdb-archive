<?php

  namespace Core;

  /**
   * View
   */
  class View
  {

    //static var to retain its value
    static $twig = null;

    /**
     * Initialize Twig Object
     * @return void
     */
    public static function initTwig(){
      $twig = &static::$twig;
      if($twig === null){
        //this is the first time calling the function...initialize twig
        $loader = new \Twig_Loader_Filesystem('../App/Views');
        // enable degug mode depending on whether we are in production
        $twigParams = (\App\Config::SHOW_ERRORS) ? array('debug' => true) : array();
        $twig = new \Twig_Environment($loader, $twigParams);
        $twig->addExtension(new \Twig_Extension_Debug());
      }
    }

    /**
    * renderTemplate
    *
    * Render a Twig Template
    */
    public static function renderTemplate( $template, $args = array() ){
      static::initTwig();
      // send uris with arguements
      $args['URI'] = \App\Config::URI;
      // send session variables with session
      $args['SESSION'] = (isset($_SESSION)) ? $_SESSION : array();
      echo static::$twig->render($template, $args);
    }

    /**
     * Get the contents of a rendered template
     * @param  string $template Path to template
     * @param  array  $args Arguments to pass to template
     * @return string Rendered Template HTML
     */
    public static function getRenderedTemplate( $template, $args = array() ){
      static::initTwig();
      // send uris with arguements
      $args['URI'] = \App\Config::URI;
      // send session variables with session
      $args['SESSION'] = $_SESSION;
      return static::$twig->render($template, $args);
    }



  }


?>
