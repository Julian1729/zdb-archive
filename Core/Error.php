<?php

  namespace Core;

  /**
   * Error and Exception handler
   */
  class Error
  {

    /**
    * Error handler. Converts all errors to Excpetions by
    * throwing an ErrorException
    *
    */
    public static function errorHandler($level, $message, $file, $line){
      if(error_reporting() !== 0){
        throw new \ErrorException($message, 0, $level, $file, $line);
      }
    }



    /**
    * Exception handler
    *
    * @param Exception $exception The Exception
    * @return void
    */
    public static function exceptionHandler($exception){

      //Code 404 (not found) or 500 (general error)
      $code = $exception->getCode();
      if($code != 404){
        $code = 500;
      }

      //if \App\Config::SHOW_ERRORS is true, show details, if not log them
      if (\App\Config::SHOW_ERRORS) {
        //output
        echo "<h1>Fatal Error</h1>";
        echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
        echo "<p>Message: '" . $exception->getMessage() . "'</p>";
        echo "<p>Stack Trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
        echo "<p>Thrown In'" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
      }else {
        //set the response code
        http_response_code($code);

        //set message to be logged
        $message = "Uncaught exception: '" . get_class($exception) . "'";
        $message .= " with message '" . $exception->getMessage() . "'";
        $message .= "\nStack trace: " . $exception->getTraceAsString();
        $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

        static::logError($message);

        echo "<h1>An error Occurred</h1>";
        if ($code == 404) {
          echo "<h1>404 Page Not Found</h1>";
        }else {
          echo "<h1>500 Error Occured</h1>";
        }

        //View::renderTemplate($code . '.html');

      }
    }

    public static function logError($message){
      $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
      ini_set('error_log', $log);
      error_log($message);
    }

  }


?>
