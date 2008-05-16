<?php

/**
 * Calls a static method on an object. Useful for simplifying call_user_func_array
 * This takes advantage of the fact php static objects can exist as instances
 * and uses call_user_func_array to create a static call based on the class
 * This is especially useful when working with mocked static objects, where
 * the name of the class is unknown
 * @param $obj an instance of an object
 **/
function SNAP_callStatic($obj, $method, $params = array()) {
    
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
    
    protected $mocked_class;
    protected $interface_names;
    protected $has_constructor;
    protected $requires_magic_methods;
    protected $requires_static_methods;
    protected $requires_inheritance;
    
    protected $methods;
    protected $signatures;
    protected $counters;
    protected $constructed_object;
    
    public $constructor_args;
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
        $this->requires_inheritance = FALSE;
        $this->requires_magic_methods = FALSE;
        $this->requires_static_methods = FALSE;
        $this->has_constructor = FALSE;
        $this->interface_names = array();
        $this->methods = array();
        $this->signatures = array();
        $this->constructor_args = array();
        $this->counters = array();
        $this->mocked_class = $class_name;
        $this->constructed_object = null;
        
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
        $this->requires_inheritance = TRUE;
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
        $this->has_constructor = TRUE;
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
        $this->requires_static_methods = TRUE;
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
        $this->requires_magic_methods = TRUE;
        return $this;
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
        return $this;
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
        return $this->mockGetTallyCount($method_signature);
    }
    
    /**
     * Builds the mock object using eval, and calls the constructor on it.
     * the class signature is unique to the sum of expectations, so an idential object
     * will share the same class signature for its public methods
     * @return Object
     * @throws Snap_UnitTestException
     */
    public function construct() {
        $mock_class = $this->generateClassName();
        
        $constructor_method_name = $this->class_signature.'_runConstructor';
        $setmock_method_name = $this->class_signature.'_injectMock';
        $this->constructor_args = func_get_args();
        
        // get the public methods
        $class_list = array_unique(array_merge(array($this->mocked_class), $this->interface_names));
        $method_list = $this->locateAllMethods($class_list);

        // listen to all methods. If there are protected methods, and we are inherited
        // listen to those
        foreach ($method_list as $method => $data) {
            if ($data['scope'] == 'public') {
                $this->listenTo($method);
            }
            elseif ($data['scope'] == 'protected' && $this->isInherited()) {
                $this->listenTo($method);
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
            if (isset($method_list[$method_name]) && $method_list[$method_name]['scope'] == 'public') {
                continue;
            }
            
            // if in protected, and we are requiring inheritance, we are okay
            if (isset($method_list[$method_name]) && $method_list[$method_name]['scope'] == 'protected' && $this->isInherited()) {
                continue;
            }
            
            // if magic methods are enabled for this class, we are okay
            // we also need to listen to it
            if ($this->hasMagicMethods()) {
                $this->listenTo($method_name);
                if (!isset($method_list[$method_name])) {
                    // add to the stack
                    $method_list[$method_name] = array(
                        'scope' => 'public',
                        'class' => $this->mocked_class,
                    );
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
        foreach ($method_list as $method_name => $data) {
            if ($data['scope'] == 'public') {
                $p_methods .= $this->buildMethod($data['class'], $method_name, 'public');
                continue;
            }
            if ($data['scope'] == 'protected' && $this->isInherited()) {
                $p_methods .= $this->buildMethod($data['class'], $method_name, 'protected');
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
            $get_mock = '$this->'.$this->class_signature.'_getMock';
            
            $output .= 'public function '.$constructor_method_name.'() {'.$endl;
            $output .= '    $mock = '.$get_mock.'();'.$endl;
            $output .= '    return $mock->invokeMethod(\'__construct\', $mock->constructor_args);'.$endl;
            $output .= '}'.$endl;
        }
        
        // add the handler for all methods
        $output .= $this->buildInvokeMethod($this->class_signature, FALSE);
        
        // build the getmock methods
        $output .= $this->buildGetMock($this->class_signature, FALSE);
        
        // add all public and protected methods
        $output .= $p_methods.$endl;
        
        // add all static output if required
        if ($this->hasStaticMethods()) {
            $output .= 'public static $mock_static;'.$endl;
        
            // special static mock setter method
            $output .= 'public static function '.$setmock_method_name.'_static($mock) {'.$endl;
            $output .= '    self::$mock_static = $mock;'.$endl;
            $output .= '}'.$endl;
            
            $output .= $this->buildInvokeMethod($this->class_signature, TRUE);
            $output .= $this->buildGetMock($this->class_signature, TRUE);
        }
        
        // ending } for class
        $output .= '}'.$endl;
        
        // if (defined('LOCALDEBUG')) {
        //     echo $output;
        // }
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
     * invokes a method on the mock object, tallying and intercepting for return values
     * this method is called usually from the mock object itself, asking the mock
     * that created it to provide the return value for the method invocation
     * @param string $method_name the name of the method to invoke
     * @param array $method_params the parameters to pass to the method
     * @return mixed
     **/
    public function invokeMethod($method_name, $method_params) {
        // get all matching signatures
        $sigs = $this->mockFindSignatures($method_name, $method_params);
        $sigs_default = $this->mockFindDefaultSignature($method_name);

        // tally all methods
        foreach ($sigs as $sig) {
            $this->mockTallyMethod($sig);
        }
        $this->mockTallyMethod($sigs_default);
        
        // we've got a lot of possible sigs, do any of them have return values @ call count?
        $returns_at_call_count = array();
        $returns_at_default = array();
        foreach ($sigs as $sig) {
            if (isset($this->methods[$sig]['returns'][$this->mockGetTallyCount($sig)])) {
                $returns_at_call_count[] = $sig;
            }
            if (isset($this->methods[$sig]['returns']['default'])) {
                $returns_at_default[] = $sig;
            }
        }
        
        if (defined('LOL')) {
            var_dump($returns_at_call_count);
            var_dump($returns_at_default);
        }
        
        // > 1 return is an exception
        if (count($returns_at_call_count) > 1) {
            // error here
            throw new Snap_UnitTestException('setup_ambiguous_return', $this->mocked_class.'::'.$method_name.' has ambiguous return values.');
        }
        
        // exactly one, that's our match
        if (count($returns_at_call_count) == 1) {
            return $returns_at_call_count[0];
        }
        
        // > 1 defaults is an exception
        if (count($returns_at_default) > 1) {
            // error here
            throw new Snap_UnitTestException('setup_ambiguous_return', $this->mocked_class.'::'.$method_name.' has ambiguous default return values.');
        }
        
        // exactly one is a match
        if (count($returns_at_default) == 1) {
            return $this->methods[$returns_at_default[0]]['returns']['default'];
        }
        
        // no specialized returns. Check now, for a call count at the default
        // if that exists, use it
        if (isset($this->methods[$sigs_default]['returns'][$this->mockGetTallyCount($sigs_default)])) {
            return $this->methods[$sigs_default]['returns'][$this->mockGetTallyCount($sigs_default)];
        }
        
        // no call count default look for a really default
        if (isset($this->methods[$sigs_default]['returns']['default'])) {
            return $this->methods[$sigs_default]['returns']['default'];
        }

        // no default. If it is inherited, fall to original
        if ($this->isInherited()) {
            $method_call = $this->class_signature.'_'.$method_name.'_original';

            if ($this->hasStaticMethods()) {
                if (method_exists(get_class($this->constructed_object), $method_call)) {
                    return call_user_func_array(array(get_class($this->constructed_object), $method_call), $method_params);
                }
            }
            else {
                if (method_exists($this->constructed_object, $method_call)) {
                    return call_user_func_array(array($this->constructed_object, $method_call), $method_params);
                }
            }
        }
        
        return NULL;
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
    
    protected function mockFindSignatures($method_name, $method_params = array()) {
        if (!is_array($method_params)) {
            $method_params = array();
        }
        
        if (!isset($this->signatures[$method_name]) || count($method_params) == 0) {
            return array();
        }
        
        $sigs = array();
        foreach ($this->signatures[$method_name] as $signature => $details) {
            $params = $details['params'];
            
            if (count($params) == 0) {
                // default, move on
                continue;
            }
            
            $param_match = TRUE;
            foreach ($params as $idx => $param) {
                // more params in sig than sent to us
                if (!isset($method_params[$idx])) {
                    $param_match = FALSE;
                    break;
                }
                
                // run a match, if it fails, it is a non match
                if (!$param->match($method_params[$idx])) {
                    $param_match = FALSE;
                    break;
                }
            }
            
            // if we match, it's good
            if ($param_match) {
                $sigs[] = $signature;
            }
        }
        
        return $sigs;
    }

    protected function mockFindDefaultSignature($method_name) {
        if (!isset($this->signatures[$method_name])) {
            return NULL;
        }
        foreach ($this->signatures[$method_name] as $signature => $details) {
            if (count($details['params']) == 0) {
                return $signature;
            }
        }
    }
    
    protected function mockTallyMethod($method_signature) {
        if (!isset($this->methods[$method_signature]['count'])) {
            $this->methods[$method_signature]['count'] = 0;
        }
        $this->methods[$method_signature]['count']++;
    }
    
    protected function mockGetTallyCount($method_signature) {
        return $this->methods[$method_signature]['count'];
    }
    
    protected function buildInvokeMethod($class_signature, $is_static) {
        $endl = "\n";
        $output = '';
        
        $func_name = 'public '.(($is_static) ? 'static ' : '').'function '.$class_signature.'_invokeMethod'.(($is_static) ? '_static' : '');
        $get_mock = (($is_static) ? 'self::'.$class_signature : '$this->'.$class_signature).'_getMock'.(($is_static) ? '_static' : '');
        
        $output .= $func_name . '($method_name, $method_params) {'.$endl;
        $output .= '    $mock = '.$get_mock.'();'.$endl;
        $output .= '    return $mock->invokeMethod($method_name, $method_params);'.$endl;
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
    protected function buildMethod($class_name, $method_name, $scope) {
        if (!method_exists($class_name, $method_name) && $this->hasMagicMethods()) {
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
        $method = new ReflectionMethod($class_name, $method_name);
        
        // is this a static method
        $is_static = $method->isStatic();

        $param_string = '';
        foreach ($method->getParameters() as $i => $param) {
            $default_value = ($param->isOptional()) ? '=' . var_export($param->getDefaultValue(), TRUE) : '';
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
        if ($this->isInherited()) {
            
            $contents = file($method->getFileName());
            $start_line = $method->getStartLine();
            $end_line = $method->getEndLine();

            $contents = implode("\n", array_slice($contents, $start_line - 1, $end_line - $start_line + 1));
            
            $matches = array();
            preg_match('/.*?function[\s]+'.$method_name.'.*?\{([\s\S]*)\}/i', $contents, $matches);
            
            // no matches, this was an interface
            if (!is_array($matches) || !isset($matches[1])) {
                $matches = array('1' => '');
            }
            
            // map self:: and parent:: to proper things
            $replaces = array(
                // self is implied, since it's in the new class, it resolves correctly
                'parent::' => get_parent_class($this->mocked_class).'::',
            );
            
            $contents = trim(str_replace(array_keys($replaces), array_values($replaces), $matches[1]));
            
            if ($is_static) {
                $output .= 'public static function '.$this->class_signature.'_'.$method_name.'_original('.$param_string.') {'.$endl;
            }
            else {
                $output .= 'public function '.$this->class_signature.'_'.$method_name.'_original('.$param_string.') {'.$endl;
            }
            
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
    
    protected function generateClassName() {
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
        
        return $mock_class_test;
    }
    
    protected function locateAllMethods($class_list) {
        $methods = array();

        foreach ($class_list as $class_name) {
            // reflect the class
            $reflected_class = new ReflectionClass($class_name);
            foreach ($reflected_class->getMethods() as $method) {
                
                // if we have already set this method, and the current class is an interface
                // do not process this method
                if (isset($methods[$method->getName()]) && $reflected_class->isInterface()) {
                    continue;
                }
                
                if ($method->isConstructor() || strtolower($method->getName()) == '__construct') {
                    // special constructor stuff here
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'public',
                    );
                    $this->requiresConstructor();
                    continue;
                }

                // __call magic method
                if (strtolower($method->getName()) == '__call') {
                    $this->requiresMagicMethods();
                }

                // skip all other magic methods
                if (strpos($method->getName(), '__') === 0) {
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
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'public',
                    );
                }
                if ($method->isProtected()) {
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'protected',
                    );
                }
                if ($method->isPrivate()) {
                    $methods[$method->getName()] = array(
                        'class' => $class_name,
                        'scope' => 'private',
                    );
                }
            }
        }

        return $methods;
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
        
        $this->constructed_object = $ready_class;

        // call a real constructor if required
        if ($this->isInherited() || $this->hasConstructor()) {
            $ready_class->$constructor_method();        
        }
        
        // clean up that global
        unset($SNAP_MockObject);
        
        // return the ready class
        return $ready_class;
    }
}


