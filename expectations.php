<?php

/**
 * The default snap expectation
 */
abstract class Snap_Expectation {
    protected $data;

    /**
     * Constructor which holds internal data
     * @param mixed $data the incoming data
     */
    public function __construct($data = null) {
        $this->data = $data;
    }
    
    abstract public function match($statement);
}


/**
 * An anything expectation, will always match
 */
class Snap_Anything_Expectation extends Snap_Expectation {
    public function match($statement) { return true; }
}


/**
 * Basic Equals Expectation
 */
class Snap_Equals_Expectation extends Snap_Expectation {

    /**
     * matches an incoming item against the constructor data
     * @param mixed $in the incoming item to match
     * @return bool true if matched
     */
    public function match($in) {
        return ($in == $this->data) ? true : false;
    }
}

/**
 * Snap Same Expectation
 */
class Snap_Same_Expectation extends Snap_Expectation {

    /**
     * matches against a strict equals ===
     */
    public function match($in) {
        return ($in === $this->data) ? true : false;
    }
}


/**
 * A regular expression expectation
 */
class Snap_Regex_Expectation extends Snap_Expectation {

    /**
     * Matches against the regex
     * @see Snap_Expectation::match()
     */
    public function match($statement) {
        return (preg_match($this->data, $statement)) ? true : false;
    }
}




?>