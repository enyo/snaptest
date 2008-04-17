<?php

/**
 * Calls a static method on an object. Useful for simplifying call_user_func_array
 * This takes advantage of the fact php static objects can exist as instances
 * and uses call_user_func_array to create a static call based on the class
 * This is especially useful when working with mocked static objects, where
 * the name of the class is unknown
 * @param $obj an instance of an object
 **/
function SNAP_calStatic($obj, $method, $params = array()) {
    
    $params = (is_array($params)) ? $params : array($params);
    $obj_name = (is_object($obj)) ? get_class($obj) : strval($obj);
    
    if (!class_exists($obj_name)) {
        throw new Snap_Exception('Static call on '.$obj_name.' when it is not an object.');
    }

    return call_user_func_array(array($obj_name, $method), $params);
}

/**
 * MockObject Base Class
 * Can create mock objects and assign expectations to them
 */
class Snap_MockObject {
    
    protected $requires_inheritance;
    protected $interface_names;
    protected $mocked_class;
    protected $requires_magic_methods;
    protected $requires_static_methods;
    protected $has_constructor;
    
    public $methods;
    public $signatures;
    public $constructor_args;
    public $counters;
    public $mock_output;

    /**
    * Begin defining a mock object, and setting its expectations
    * if $test is set, it will be the parent test object (tenative? is this good?)
    * @access public
    * @param string $class_name class name to create a mock of
    * @param UnitTest a unit test object that calls it (tenative)
    */
    public function __construct($class_name) {
        // $this->test = $test;
        $this->requires_inheritance = false;
        $this->requires_magic_methods = false;
        $this->requires_static_methods = false;
        $this->has_constructor = false;
        $this->interface_names = array();
        $this->methods = array();
        $this->signatures = array();
        $this->constructor_args = array();
        $this->counters = array();
        $this->mocked_class = $class_name;
        
        // do some quick reflection on the class
        $reflected_class = new ReflectionClass($this->mocked_class);
        if ($reflected_class->isInterface()) {
            $this->interface_names[] = $class_name;
        }
        
        if (count($reflected_class->getInterfaces()) > 0) {
            foreach($reflected_class->getInterfaces() as $k => $interface) {
                $this->interface_names[] = $interface->getName();
            }
        }
    }
    
    /**
     * Specify this mock object requires inheritance (mocks protected methods, copies private)
     * @return MockObject the mock setup object
     */
    public function requiresInheritance() {
        $this->requires_inheritance = true;
        return $this;
    }
    
    /**
     * Get the inheritance required state of the mock object
     * @return boolean TRUE if object requires inheritance
     */
    public function isInherited() {
        return $this->requires_inheritance;
    }
    
    /**
     * Specify this mock object requires a constructor
     * @return MockObject the mock setup object
     **/
    public function requiresConstructor() {
        $this->has_constructor = true;
        return $this;
    }
    
    /**
     * Returns if this mock object has a constructor
     * @return boolean TRUE if object has a constructor
     **/
    public function hasConstructor() {
        return $this->has_constructor;
    }
    
    /**
     * Specify this mock object requires static methods
     * @return MockObject the mock setup object
     **/
    public function requiresStaticMethods() {
        $this->requires_static_methods = true;
        return $this;
    }
    
    /**
     * Returns if the mock object has static methods
     * @return boolean TRUE if object has static methods
     **/
    public function hasStaticMethods() {
        return $this->requires_static_methods;
    }
    
    /**
     * Specify this mock object requires magic methods (has a __call)
     * @return MockObject the mock setup object
     **/
    public function requiresMagicMethods() {
        $this->requires_magic_methods = true;
    }
    
    /**
     * Get the magic method required state of the mock object
     * @return boolean TRUE if the object requires magic methods
     **/
    public function hasMagicMethods() {
        return $this->requires_magic_methods;
    }
    
    /**
     * Specify an interface that this mock object should have
     * @param string $iface the name of an interface to implement
     * @return MockObject the mock setup object
     **/
    public function requiresInterface($iface) {
        $this->interface_names[] = $iface;
    }
    
