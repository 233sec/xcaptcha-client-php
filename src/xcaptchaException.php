<?php
class xcaptchaException extends \Exception {
    protected $data = null;

    /**
     * __construct
     * @param  string           $message
     * @param  int              $code
     * @param  array|object     $data
     * @return  \xcaptchaException
     */
    public function __construct($message, $code, $data = null) {
        if($data !== null)$this->data = $data;
        parent::__construct($message, $code);
    }

    /**
     * getData
     * @return array|object
     */
    public function getData() {
        return $this->data;
    }

}