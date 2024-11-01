<?php


$settings = array(
        array(
            'name' => __( 'General Configuration', 'tracksend' ),
            'type' => 'title',
            'id'   => $prefix . 'general_config_settings',
            'desc' => __( 'To connect, follow the instructions on the <a href="https://app.tracksend.co/integrations" target="_blank"> integrations page</a> of your Tracksend dashboard.', 
            
            'tracksend' ),

        ),
       
        array(
            'id'        => $prefix . 'api_key',
            'name'      => __( 'Tracksend API Key', 'tracksend' ), 
            'type'      => 'text',
            'desc_tip'  => __( ' Contact tracksend admin if this key doesnt match the key on your dashboard', 'tracksend'),
            'desc'              => __( 'Tracksend API Key  is populated when your store is successfully connected to Tracksend.', 'tracksend' ),
            'custom_attributes' => array( 'readonly' => 'readonly' ),
        ),
        
        array(
            'id'        => '',
            'name'      => __( 'General Configuration', 'tracksend' ),
            'type'      => 'sectionend',
            'desc'      => '',
            'id'        => $prefix . 'general_config_settings'
        ),

                             
    );
?>