    /**
     * Get the interfaces for this mock
     * @return array A collection of strings that are all interfaces this mock implements
     **/
    public function getInterfaces() {
        return $this->interface_names;
    }
    
    /**
     * Set the return value for a method call of the specified params
     * @param string $method_name name of method to call
     * @param mixed $return_value the value to return when the method is called
     * @param array $method_params the method parameters to match for this to trigger
     * @return MockObject $this
     */
    public function setReturnValue($method_name, $return_value, $method_params = array()) {
        return $this->setReturnValueAt($method_name, 'default', $return_value, $method_params);
    }
    
    /**
     * Set the return value at a given occurance of $method_name with $method_params
     * @see MockObject::setReturnValue
     * @param int $call_order the order the call is made
     */
    public function setReturnValueAt($method_name, $call_order, $return_value, $method_params = array()) {
        $method_params = $this->handleMethodParameters($method_params);
        $method_signature = $this->getMethodSignature($method_name, $method_params);
        $this->logMethodSignature($method_name, $method_signature, $method_params);
        $this->methods[$method_signature]['returns'][$call_order] = $return_value;
        return $this;
    }
    
    /**
     * Tell the mock object to listen on a given set of params.  This enables tally options
     * any method that has also been tagged with a setReturnValue gets the listener as
     * well.  Use this primarily during setup to prepare to test Expectations
     */
    public function listenTo($method_name, $method_params = array()) {
        $method_params = $this->handleMethodParameters($method_params);
        $method_signature = $this->getMethodSignature($method_name, $method_params);
        $this->logMethodSignature($method_name, $method_signature, $method_params);
        return $this;
    }
        
    /**
     * Record the method name, signature, and expectations
     * @param string $method_name the name of the method to record a signature for
     * @param string $method_signature the signature of the method
     * @param array $method_params the array of expectations that make up this signature
     * @return void
     */
    protected function logMethodSignature($method_name, $method_signature, $method_params) {
        if (!isset($this->signatures[$method_name])) {
            $this->signatures[$method_name] = array();
        }
        
        $this->methods[$method_signature]['count'] = 0;
        $this->methods[$method_signature]['exec_count'] = 0;
        
        $this->signatures[$method_name][$method_signature] = array(
            'params'    => $method_params,
        );
    }
    
    /**
     * Check parameter list, and wrap parameter in a MockObject_Expectation class if necessary
     * @access protected
     * @param array $method_params the method arguments
     * @return array the processed parameter list
     */
    protected function handleMethodParameters($method_params) {
        foreach ($method_params as $idx => $param) {
            if (is_object($param) && ($param instanceof Snap_Expectation)) {
                continue;
            }
        
            if ((substr($param, 0, 1) == '/') && (substr($param, -1, 1) == '/')) {
                $method_params[$idx] = new Snap_Regex_Expectation($param);
            }
            
            $method_params[$idx] = new Snap_Equals_Expectation($param);
        }
        return $method_params;
    }
    
    /**
     * Generate the method's signature based on its params
     * @param string $method_name the name of the method
     * @param array $method_params the paramters for the method
     */
    public function getMethodSignature($method_name, $method_params) {
        $method_signature = $method_name . ' ' . md5(strtolower(serialize($method_params)));
        return $method_signature;
    }
    
    
    /**
     * Get the tally for a specified method name and signature
     * returns the total times a signature was called
     * @param string $method_name the name of the method to tally
     * @param array $method_params the parameters to check against
     */
    public function getTally($method_name, $method_params = array()) {
        $method_params = $this->handleMethodParameters($method_params);
        $method_signature = $this->getMethodSignature($method_name, $method_params);
        return (isset($this->methods[$method_signature]['count'])) ? $this->methods[$method_signature]['count'] : 0;
    }
    
