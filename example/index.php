<?php


require '../vendor/autoload.php';


use Amsitlab\Library\Options\Options;
use Amsitlab\Library\Options\OptionsAwareInterface;


class Test implements OptionsAwareInterface {





        public function optionsMap(){

                return [
                        'domain' => [
                                self::FLAG_REQUIRED => true,
                                self::FLAG_ALLOW_VALUE => [
                                        'tokopedia.com',
                                        'bukalapak.com',
                                ],
                                self::FLAG_ALLOW_TYPE => 'fotos',
                                self::FLAG_NORMALIZE => function (Options $opt, $value){
                                        if($opt['domain'] == 'bukalapak.com'){
                                                return 'tokopedia.com';
                                        }
                                },
                        ],

                        'schema' => [
                                self::FLAG_DEFAULT => 'http',
                                self::FLAG_NORMALIZE => function (Options $opt, $value){

                                        if($value == 'http'){
                                                $opt['port'] = 80;
                                        }
                                        return $value;
                                },
                        ],
                ];
        }
}






function is_fotos($key){
   return true;
}

$opt = new Options(
	['domain' => 'bukalapak.com']

);

$opt->append('schema','https');

$opt->append('port',443);

$opt->append('mobiledomain','m.tokopedia.com');


$opt->registerFilterType('fotos','is_fotos');

$opt->registerFilterType('someFilterName', function ($args){
	return true;
});

$test = new Test;



var_dump($opt->resolve($test)); // array(4) { ["domain"]=> string(13) "tokopedia.com" ["schema"]=> string(5) "https" ["port"]=> int(443) ["mobiledomain"]=> string(15) "m.tokopedia.com" }


var_dump($opt->get('port')); // int(443)

// Or use Array Access

var_dump($opt['port']); // int(443)
