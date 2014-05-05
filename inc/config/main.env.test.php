<?php
return array(
    'logger' => true,
    'actions' => array(
        'test' => array(
            'classname' => 'Index'
        ),
        'index' => array(
            'classname' => 'Index'
        )
    ),
    'plugins' => array(
        'oauth2' => array(
            'name' => 'OAuth2',
            'directory' => 'oauth2'
        )
    )
);
?>