    /**
     * Builds the mock object using eval, and calls the constructor on it.
     * the class signature is unique to the sum of expectations, so an idential object
     * will share the same class signature for its public methods
     * @return Object
     * @throws Snap_UnitTestException
     */
    public function construct() {
        // include once on matching file
        // include_once str_replace('.test', '', __FILE__);
        
        // create the class
        // $class_name = preg_replace('/^test_/', '', get_class($this));

        
        $keys = array_keys($this->methods);
        sort($keys);
        $this->class_signature = 'c'.md5(strtolower(serialize($keys)));
        
        $mock_class = 'mock_'.$this->mocked_class.'_'.$this->class_signature;
        
        // add suffixes if there is inheritance / interface
        if ($this->isInherited()) {
            $mock_class .= '_ri';
        }
        if (count($this->getInterfaces()) > 0) {
            $mock_class .= '_if';
        }
        
        // add iterations until we get a unique name for mock_class
        $mock_class_test = $mock_class;
        $class_counter = 1;
        while (class_exists($mock_class_test)) {
            $mock_class_test = $mock_class . '_' . $class_counter;
            $class_counter++;
        }
        $mock_class = $mock_class_test;
        
        $constructor_method_name = $this->class_signature.'_runConstructor';
        $setmock_method_name = $this->class_signature.'_injectMock';
        $this->constructor_args = func_get_args();
        
        // reflect the class
        $reflected_class = new ReflectionClass($this->mocked_class);
        
        // get the public methods
        $public_methods = array();
        $protected_methods = array();
        foreach ($reflected_class->getMethods() as $method) {
            if ($method->isConstructor() || strtolower($method->getName()) == '__construct') {
                // special constructor stuff here
                $public_methods[] = $method->getName();
                $this->listenTo($method->getName());
                $this->requiresConstructor();
                continue;
            }
            
            // skip all other magic methods
            if (strpos($method->getName(), '__') === 0) {
                if (strtolower($method->getName()) == '__call') {
                    $this->requiresMagicMethods();
                }
                continue;
            }
            
            // skip all final methods
            if ($method->isFinal() && $this->isInherited()) {
                // cannot be overridden
                continue;
            }
            
            // if static methods are required, add a flag
            if ($method->isStatic()) {
                $this->requiresStaticMethods();
            }
            
            if ($method->isPublic()) {
                $public_methods[] = $method->getName();
                $this->listenTo($method->getName());
            }
            if ($method->isProtected() && $this->isInherited()) {
                $protected_methods[] = $method->getName();
                $this->listenTo($method->getName());
            }
        }
        
        // sanity check. Make sure each logged method we put expectations on
        // is in our public or protected list. If not, setting up this object
        // has failed
        foreach ($this->signatures as $method_name => $signature_data) {
            // skip magic methods
            if (strpos($method_name, '__') === 0) {
                continue;
            }
            
            // if in public, we are okay
            if (is_array($public_methods) && in_array($method_name, $public_methods)) {
                continue;
            }
            
            // if in protected, and we are requiring inheritance, we are okay
            if ($this->isInherited() && is_array($protected_methods) && in_array($method_name, $protected_methods)) {
                continue;
            }
            
            // if magic methods are enabled for this class, we are okay
            // we also need to listen to it
            if ($this->hasMagicMethods()) {
                $this->listenTo($method_name);
                if (is_array($public_methods) && !in_array($method_name, $public_methods)) {
                    $public_methods[] = $method_name;
                }
                continue;
            }
            
            // now we're in trouble. We throw an exception, as they
            // called on something that is not mockable
            throw new Snap_UnitTestException('setup_invalid_method', $this->mocked_class.'::'.$method_name.' cannot have expects or return values. It might be private or final.');
        }
        
        // if the class exists with everything intact, no need to eval from here on out
        if (class_exists($mock_class)) {
            return $this->buildClassInstantiation($mock_class, $setmock_method_name, $constructor_method_name);
        }
        
        // for each public method found, build it's code block
        // take the func_get_args of it, create a signature via
        // the reflector.  If there's an exact match, add one to
        // it's call count, then try/catch the method, returning
        // it's value
        $p_methods = '';
        foreach ($public_methods as $method) {
            $p_methods .= $this->buildMethod($method, 'public');
        }
        
        // if this needs inheritance, protected methods may need to be
        // punched out as well
        if ($this->isInherited()) {
            foreach($protected_methods as $method) {
                $p_methods .= $this->buildMethod($method, 'protected');
            }
        }
        
        // start building the class
        $endl = "\n";
        $output  = '';
        
        // class header
        $class_header = 'class '.$mock_class;
        if ($this->isInherited()) {
            $class_header .= ' extends '.$this->mocked_class;
        }
        if (count($this->getInterfaces()) > 0) {
            $class_header .= ' implements '.implode(', ', $this->getInterfaces());
        }
        $class_header .= ' {'.$endl;
        
        // attach header to the output
        $output .= $class_header;
        $output .= 'public $mock;'.$endl;
        
        // special mock setter method
        $output .= 'public function '.$setmock_method_name.'($mock) {'.$endl;
        $output .= '    $this->mock = $mock;'.$endl;
        $output .= '}'.$endl;

        // add a runConstructor call if this is refection+extension
        if ($this->isInherited() || $this->hasConstructor()) {
            $output .= 'public function '.$constructor_method_name.'() {'.$endl;
            $output .= '    $parent_methods = get_class_methods(get_parent_class($this));'.$endl;
            $output .= '    $method_signature = $this->'.$this->class_signature.'_findSignature(\'__construct\', $this->mock->constructor_args);'.$endl;
            $output .= '    $default_signature = $this->'.$this->class_signature.'_findSignature(\'__construct\', array());'.$endl;
            $output .= '    if ($method_signature != $default_signature) {'.$endl;
            $output .= '        $this->'.$this->class_signature.'_tallyMethod($default_signature, false);'.$endl;
            $output .= '    }'.$endl;
            $output .= '    if ($method_signature != null) {'.$endl;
            $output .= '        $this->'.$this->class_signature.'_tallyMethod($method_signature);'.$endl;
            $output .= '    }'.$endl;
            $output .= '    if (is_array($parent_methods) && in_array(\'__construct\', $parent_methods)) {'.$endl;
            $output .= '        return call_user_func_array(array($this, \'parent::__construct\'), $this->mock->constructor_args);'.$endl;
            $output .= '    }'.$endl;
            $output .= '}'.$endl;
        }
        
        // add the handler for all methods
        $output .= $this->buildInvokeMethod($this->class_signature, false);
        
        // finds the signature for a method name and params
        $output .= $this->buildFindSignature($this->class_signature, false);
        
        // tally method for counting
        $output .= $this->buildTallyMethod($this->class_signature, false);
        
        // build the getmock methods
        $output .= $this->buildGetMock($this->class_signature, false);
        
        // add all public and protected methods
        $output .= $p_methods.$endl;
        
        // add all static output if required
        if ($this->hasStaticMethods()) {
            $output .= 'public static $mock_static;'.$endl;
        
            // special static mock setter method
            $output .= 'public static function '.$setmock_method_name.'_static($mock) {'.$endl;
            $output .= '    self::$mock_static = $mock;'.$endl;
            $output .= '}'.$endl;
            
            $output .= $this->buildInvokeMethod($this->class_signature, true);
            $output .= $this->buildFindSignature($this->class_signature, true);
            $output .= $this->buildTallyMethod($this->class_signature, true);
            $output .= $this->buildGetMock($this->class_signature, true);
        }
        
        // ending } for class
        $output .= '}'.$endl;
        
        // echo $output;
        // echo "\n\n----------\n\n";
        //var_dump($this->methods);
        //echo "\n\n----------\n\n";
        // var_dump($this->signatures);
        
        eval($output);
        $this->mock_output = $output;
        
        // create the ready class
        return $this->buildClassInstantiation($mock_class, $setmock_method_name, $constructor_method_name);
    }
    
