<?php

return array(
    'production' => array(
        
        'bootstrap' => array(
            'path' => APPLICATION_PATH . "/Bootstrap.php",
            'class' => 'Bootstrap',

        ),
        //'displayException' => true,
        'resources' => array(
            'frontController' => array(
                'params' => array(
                    
                ),
                'controllerDirectory' => APPLICATION_PATH . "/controllers",
            ),
            'view' => array(
                //'theme' => 'new'
                //'display' => false,
            ),
        ),
    ),
);

?>