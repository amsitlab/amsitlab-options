<?php

/*
 * This file is part of the Amsitlab package.
 *
 * (c) Amsit S <amsit14@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Amsitlab\Library\Options;


use ArrayAccess;
use Exception;
use UnexpectedValueException;
use InvalidArgumentException;
use Amsitlab\Library\Options\OptionsAwareInterface as Oai;



/**
 * Set Options , validate and merge them with default values
 *
 * @author Amsit S <amsit14@gmail.com>
 */



class Options implements ArrayAccess {



	/**
	 * Options collect
	 * @var string[] $filterType
	 */
	protected $options = [];



	/**
	 * filtering value type
	 * string key, callable value
	 *
	 * @var string[]
	 */
	protected $filterType = [
		'string' => 'is_string',
		'bool' => 'is_bool',
		'object' => 'is_object',
		'int' => 'is_int',
		'float' => 'is_float',

	];










	/**
	 * Constructor
	 * Set options an array type
	 *
	 * @param string[] $options
	 * @see \Amsitlab\Library\Options\Options::set() for details.
	 * @return void
	 */
	public function __construct(array $options=[]){
		$this->set($options);
	}










	/**
	 * Append/Add key value options
	 *
	 * @param string $key
	 * @param mixed $val
	 * @throws Exception if option key ($key) not string
	 * @return void
	 */
	public function append( $key, $val){
		if(!is_string($key)){
			$msg = sprintf('Invalid options key "%s" expect string type, %s given', $key, gettype($key));
			throw new InvalidArgumentException($msg);
		}
		$this->options[$key] = $val;
	}







	/**
	 * Rrmoving options
	 *
	 * @param string $key
	 */
	public function remove( $key){

		if($this->has($key)){
			unset($this->options[$key]);
		}
	}







	/**
	 * Ensure option with key
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key){
		return isset($this->options[$key]);
	}









	/**
	 * Set options an array
	 * with format (string key, mixed value)
	 *
	 * @param string[] $opt
	 * @see \Amsitlab\Library\Options\Options::append() for details.
	 * @return void
	 */
	public function set( array $opt ){
		foreach($opt as $key => $val){
			$this->append($key,$val);
		}
	}





	/**
	 * Getting options with oop style
	 *
	 * @param string $key
	 * @param mixed $onfail default null
	 * @see \Amsitlab\Library\Options\Options::has() for details
	 * @return mixed
	 */
	public function get($key,$onfail=null){
		return $this->has($key) ? $this->options[$key] : $onfail;
	}










	/**
	 * Register function for filtering type
	 *
	 * @param string $filterName
	 * @param callable $callback
	 * @throws UnexpectedValueException if $filterName not an string
	 * @return void
	 */
	public function registerFilterType($filterName, callable $callback){
		if(!is_string($filterName)){
			$msg = sprintf('Invalid type for filter name "%s", expect string, "%s" given',$filterName,gettype($filterName));
			throw new UnexpectedValueException($msg);

		}

		$filterName = strtolower($filterName);
		$this->filterType[$filterName] = $callback;

	}





	/**
	 * Validating type
	 *
	 * @param string $key options key
	 * @param string $filterName
	 * @throws UnexpectedValueException if filter type not set.
	 * @return bool
	 */
	public function validType( $key, $filterName){
		$filterName = strtolower($filterName);
		if(!$this->has($key)) return false;
		if(!isset($this->filterType[$filterName])){
			$msg = sprintf('Unknown filter for type "%s"',$filterName);
			throw new UnexpectedValueException($msg);

		}
		return call_user_func($this->filterType[$filterName],$this->options[$key]);

	}











	/**
	 * Resolve options
	 *
	 * @param \Amsitlab\Library\Options\OptionsAwareInterface $oai default null
	 * @return string[]
	 */
	public function resolve(Oai $oai=null){

		if(null !== $oai){
			$valid = $this->withOai($oai);
			return $valid;
		}

		return $this->options;

		

	}