    /**
     * Builds and instantiates a named mock class
     * In addition to instantiating the mock class, it injects the mock object
     * and runs the constructor if required
     * @return Object the mocked object, ready for use
     * @param string $mock_class the mock class name
     * @param string $setmock_method the method to call for setting the mock object
     * @param string $constructor_method the constructor to call if required
     **/
    protected function buildClassInstantiation($mock_class, $setmock_method, $constructor_method) {
        global $SNAP_MockObject;
        $SNAP_MockObject = $this;
        $setmock_method_static = $setmock_method . '_static';
        
        // make the arguments for the ready class
        $ready_class = '';
        if (count($this->constructor_args) > 0) {
            $arg_output = "";
            
            foreach ($this->constructor_args as $idx => $arg) {
                $arg_output .= '$this->constructor_args['.$idx.'],';
            }
            $arg_output = trim($arg_output, ',');
            
            $ready_class = 'return new '.$mock_class.'('.$arg_output.');';
        }
        else {
            $ready_class = 'return new '.$mock_class.'();';
        }

        $ready_class = eval($ready_class);
        
        // inject the mock class
        $ready_class->$setmock_method($this);
        
        if ($this->hasStaticMethods()) {
            call_user_func_array(array(get_class($ready_class), $setmock_method_static), array($this));
        }

        // call a real constructor if required
        if ($this->isInherited() || $this->hasConstructor()) {
            $ready_class->$constructor_method();        
        }
        
        // clean up that global
        unset($SNAP_MockObject);
        
        // return the ready class
        return $ready_class;
    }
    
