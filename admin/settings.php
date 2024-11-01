<?php

if ( ! function_exists( 'get_stan_settings' ) ) {

    function get_stan_settings() {

        return apply_filters(
            'wc_stan_payment_gateway_settings',
            array(
                'enabled' => array(
                    'title' => 'Activer/Désactiver',
                    'label' => 'Activer le paiement avec Stan',
                    'type' => 'checkbox',
                    'description' => "Activation du paiement avec Stan permet à vos clients d'acheter sur votre site sans utiliser la carte.",
                    'default' => 'no',
                    'desc_tip' => true
                ),
                'testmode' => array(
                    'title' => 'Activer le mode test',
                    'id' => 'stan-payment-gateway-testmode',
                    'type' => 'checkbox',
                    'description' => 'Le mode test permet de tester le paiement avec Stan. Désactivez le losque votre site ecommerce est prêt.',
                    'default' => 'no',
                    'desc_tip' => true
                ),
                'client_id' => array(
                    'title' => 'Identifiant du client',
                    'id' => 'stan-payment-gateway-client_id',
                    'type' => 'text',
                    'description' => "Il s'agit de votre identifiant client, il vous a été transmis par email lors de votre inscription. Si vous n'en avez pas ou avez perdu votre code, rendez-vous sur <a href='https://compte.stan-app.fr/signup' target='_blank'>stan-app.fr</a>."
                ),
                'test_client_id' => array(
                    'title' => 'Identifiant du client TEST',
                    'id' => 'stan-payment-gateway-test_client_id',
                    'type' => 'text',
                    'description' => "Il s'agit de votre identifiant client en MODE TEST, il vous a été transmis par email lors de votre inscription. Si vous n'en avez pas ou avez perdu votre code, rendez-vous sur <a href='https://compte.stan-app.fr/signup' target='_blank'>stan-app.fr</a>."
                ),
                'secret_key' => array(
                    'title' => 'Code secret du client',
                    'id' => 'stan-payment-gateway-secret_key',
                    'type' => 'password',
                    'description' => "Il s'agit du code secret associé à votre identifiant client, il vous a été transmis par email lors de votre inscription. Si vous n'en avez pas ou avez perdu votre code, rendez-vous sur <a href='https://compte.stan-app.fr/signup' target='_blank'>stan-app.fr</a>."
                ),
                'test_secret_key' => array(
                    'title' => 'Code secret du client TEST',
                    'id' => 'stan-payment-gateway-test_secret_key',
                    'type' => 'password',
                    'description' => "Il s'agit du code secret de test associé à votre identifiant client, il vous a été transmis par email lors de votre inscription. Si vous n'en avez pas ou avez perdu votre code, rendez-vous sur <a href='https://compte.stan-app.fr/signup' target='_blank'>stan-app.fr</a>."
                ),
                'test_connection' => array(
                    'title' => 'Tester la connexion',
                    'class' => 'button-secondary',
                    'id' => 'stan-test-btn',
                    'type' => 'text'
                ),
                'test_connection_result' => array(
                    'id' => 'stan-payment-gateway-test_connection_result',
                    'type' => 'hidden'
                ),
                'stan_account_infos_btn' => array(
                    'title' => 'Mes informations',
                    'class' => 'button-primary',
                    'id' => 'stan-account-infos-btn',
                    'type' => 'text'
                ),
                'stan_payment_button_section' => array(
                    'title' => 'Bouton de paiement Stan',
                    'type' => 'title'
                ),
                'title' => array(
                    'title' => 'Texte du bouton de paiement',
                    'type' => 'text',
                    'description' => 'Texte affiché lorsque votre client est dans le page de paiement.',
                    'default' => 'Payer avec Stan',
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => 'Description sous le bouton',
                    'id' => 'stan-payment-gateway-description',
                    'type' => 'textarea',
                    'description' => 'Description sous le bouton de paiment Stan.',
                    'default' => "Achat rapide et sécurisé par virement open banking",
                    'desc_tip' => true
                ),
                'only_stanners' => array(
                    'title' => 'Accessibilité à Stan Payment',
                    'label' => 'Afficher le paiement avec Stan uniquement aux Stanners',
                    'id' => 'stan-payment-gateway-only_stanners',
                    'type' => 'checkbox',
                    'description' => "Cette option vous permet d'afficher le paiement avec Stan uniquement aux utilisateurs qui visitent votre site avec Stan App",
                    'default' => 'no'
                ),
                'stan_connect' => array(
                    'title' => 'Stan Easy Connect',
                    'label' => 'Activer Stan Easy Connect pour Woocommerce',
                    'id' => 'stan-payment-gateway-stan_connect',
                    'type' => 'checkbox',
                    'description' => "Permettre à vos clients de se connecter avec Stan, ils n'auront pas besoin de remplir de formulaire lors du checkout, rendez l'achat simple !",
                    'default' => 'yes'
                ),
                'stan_api_url' => array(
                    'id' => 'stan-api-url',
                    'type' => 'hidden'
                ),
                'stan_api_auth_url' => array(
                    'id' => 'stan-api-auth-url',
                    'type' => 'hidden'
                )
            )
        );
           
    }
}