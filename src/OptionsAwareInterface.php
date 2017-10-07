<?php

/*                                                                       * This file is part of the Amsitlab package.
 *
 * (c) Amsit S <amsit14@gmail.com>
 *
 * For the full copyright and license information, please view the LICEN
SE
 * file that was distributed with this source code.
 */

namespace Amsitlab\Library\Options;


/**
 * Options aware interface class
 *
 * @author Amsit S <amsit14@gmail.com>
 */

interface OptionsAwareInterface {




	/**
	 * @var string FLAG_DEFAULT
	 */
	const FLAG_DEFAULT = 'default';



	/**
	 * @var string FLAG_REQUIRED
	 */
	const FLAG_REQUIRED = 'required';



	/**
	 * @var string FLAG_ALLOW_VALUE
	 */
	const FLAG_ALLOW_VALUE = 'allow_value';




	/**
	 * @var string FLAG_ALLOW_TYPE
	 */
	const FLAG_ALLOW_TYPE = 'allow_type';




	/**
	 * @var string FLAG_NORMALIZE
	 */
	const FLAG_NORMALIZE = 'normalize';








	/**
	 * Options Map	
	 *
	 * mapping option 
	 *			
	 * @return mixed[]
	 */


	public function optionsMap();








}