    protected function buildInvokeMethod($class_signature, $is_static) {
        $endl = "\n";
        $output = '';
        
        $func_name = 'public '.(($is_static) ? 'static ' : '').'function '.$class_signature.'_invokeMethod'.(($is_static) ? '_static' : '');
        $find_signature = (($is_static) ? 'self::'.$class_signature : '$this->'.$class_signature).'_findSignature'.(($is_static) ? '_static' : '');
        $tally_method = (($is_static) ? 'self::'.$class_signature : '$this->'.$class_signature).'_tallyMethod'.(($is_static) ? '_static' : '');
        $get_mock = (($is_static) ? 'self::'.$class_signature : '$this->'.$class_signature).'_getMock'.(($is_static) ? '_static' : '');
        $call_parent = ($is_static) ? 'return call_user_func_array(array(\'self\', \''.$class_signature.'_\'.$method_name.\'_original\'), $method_params);'
                                    : 'return call_user_func_array(array($this, \'parent::\'.$method_name), $method_params);';
        
        $output .= $func_name . '($method_name, $method_params) {'.$endl;
        $output .= '    $method_signature = '.$find_signature.'($method_name, $method_params);'.$endl;
        $output .= '    $default_signature = '.$find_signature.'($method_name, array());'.$endl;
        $output .= '    $mock = '.$get_mock.'();'.$endl;
        $output .= '    if ($method_signature != $default_signature) {'.$endl;
        $output .= '        '.$tally_method.'($default_signature, false);'.$endl;
        $output .= '    }'.$endl;
        $output .= '    // if we have a match, tally on it'.$endl;
        $output .= '    if ($method_signature != null) {'.$endl;
        $output .= '        $call_count = '.$tally_method.'($method_signature);'.$endl;
        $output .= '        // if we have a return value, return that'.$endl;
        $output .= '        if (isset($mock->methods[$method_signature][\'returns\'][$call_count])) {'.$endl;
        $output .= '            return $mock->methods[$method_signature][\'returns\'][$call_count];'.$endl;
        $output .= '        }'.$endl;
        $output .= '        if (isset($mock->methods[$method_signature][\'returns\'][\'default\'])) {'.$endl;
        $output .= '            return $mock->methods[$method_signature][\'returns\'][\'default\'];'.$endl;
        $output .= '        }'.$endl;
        $output .= '    }'.$endl;
        $output .= '    // if we have a return value for the default signature, return that (option 2)'.$endl;
        $output .= '    if (isset($mock->methods[$default_signature][\'returns\'][$call_count])) {'.$endl;
        $output .= '        return $mock->methods[$default_signature][\'returns\'][$call_count];'.$endl;
        $output .= '    }'.$endl;
        $output .= '    if (isset($mock->methods[$default_signature][\'returns\'][\'default\'])) {'.$endl;
        $output .= '        return $mock->methods[$default_signature][\'returns\'][\'default\'];'.$endl;
        $output .= '    }'.$endl;
        $output .= '    // if this is an inherited method, return parent method call'.$endl;
        $output .= '    if ($mock->isInherited()) {'.$endl;
        $output .= '        '.$call_parent.$endl;
        $output .= '    }'.$endl;
        $output .= '}'.$endl;
        return $output;
    }
    
