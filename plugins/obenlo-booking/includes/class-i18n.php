<?php
/**
 * Internationalization and Localization
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_i18n {

    private $current_lang;
    private $translations;

    public function init() {
        // Determine current language from cookie or default to 'en'
        $this->current_lang = isset( $_COOKIE['obenlo_lang'] ) ? sanitize_text_field( $_COOKIE['obenlo_lang'] ) : 'en';

        // Load custom translations
        $this->load_translations();

        // Hook into WP locale
        add_filter( 'locale', array( $this, 'set_locale' ) );

        // Hook to handle ?lang= query param
        add_action( 'template_redirect', array( $this, 'handle_query_language' ), 1 );

        // Hook to translate custom text on the fly
        add_filter( 'gettext', array( $this, 'translate_custom_strings' ), 20, 3 );
        add_filter( 'gettext_with_context', array( $this, 'translate_custom_strings_context' ), 20, 4 );
        
        // Register AJAX action for switching language
        add_action( 'wp_ajax_obenlo_set_language', array( $this, 'handle_set_language' ) );
        add_action( 'wp_ajax_nopriv_obenlo_set_language', array( $this, 'handle_set_language' ) );
    }

    public function set_locale( $locale ) {
        if ( $this->current_lang === 'es' ) {
            return 'es_ES';
        } elseif ( $this->current_lang === 'fr' ) {
            return 'fr_FR';
        }
        return 'en_US';
    }

    public function handle_query_language() {
        if ( isset( $_GET['lang'] ) ) {
            $lang = sanitize_text_field( $_GET['lang'] );
            if ( in_array( $lang, array( 'en', 'es', 'fr' ) ) ) {
                setcookie( 'obenlo_lang', $lang, time() + YEAR_IN_SECONDS, '/' );
                
                // Redirect to clean up the URL
                $redirect_url = remove_query_arg( 'lang' );
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    }

    public function handle_set_language() {
        if ( isset( $_POST['lang'] ) ) {
            $lang = sanitize_text_field( $_POST['lang'] );
            if ( in_array( $lang, array( 'en', 'es', 'fr' ) ) ) {
                setcookie( 'obenlo_lang', $lang, time() + YEAR_IN_SECONDS, '/' );
                wp_send_json_success( array( 'lang' => $lang ) );
            }
        }
        wp_send_json_error();
    }

    private function load_translations() {
        $this->translations = array(
            'es' => array(
                'Obenlo your home' => 'Pon tu casa en Obenlo',
                'Switch to hosting' => 'Cambiar a anfitrión',
                'Trips' => 'Viajes',
                'Wishlists' => 'Favoritos',
                'Log out' => 'Cerrar sesión',
                'Log in / Sign up' => 'Iniciar sesión / Regístrate',
                'Log in' => 'Iniciar sesión',
                'Sign up' => 'Regístrate',
                'Host Dashboard' => 'Panel de Anfitrión',
                'Account' => 'Cuenta',
                'Messages' => 'Mensajes',
                'Support / Dispute' => 'Soporte / Disputa',
                'Help / Support' => 'Ayuda / Soporte',
                'Site Admin' => 'Admin del Sitio',
                'Search listings, categories, locations or hosts...' => 'Buscar alojamientos, categorías, ubicaciones o anfitriones...',
                'Stay' => 'Estancias',
                'Experience' => 'Experiencias',
                'Service' => 'Servicios',
                'Event' => 'Eventos',
                'Show all' => 'Mostrar todo',
                'Listings by' => 'Alojamientos de',
                'Hosted by' => 'Anfitrión:',
                'Starting from' => 'Desde',
                'total' => 'total',
                'reviews' => 'evaluaciones',
                'review' => 'evaluación',
                'Obenlo Support' => 'Soporte de Obenlo',
                "We're online and ready to help" => 'Estamos en línea y listos para ayudar',
                'Hello! How can we help you today?' => '¡Hola! ¿En qué podemos ayudarte hoy?',
                'Type a message...' => 'Escribe un mensaje...',
                'Legal' => 'Legal',
                'About Us' => 'Sobre nosotros',
                'Blog' => 'Blog',
                'News & Articles' => 'Noticias y artículos',
                'Community' => 'Comunidad',
                'Trust & Safety' => 'Confianza y seguridad',
                'About Obenlo' => 'Sobre Obenlo',
                'Our Mission' => 'Nuestra misión',
                'What is Obenlo?' => '¿Qué es Obenlo?',
                'Ready to start your journey?' => '¿Listo para empezar tu viaje?',
                'Become a Host' => 'Conviértete en anfitrión',
                'Explore Obenlo' => 'Explora Obenlo',
                'Help Center' => 'Centro de ayuda',
                'Host Onboarding' => 'Registro de anfitrión',
                'Step 1: Account Information' => 'Paso 1: Información de la cuenta',
                'Step 2: Identity Verification' => 'Paso 2: Verificación de identidad',
                'Step 3: Payout Method' => 'Paso 3: Método de pago',
                'Payout Method' => 'Método de cobro',
                'Select how you want to get paid' => 'Selecciona cómo quieres recibir tus pagos',
                'PayPal' => 'PayPal',
                'Stripe' => 'Stripe',
                'CashApp' => 'CashApp',
                'Venmo' => 'Venmo',
                'Zelle' => 'Zelle',
                'MonCash (Haiti)' => 'MonCash (Haití)',
                'Natcash (Haiti)' => 'Natcash (Haití)',
                'Phone Number' => 'Número de teléfono',
                'Email Address' => 'Correo electrónico',
                'Username / ID' => 'Usuario / ID',
                'Upload ID Document' => 'Subir documento de identidad',
                'Submit for Verification' => 'Enviar para verificación',
                'Verification Pending' => 'Verificación pendiente',
                'Verified' => 'Verificado',
                'Rejected' => 'Rechazado',
                'Welcome to Obenlo!' => '¡Bienvenido a Obenlo!',
                'Your account has been verified!' => '¡Tu cuenta ha sido verificada!',
                'Global Cancellation Policy' => 'Política global de cancelación',
                'Global Refund Policy' => 'Política global de reembolsos',
                'Guest Rules' => 'Reglas del huésped',
                'Full Refund:' => 'Reembolso completo:',
                'Partial Refund:' => 'Reembolso parcial:',
                'No Refund:' => 'Sin reembolso:',
                'How Obenlo works' => 'Cómo funciona Obenlo',
                'FAQ' => 'Preguntas frecuentes',
                'Support for Hosts' => 'Soporte para anfitriones',
                'Privacy Policy' => 'Política de privacidad',
                'Terms of Service' => 'Términos de servicio',
                'Privacy' => 'Privacidad',
                'Terms' => 'Términos',
                'Search results for' => 'Resultados de búsqueda para',
                'No results found' => 'No se encontraron resultados',
                'Hosts' => 'Anfitriones',
                'Categories' => 'Categorías',
                'Listings & Locations' => 'Alojamientos y Ubicaciones',
                'View Profile' => 'Ver Perfil',
                'All' => 'Todo',
                'Explore by category' => 'Explorar por categoría',
                'Hotels, guest houses & unique rooms' => 'Hoteles, casas de huéspedes y habitaciones únicas',
                'Tours, adventures & local activities' => 'Tours, aventuras y actividades locales',
                'Book pros: chefs, drivers & more' => 'Reserva profesionales: chefs, conductores y más',
                'Shows, live nights & performances' => 'Espectáculos, noches en vivo y actuaciones',
                'Featured Stays' => 'Estancias destacadas',
                'Check back soon for featured stays!' => '¡Vuelve pronto para ver estancias destacadas!',
                'Featured Experiences' => 'Experiencias destacadas',
                'Explore local experiences soon!' => '¡Explora experiencias locales pronto!',
                'Featured Services' => 'Servicios destacados',
                'Professional services coming soon!' => '¡Servicios profesionales próximamente!',
                'Featured Events' => 'Eventos destacados',
                'No events yet — check back soon! 🎉' => 'Aún no hay eventos — ¡vuelve pronto! 🎉',
                'What guests are saying' => 'Lo que dicen los huéspedes',
                'Obenlo experiences through the eyes of our community.' => 'Experiencias Obenlo a través de los ojos de nuestra comunidad.',
                'Meet Our Top Hosts' => 'Conoce a nuestros mejores anfitriones',
                'Meet them all' => 'Conócelos a todos',
                'Professional guides and home owners ready to welcome you.' => 'Guías profesionales y dueños de casa listos para recibirte.',
                'Toronto, Canada' => 'Toronto, Canadá',
                'night' => 'noche',
                'person' => 'persona',
                'session' => 'sesión',
                'unit' => 'unidad',
                'Hosted since %s' => 'Anfitrión desde %s',
                'Guest' => 'Huésped',
                'Recently' => 'Recientemente',
                'Hotel' => 'Hotel',
                'Guest House' => 'Casa de huéspedes',
                'Chauffeur' => 'Chófer',
                'Cook' => 'Cocinero',
                'Barbershop' => 'Barbería',
                'Hairdresser' => 'Peluquería',
                'Concierge' => 'Conserje',
                'Personal Assistant' => 'Asistente personal',
                'Babysitter' => 'Niñera',
                'Dogsitter' => 'Cuidador de perros',
                'Tour' => 'Tour',
                'Show' => 'Espectáculo',
                "You're Offline" => 'Estás desconectado',
                "It seems you've lost your connection. Don't worry, you can still browse some of your previously visited pages." => 'Parece que has perdido la conexión. No te preocupes, aún puedes navegar por algunas de las páginas que visitaste anteriormente.',
                'Back to Home' => 'Volver al inicio',
            ),
            'fr' => array(
                'Obenlo your home' => 'Mettez votre logement sur Obenlo',
                'Switch to hosting' => 'Passer en mode hôte',
                'Trips' => 'Voyages',
                'Wishlists' => 'Favoris',
                'Log out' => 'Se déconnecter',
                'Log in / Sign up' => 'Connexion / Inscription',
                'Log in' => 'Connexion',
                'Sign up' => 'Inscription',
                'Host Dashboard' => 'Tableau de bord Hôte',
                'Account' => 'Compte',
                'Messages' => 'Messages',
                'Support / Dispute' => 'Support / Litige',
                'Help / Support' => 'Aide / Support',
                'Site Admin' => 'Admin du site',
                'Search listings, categories, locations or hosts...' => 'Rechercher des logements, catégories, lieux ou hôtes...',
                'Stay' => 'Séjours',
                'Experience' => 'Expériences',
                'Service' => 'Services',
                'Event' => 'Événements',
                'Show all' => 'Tout afficher',
                'Listings by' => 'Annonces de',
                'Hosted by' => 'Hôte :',
                'Starting from' => 'À partir de',
                'total' => 'total',
                'reviews' => 'commentaires',
                'review' => 'commentaire',
                'Obenlo Support' => 'Support Obenlo',
                "We're online and ready to help" => 'Nous sommes en ligne et prêts à vous aider',
                'Hello! How can we help you today?' => 'Bonjour ! Comment pouvons-nous vous aider aujourd\'hui ?',
                'Type a message...' => 'Tapez votre message...',
                'Legal' => 'Juridique',
                'About Us' => 'À propos',
                'Blog' => 'Blog',
                'News & Articles' => 'Nouvelles et articles',
                'Community' => 'Communauté',
                'Trust & Safety' => 'Confiance et sécurité',
                'About Obenlo' => 'À propos de Obenlo',
                'Our Mission' => 'Notre mission',
                'What is Obenlo?' => 'Qu\'est-ce que Obenlo ?',
                'Ready to start your journey?' => 'Prêt à commencer votre voyage ?',
                'Become a Host' => 'Devenir hôte',
                'Explore Obenlo' => 'Explorer Obenlo',
                'Help Center' => 'Centre d\'aide',
                'Host Onboarding' => 'Inscription de l\'hôte',
                'Step 1: Account Information' => 'Étape 1 : Informations sur le compte',
                'Step 2: Identity Verification' => 'Étape 2 : Vérification d\'identité',
                'Step 3: Payout Method' => 'Étape 3 : Méthode de versement',
                'Payout Method' => 'Méthode de versement',
                'Select how you want to get paid' => 'Sélectionnez comment vous souhaitez être payé',
                'PayPal' => 'PayPal',
                'Stripe' => 'Stripe',
                'CashApp' => 'CashApp',
                'Venmo' => 'Venmo',
                'Zelle' => 'Zelle',
                'MonCash (Haiti)' => 'MonCash (Haïti)',
                'Natcash (Haiti)' => 'Natcash (Haïti)',
                'Phone Number' => 'Numéro de téléphone',
                'Email Address' => 'Adresse e-mail',
                'Username / ID' => 'Nom d\'utilisateur / ID',
                'Upload ID Document' => 'Télécharger la pièce d\'identité',
                'Submit for Verification' => 'Soumettre pour vérification',
                'Verification Pending' => 'Vérification en attente',
                'Verified' => 'Vérifié',
                'Rejected' => 'Rejeté',
                'Welcome to Obenlo!' => 'Bienvenue sur Obenlo !',
                'Your account has been verified!' => 'Votre compte a été vérifié !',
                'Global Cancellation Policy' => 'Politique globale d\'annulation',
                'Global Refund Policy' => 'Politique globale de remboursement',
                'Guest Rules' => 'Règles des voyageurs',
                'Full Refund:' => 'Remboursement complet :',
                'Partial Refund:' => 'Remboursement partiel :',
                'No Refund:' => 'Aucun remboursement :',
                'How Obenlo works' => 'Comment fonctionne Obenlo',
                'FAQ' => 'FAQ',
                'Support for Hosts' => 'Support pour les hôtes',
                'Privacy Policy' => 'Politique de confidentialité',
                'Terms of Service' => 'Conditions d\'utilisation',
                'Privacy' => 'Confidentialité',
                'Terms' => 'Conditions',
                'Search results for' => 'Résultats de recherche pour',
                'No results found' => 'Aucun résultat trouvé',
                'Hosts' => 'Hôtes',
                'Categories' => 'Catégories',
                'Listings & Locations' => 'Annonces et Lieux',
                'View Profile' => 'Voir le profil',
                'All' => 'Tout',
                'Explore by category' => 'Explorer par catégorie',
                'Hotels, guest houses & unique rooms' => 'Hôtels, maisons d\'hôtes et chambres uniques',
                'Tours, adventures & local activities' => 'Visites, aventures et activités locales',
                'Book pros: chefs, drivers & more' => 'Réservez des pros : chefs, chauffeurs et plus',
                'Shows, live nights & performances' => 'Spectacles, soirées en direct et performances',
                'Featured Stays' => 'Séjours à la une',
                'Check back soon for featured stays!' => 'Revenez bientôt pour des séjours à la une !',
                'Featured Experiences' => 'Expériences à la une',
                'Explore local experiences soon!' => 'Explorez bientôt des expériences locales !',
                'Featured Services' => 'Services à la une',
                'Professional services coming soon!' => 'Services professionnels à venir !',
                'Featured Events' => 'Événements à la une',
                'No events yet — check back soon! 🎉' => 'Pas encore d\'événements — revenez bientôt ! 🎉',
                'What guests are saying' => 'Ce que disent les voyageurs',
                'Obenlo experiences through the eyes of our community.' => 'L\'expérience Obenlo à travers les yeux de notre communauté.',
                'Meet Our Top Hosts' => 'Rencontrez nos meilleurs hôtes',
                'Meet them all' => 'Rencontrez-les tous',
                'Professional guides and home owners ready to welcome you.' => 'Guides professionnels et propriétaires prêts à vous accueillir.',
                'Toronto, Canada' => 'Toronto, Canada',
                'night' => 'nuit',
                'person' => 'personne',
                'session' => 'séance',
                'unit' => 'unité',
                'Hosted since %s' => 'Hôte depuis %s',
                'Guest' => 'Voyageur',
                'Recently' => 'Récemment',
                'Hotel' => 'Hôtel',
                'Guest House' => 'Maison d\'hôtes',
                'Chauffeur' => 'Chauffeur',
                'Cook' => 'Cuisinier',
                'Barbershop' => 'Salon de coiffure',
                'Hairdresser' => 'Coiffeur',
                'Concierge' => 'Concierge',
                'Personal Assistant' => 'Assistant personnel',
                'Babysitter' => 'Babysitter',
                'Dogsitter' => 'Dogsitter',
                'Tour' => 'Visite',
                'Show' => 'Spectacle',
                "You're Offline" => 'Vous êtes hors ligne',
                "It seems you've lost your connection. Don't worry, you can still browse some of your previously visited pages." => 'Il semble que vous ayez perdu votre connexion. Ne vous inquiétez pas, vous pouvez toujours parcourir certaines des pages que vous avez visitées précédemment.',
                'Back to Home' => 'Retour à l\'accueil',
            )
        );
    }

    public function translate_custom_strings( $translated_text, $text, $domain ) {
        if ( $this->current_lang === 'en' ) {
            return $translated_text;
        }

        // Check our custom dictionary first
        if ( isset( $this->translations[ $this->current_lang ][ $text ] ) ) {
            return $this->translations[ $this->current_lang ][ $text ];
        }

        return $translated_text;
    }
    
    public function translate_custom_strings_context( $translated_text, $text, $context, $domain ) {
        return $this->translate_custom_strings( $translated_text, $text, $domain );
    }
}
