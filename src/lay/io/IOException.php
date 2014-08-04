<?php
namespace lay\io;

/**
 * Exception class thrown when a filesystem operation failure happens
 *
 * @author Lay Li
 */
class IOException extends \RuntimeException {
    private $path;
    public function __construct($message, $code = 0,\Exception $previous = null, $path = null) {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }
    
    /**
     *
     * @ERROR!!!
     *
     */
    public function getPath() {
        return $this->path;
    }
}
?>