    protected function buildFindSignature($class_signature, $is_static) {
        $endl = "\n";
        $output = '';
        
        $func_name = 'public '.(($is_static) ? 'static ' : '').'function '.$class_signature.'_findSignature'.(($is_static) ? '_static' : '');
        $get_mock = (($is_static) ? 'self::'.$class_signature : '$this->'.$class_signature).'_getMock'.(($is_static) ? '_static' : '');
        
        $output .= $func_name.'($method_name, $method_params = array()) {'.$endl;
        $output .= '    $mock = '.$get_mock.'();'.$endl;
        $output .= '    if (!is_array($method_params)) {'.$endl;
        $output .= '        $method_params = array();'.$endl;
        $output .= '    }'.$endl;
        $output .= '    if (!isset($mock->signatures[$method_name])) {'.$endl;
        $output .= '        $mock->signatures[$method_name] = array();'.$endl;
        $output .= '    }'.$endl;
        $output .= '    $method_signature = null;'.$endl;
        $output .= '    foreach ($mock->signatures[$method_name] as $signature => $details) {'.$endl;
        $output .= '        $signature_params = $details[\'params\'];'.$endl;
        $output .= '        // default params'.$endl;
        $output .= '        if (count($signature_params) == 0) {'.$endl;
        $output .= '            $default_method_signature = $signature;'.$endl;
        $output .= '            continue;'.$endl;
        $output .= '        }'.$endl;
        $output .= '        // non default, if all params match, use it'.$endl;
        $output .= '        $param_match = true;'.$endl;
        $output .= '        foreach ($signature_params as $idx=>$param) {'.$endl;
        $output .= '            // method param does not exist, just exit'.$endl;
        $output .= '            if (!isset($method_params[$idx])) {'.$endl;
        $output .= '                $param_match = false;'.$endl;
        $output .= '                break;'.$endl;
        $output .= '            }'.$endl;
        $output .= '            // do match. On no matches, fail entire list'.$endl;
        $output .= '            if (!$param->match($method_params[$idx])) {'.$endl;
        $output .= '                $param_match = false;'.$endl;
        $output .= '                break;'.$endl;
        $output .= '            }'.$endl;
        $output .= '        }'.$endl;
        $output .= '        // if we match'.$endl;
        $output .= '        if ($param_match) {'.$endl;
        $output .= '            $method_signature = $signature;'.$endl;
        $output .= '        }'.$endl;
        $output .= '    }'.$endl;
        $output .= '    // if there was a default, but no method match, use the default'.$endl;
        $output .= '    if (isset($default_method_signature) && !isset($method_signature)) {'.$endl;
        $output .= '        $method_signature = $default_method_signature;'.$endl;
        $output .= '    }'.$endl;
        $output .= '    return $method_signature;'.$endl;
        $output .= '}'.$endl;
        
        return $output;
    }
    
    protected function buildTallyMethod($class_signature, $is_static) {
        $endl = "\n";
        $output = '';
        
        $func_name = 'public '.(($is_static) ? 'static ' : '').'function '.$class_signature.'_tallyMethod'.(($is_static) ? '_static' : '');
        $get_mock = (($is_static) ? 'self::'.$class_signature : '$this->'.$class_signature).'_getMock'.(($is_static) ? '_static' : '');
        
        $output .= $func_name.'($method_signature, $is_execute = true) {'.$endl;
        $output .= '    $mock = '.$get_mock.'();'.$endl;
        $output .= '    $mock->methods[$method_signature][\'count\']++;'.$endl;
        $output .= '    if ($is_execute) {'.$endl;
        $output .= '        $mock->methods[$method_signature][\'exec_count\']++;'.$endl;
        $output .= '    }';
        $output .= '    return $mock->methods[$method_signature][\'exec_count\'];'.$endl;
        $output .= '}'.$endl;
        
        return $output;
    }
    