	/**
	 * Registering Option Aware
	 *
	 * @param \Amsitlab\Library\Options\OptionsAwareInterface $oai as Oai
	 * 
	 * @see \Amsitlab\Library\Options\Options::ensureRequired() for detail.
	 * @see \Amsitlab\Library\Options\Options::ensureAllowValue() for detail.
	 * @see \Amsitlab\Library\Options\Options::ensureAllowType() for detail.
	 * @see \Amsitlab\Library\Options\Options::ensureNormalize() for detail.
	 * @return string[] or empty array on fail
	 */
	protected function withOai(Oai $oai){
		// Oai is alias of \Amsitlab\Library\Options\OptionsAwareInterface

		$data = $oai->optionsMap();
	

		$return = true;

		$debug = 0;
		foreach($data as $key => $value){

			

			//if option not set use default options 
			if(!isset($this->options[$key]) && isset($value[Oai::FLAG_DEFAULT])){

				$this->options[$key] = $value[Oai::FLAG_DEFAULT];
			}

			if(!$this->ensureRequired($key,$value)){

 				$return = false;
				break;
			}

			if(!$this->ensureAllowValue($key,$value)){
				
				$return = false;
				break;
			}

			if(!$this->ensureAllowType($key,$value)){
				
				$return = false;
				break;
			}

			if(!$this->ensureNormalize($key,$value)){
				
				$return = false;
				break;
			}
		}
		

		return $return === false ? [] : $this->options;

	}









	/**
	 * Ensuring required options
	 *
	 * @param string $key
	 * @param mixed[] $value
	 * @see \Amsitlab\Library\Options\Options::withOai() for details.
	 * @throws Exception if options is not set.
	 * @return bool
	 */
	protected function ensureRequired($key,$value){
		if(isset($value[Oai::FLAG_REQUIRED])
		&& $value[Oai::FLAG_REQUIRED] === true
		&& !$this->has($key)){

			$msg = sprintf('Required option "%s"',$key);
			throw new Exception($msg);
			return false;                                                 
		}
		return true;

	}











	/**
	 * Ensuring Allowed Value of options
	 *
	 * @param string $key,
	 * @param mixed[] $value;
	 *
	 * @see \Amsitlab\Library\Options\Options::withOai() for details.
	 * @throws UnexpectedValueException if value not allowed
	 *
	 * @return bool
	 */
	protected function ensureAllowValue($key,$value){

		if(isset($value[Oai::FLAG_ALLOW_VALUE])){
			$vals = (array)$value[Oai::FLAG_ALLOW_VALUE];
			if(!in_array($this->options[$key],$vals)){
			       
				$msg = sprintf('Unexpected option "%s" with value "%s", expect to be one of (%s)',$key,$this->options[$key],implode(', ',$vals));

				throw new UnexpectedValueException($msg);
				return false;
			}                                                                                                                                       }

		return true;

	}









	/**
	 * Ensuring allowed type of options value
	 *
	 * @param string $key
	 * @param mixed[] $value
	 * @see \Amsitlab\Library\Options\Options::withOai() for details.
	 * @throws InvalidArgumentException if type is invalid
	 *
	 * @return bool
	 */
	protected function ensureAllowType( $key, $value){
		
		if(isset($value[Oai::FLAG_ALLOW_TYPE])){
			$types = (array)$value[Oai::FLAG_ALLOW_TYPE];
			foreach($types as $type){
				if(!$this->validType($key,$type)){
					$msg = sprintf('Invalid type option "%s", expect type to be one of ( %s ), %s given',$key,implode(', ', $types),gettype($this->options[$key]));
					throw new InvalidArgumentException($msg);
					return false;                                           
				}
			}
		}
		return true;
	}










	/**
	 * Ensure normalize options value
	 *
	 * @param string $key
	 * @param mixed[] $value
	 * @see \Amsitlab\Library\Options\Options::withOai() for details.
	 * @return bool
	 */
	protected function ensureNormalize($key, $value){


		if(isset($value[Oai::FLAG_NORMALIZE])){
			$func = $value[Oai::FLAG_NORMALIZE];
			if(!is_callable($func)){
				$msg = sprintf('Missing callable type for normalize option "%s", "%s" type given',$key, gettype($func));
				throw new InvalidArgumentException($msg);
				return false;
			}
			$this->options[$key] = call_user_func_array($func, [$this, $this->options[$key]]);
			return true;
		}

		return true;

	}







	/************************************************
	 *						*
	 *              ArrayAccess Method		*
	 *						*
	 * *********************************************/





	/**
	 * @inheritdoc
	 */
	public function offsetExists($key){
		return isste($this->options[$key]);
	}






	/**
	 * @inheritdoc
	 */
	public function offsetGet($key){
		return isset($this->options[$key]) ? $this->options[$key] : null;

	}





	/**
	 * @inheritdoc
	 */
	public function offsetSet($key,$val){

		$this->options[$key] = $val;
	}







	/**
	 * @inheritdoc
	 */
	public function offsetUnset($key){
		unset($this->options[$key]);
	}

}





