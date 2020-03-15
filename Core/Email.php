<?php

namespace Core;

use PHPMailer\PHPMailer\PHPMailer;
use Core\Model;
use \App\Models\Admin;

/**
 * Email class to send emails using PHPMailer Class
 */
class Email
{

  /**
   * PHPMailer Object
   * @var object
   */
  protected static $mail = null;

  /**
   * SMTP Host
   * @var string
   */
  protected static $email_host = "smtp.gmail.com";
  /**
   * Port Number
   * @var integer
   */
  protected static $email_port = 587;
  /**
   * Email Account Username
   * @var string
   */
  protected static $email_username = "hernandez.julian17@gmail.com";
  /**
   * Email Account Password
   * @var string
   */
  protected static $email_password = "Jehovah1106";
  /**
   * Name to be sent with email
   * @var string
   */
  protected static $email_from_name = "Julian Hernandez";

  /**
   * Instantiate PHPMailer Class and set needed SMTP variables
   * @return void
   */
  static function setup()
  {
    $val = array(
      'host' => Admin::getCol('email_host'),
      'user' => Admin::getCol('email_username'),
      'pass' => Admin::getCol('email_password'),
      'port' => Admin::getCol('email_port'),
      'name' => Admin::getCol('email_from_name')
    );
    static::$mail = new PHPMailer;

    //Enable SMTP debugging.
    //static::$mail->SMTPDebug = 3;
    //Set PHPMailer to use SMTP.
    static::$mail->isSMTP();
    //Set SMTP host name
    static::$mail->Host = $val['host'];
    //Set this to true if SMTP host requires authentication to send email
    static::$mail->SMTPAuth = true;
    //Provide username and password
    static::$mail->Username = $val['user'];
    static::$mail->Password = $val['pass'];
    //If SMTP requires TLS encryption then set it
    static::$mail->SMTPSecure = "tls";
    //Set TCP port to connect to
    static::$mail->Port = $val['port'];

    static::$mail->From = $val['user'];
    static::$mail->FromName = $val['name'];
  }

  /**
   * Send Test Email
   */
   public static function test(){
    //  if(static::$mail === null){
    //    static::setup();
    //  }
     static::setup();
     static::$mail->addAddress("jhernandez@student.mastccs.org", "Julian Hernandez");

     static::$mail->Subject = "succesfull test! | " . date('h : i : s');
     static::$mail->Body = "<p> \Core\Email Class Test! </p>";
     static::$mail->AltBody = "This is the plain text version of the email content";

     if(!static::$mail->send())
     {
         echo "Mailer Error: " . $mail->ErrorInfo;
     }
     else
     {
         echo "Message has been sent successfully";
     }
   }


   /**
    * Send a generic email
    * @param  string $to Receipient's email address
    * @param  string $toName  Receipient's name
    * @param  string $subject Subject line
    * @param  string $body Body of email...can be html
    * @param  boolean $html Is the body of the email HTML? default false;
    * @return boolean False on failure
    */
  public static function sendEmail($to = null, $toName = '"To Name"', $subject = '', $body, $html = false){
    try {
      if(static::$mail === null){
        static::setup();
      }
      static::$mail->addAddress($to, "Julian Hernandez");

      static::$mail->isHTML($html);

      static::$mail->Subject = $subject;
      static::$mail->Body = $body;

      return static::$mail->send();
    } catch (Exception $e) {
      throw new \Exception($mail->ErrorInfo);
    }
  }

  public static function sendForgotEmail($email, $token){
    // build url
    $url = \App\Config::FORGOT_PASSWORD_URI . $token;
    // get html email
    $htmlString = View::getRenderedTemplate('Emails/ForgotPassword.html', array(
      'name' => $_SESSION['name'],
      'url' => $url
    ));
    static::sendEmail(
      $_SESSION['email'],
      // to name
      $_SESSION['name'] . ' Congregation',
      // subject
      $_SESSION['name'] . ' ZDB Password Reset',
      // html body
      $htmlString,
      true
    );

  }

}


?>