    protected function buildGetMock($class_signature, $is_static) {
        $endl = "\n";
        $output = '';
        
        $func_name = 'public '.(($is_static) ? 'static ' : '').'function '.$class_signature.'_getMock'.(($is_static) ? '_static' : '');
        $mock_location = ($is_static) ? 'self::$mock_static' : '$this->mock';
        
        $output .= $func_name.'() {'.$endl;
        $output .= '    return '.$mock_location.';'.$endl;
        $output .= '}'.$endl;
        
        return $output;
    }
    
    /**
     * Build an output block for a public method
     * calls the invokeMethod call for that public method
     * @param string $method_name
     * @return string php eval ready output
     */
    protected function buildMethod($method_name, $scope) {
        if (!method_exists($this->mocked_class, $method_name) && $this->hasMagicMethods()) {
            // magic method code!
            // if the method doesn't exist, and this class has magic methods, we have to
            // assume this was a magic method.
            $output = '';
            $endl = "\n";
            $output .= $scope.' function '.$method_name.'() {'.$endl;
            $output .= '    $args = func_get_args();'.$endl;
            $output .= '    return $this->'.$this->class_signature.'_invokeMethod(\''.$method_name.'\', $args);'.$endl;
            $output .= '}'.$endl;
            return $output;
        }
        
        // this is considered a normal method, we can use reflection to build it to
        // specification.
        $method = new ReflectionMethod($this->mocked_class, $method_name);
        
        // is this a static method
        $is_static = $method->isStatic();

        $param_string = '';
        foreach ($method->getParameters() as $i => $param) {
            $default_value = ($param->isOptional()) ? '=' . var_export($param->getDefaultValue(), true) : '';
            $type = ($param->getClass()) ? $param->getClass()->getName().' ' : '';

            $ref = ($param->isPassedByReference()) ? '&' : '';

            $param_string .= $type . $ref . '$'.$param->getName().$default_value.',';
        }
        
        $param_string = trim($param_string, ',');
        
        $output  = '';
        $endl = "\n";
        
        // if this is static, AND we need the original methods, copy them
        // please replace with late static bindings once PHP 5.3 becomes
        // a baseline
        if ($is_static && $this->isInherited()) {
            
            $contents = file($method->getFileName());
            $start_line = $method->getStartLine();
            $end_line = $method->getEndLine();

            $contents = implode("\n", array_slice($contents, $start_line - 1, $end_line - $start_line + 1));
            
            $matches = array();
            preg_match('/.*?function[\s]+'.$method_name.'.*?\{([\s\S]*)\}/i', $contents, $matches);
            
            // map self:: and parent:: to proper things
            $replaces = array(
                // self is implied, since it's in the new class, it resolves correctly
                'parent::' => get_parent_class($this->mocked_class).'::',
            );
            $contents = trim(str_replace(array_keys($replaces), array_values($replaces), $matches[1]));
            
            $output .= 'public static function '.$this->class_signature.'_'.$method_name.'_original('.$param_string.') {'.$endl;
            
            // add the original method's guts here
            $output .= $endl.$contents.$endl;
            
            $output .= '}'.$endl;
        }
        
        $invoke_method = (($is_static) ? 'self::'.$this->class_signature : '$this->'.$this->class_signature).'_invokeMethod'.(($is_static) ? '_static' : '');
        
        $output .= $scope.(($is_static) ? ' static' : '').' function '.$method_name.'('.$param_string.') {'.$endl;
        if (!$method->isConstructor() && strtolower($method->getName()) != '__construct') {
            $output .= '    $args = func_get_args();'.$endl;
            $output .= '    return '.$invoke_method.'(\''.$method_name.'\', $args);'.$endl;
        }
        else {
            // constructor takes the mock in question and loads it
            $output .= '    global $SNAP_MockObject;'.$endl;
            $output .= '    $this->mock = $SNAP_MockObject;'.$endl;
        }
        $output .= '}'.$endl;
        return $output;
    }

}


