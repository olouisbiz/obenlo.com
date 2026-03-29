<?php
/**
 * Translation Engine for Obenlo
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_I18N_Engine
{
    private $current_lang;
    private $translations;

    public function init()
    {
        // Determine current language from cookie or default to 'en'
        $this->current_lang = isset($_COOKIE['obenlo_lang']) ? sanitize_text_field($_COOKIE['obenlo_lang']) : 'en';

        // 1. Auto-Repair: Sync master dictionaries if database boxes are empty
        $this->maybe_auto_populate_dictionaries();

        // Load custom translations
        $this->load_translations();

        // Hook into WP locale
        add_filter('locale', array($this, 'set_locale'));

        // Hook to handle ?lang= query param
        add_action('template_redirect', array($this, 'handle_query_language'), 1);

        // Hook to translate custom text on the fly
        add_filter('gettext', array($this, 'translate_custom_strings'), 20, 3);
        add_filter('gettext_with_context', array($this, 'translate_custom_strings_context'), 20, 4);

        // Hook into dynamic content
        add_filter('the_title', array($this, 'translate_dynamic_content'), 20);
        add_filter('the_content', array($this, 'translate_dynamic_content'), 20);
        add_filter('the_excerpt', array($this, 'translate_dynamic_content'), 20);
        
        // Also translate custom fields (meta)
        add_filter('get_post_metadata', array($this, 'translate_meta_content'), 20, 4);

        // Translate Menu Items
        add_filter('wp_get_nav_menu_items', array($this, 'translate_menu_items'), 20);

        // Unified AJAX action for switching language
        add_action('wp_ajax_obenlo_set_language', array($this, 'handle_set_language'));
        add_action('wp_ajax_nopriv_obenlo_set_language', array($this, 'handle_set_language'));

        // Google Translate & Obenlo Switcher (Unconditional Registration for Resilience)
        add_action('wp_enqueue_scripts', array($this, 'enqueue_google_translate'));
        add_action('wp_head', array($this, 'inject_google_translate_css'), 1);
        add_action('wp_footer', array($this, 'inject_google_translate_script'), 999);
    }

    private function maybe_auto_populate_dictionaries()
    {
        $es_opt = get_option('obenlo_i18n_es');
        $fr_opt = get_option('obenlo_i18n_fr');
        $gt_enabled = get_option('obenlo_enable_google_translate', '0');

        // Force Enable Widget if missing or pulled from dev
        if ($gt_enabled !== '1') {
            update_option('obenlo_enable_google_translate', '1');
        }

        // Check for empty or default placeholder
        if (!$es_opt || $es_opt === '[]' || (is_array($es_opt) && empty($es_opt))) {
            $es_master = array(
                'Dashboard Overview' => 'Resumen del panel',
                'My Listings' => 'Mis anuncios',
                'Bookings' => 'Reservas',
                'Reviews' => 'Reseñas',
                'Broadcasts' => 'Anuncios',
                'Support' => 'Soporte',
                'Account Status:' => 'Estado de la cuenta:',
                'Complete Verification →' => 'Completar verificación →',
                'Total Earnings' => 'Ganancias totales',
                'Completed Bookings' => 'Reservas completadas',
                'Active Listings' => 'Anuncios activos',
                'View Storefront' => 'Ver escaparate',
                '+ Add New Listing' => '+ Añadir nuevo anuncio',
                'Search bookings...' => 'Buscar reservas...',
                'Base Price' => 'Precio base',
                'Business / Property Name' => 'Nombre del negocio / propiedad',
                'Login' => 'Iniciar sesión',
                'Trips' => 'Viajes',
                'Wishlists' => 'Lista de deseos',
                'Log out' => 'Cerrar sesión'
            );
            update_option('obenlo_i18n_es', $es_master);
        }

        if (!$fr_opt || $fr_opt === '[]' || (is_array($fr_opt) && empty($fr_opt))) {
            $fr_master = array(
                'Dashboard Overview' => 'Aperçu du tableau de bord',
                'My Listings' => 'Mes annonces',
                'Bookings' => 'Réservations',
                'Reviews' => 'Avis',
                'Broadcasts' => 'Annonces',
                'Support' => 'Support',
                'Account Status:' => 'Statut du compte :',
                'Complete Verification →' => 'Compléter la vérification →',
                'Total Earnings' => 'Gains totaux',
                'Completed Bookings' => 'Réservations terminées',
                'Active Listings' => 'Annonces actives',
                'View Storefront' => 'Voir ma boutique',
                '+ Add New Listing' => '+ Ajouter une annonce',
                'Search bookings...' => 'Chercher des réservations...',
                'Base Price' => 'Prix de base',
                'Business / Property Name' => 'Nom de l\'entreprise / propriété',
                'Login' => 'Connexion',
                'Trips' => 'Voyages',
                'Wishlists' => 'Favoris',
                'Log out' => 'Se déconnecter'
            );
            update_option('obenlo_i18n_fr', $fr_master);
        }
    }

    public function enqueue_google_translate()
    {
        wp_enqueue_script('google-translate', '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit', array(), null, true);
    }

    public function inject_google_translate_css()
    {
        if (get_option('obenlo_enable_google_translate', '0') !== '1') return;
        ?>
        <style id="obenlo-pure-proxy-suppression">
            /* THE SHIELD: Total suppression of all Google-injected UI */
            .goog-te-banner-frame,
            .goog-te-banner-frame.skiptranslate,
            #goog-gt-tt,
            .goog-te-balloon-frame,
            .goog-tooltip,
            .goog-tooltip:hover,
            iframe.goog-te-banner-frame,
            #google-translate-banner,
            .skiptranslate > iframe {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                width: 0 !important;
                pointer-events: none !important;
                position: fixed !important;
                top: -1000px !important;
                left: -1000px !important;
                z-index: -9999 !important;
            }

            /* FORCE LAYOUT STABILITY: Stop Google from shifting the page */
            html, body {
                top: 0px !important;
                margin-top: 0px !important;
                position: relative !important;
            }

            /* REMOVE HIGHLIGHTS: No background-color leaks on translated text */
            .goog-text-highlight,
            [class*="goog-text-highlight"],
            font[style*="background-color"],
            span[style*="background-color"] {
                background-color: transparent !important;
                border: none !important;
                box-shadow: none !important;
                color: inherit !important;
                background: transparent !important;
            }

            /* PREMIUM OBENLO SWITCHER UI */
            .obenlo-lang-switcher {
                position: fixed;
                bottom: 85px;
                right: 25px;
                z-index: 999999;
                font-family: 'Inter', -apple-system, sans-serif;
            }

            .obenlo-lang-trigger {
                background: rgba(230, 30, 77, 0.9);
                backdrop-filter: blur(10px);
                color: #fff;
                padding: 10px 18px;
                border-radius: 50px;
                cursor: pointer;
                box-shadow: 0 8px 30px rgba(230, 30, 77, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                user-select: none;
            }

            .obenlo-lang-trigger:hover {
                transform: translateY(-3px) scale(1.02);
                background: #e61e4d;
                box-shadow: 0 12px 40px rgba(230, 30, 77, 0.4);
            }

            .obenlo-lang-menu {
                position: absolute;
                bottom: calc(100% + 15px);
                right: 0;
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 15px 45px rgba(0,0,0,0.15);
                width: 180px;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                transform: translateY(20px) scale(0.95);
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                border: 1px solid #f0f0f0;
            }

            .obenlo-lang-switcher.active .obenlo-lang-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0) scale(1);
            }

            .obenlo-lang-item {
                padding: 14px 20px;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 12px;
                color: #333;
                font-size: 14px;
                font-weight: 500;
            }

            .obenlo-lang-item:hover {
                background: #f8f8f8;
                padding-left: 25px;
                color: #e61e4d;
            }

            .obenlo-lang-item.active {
                background: #fff5f7;
                color: #e61e4d;
            }

            .obenlo-lang-item.active::after {
                content: '✓';
                margin-left: auto;
                font-size: 12px;
            }

            .obenlo-lang-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #ccc;
            }
            .obenlo-lang-item.active .obenlo-lang-dot {
                background: #e61e4d;
            }

            /* HIDDEN ENGINE ROOT */
            #google_translate_element_isolated {
                display: none !important;
                visibility: hidden !important;
                position: fixed !important;
                top: -9999px !important;
            }
        </style>
        <?php
    }

    public function inject_google_translate_script()
    {
        if (get_option('obenlo_enable_google_translate', '0') !== '1') return;
        
        // Use Native Obenlo Cookie for UI state to avoid confusion with Google's state
        $lang_code = isset($_COOKIE['obenlo_lang']) ? $_COOKIE['obenlo_lang'] : 'en';
        $current_lang = 'English';
        if ($lang_code === 'es') $current_lang = 'Español';
        if ($lang_code === 'fr') $current_lang = 'Français';
        ?>
        <!-- HIDDEN ENGINE -->
        <div id="google_translate_element_isolated" style="display:none !important;"></div>

        <!-- SOVEREIGN SWITCHER (Shielded from Google auto-translation) -->
        <div class="obenlo-lang-switcher notranslate" id="obenloLangSwitcher">
            <div class="obenlo-lang-trigger" id="obenloLangTrigger">
                <span class="obenlo-lang-label"><?php echo esc_html($current_lang); ?></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </div>
            <div class="obenlo-lang-menu">
                <div class="obenlo-lang-item <?php echo $lang_code === 'en' ? 'active' : ''; ?>" data-lang="en">
                    <div class="obenlo-lang-dot"></div> English
                </div>
                <div class="obenlo-lang-item <?php echo $lang_code === 'es' ? 'active' : ''; ?>" data-lang="es">
                    <div class="obenlo-lang-dot"></div> Español
                </div>
                <div class="obenlo-lang-item <?php echo $lang_code === 'fr' ? 'active' : ''; ?>" data-lang="fr">
                    <div class="obenlo-lang-dot"></div> Français
                </div>
            </div>
        </div>

        <script type="text/javascript">
            window.googleTranslateElementInit = function() {
                if(typeof google !== 'undefined' && google.translate) {
                    new google.translate.TranslateElement({
                        pageLanguage: 'en',
                        autoDisplay: false
                    }, 'google_translate_element_isolated');
                }
            };

            (function() {
                const trigger = document.getElementById('obenloLangTrigger');
                const switcher = document.getElementById('obenloLangSwitcher');
                const items = document.querySelectorAll('.obenlo-lang-item');

                // Robust Initialization Check
                const checkGoogle = setInterval(() => {
                    if (window.google && google.translate) {
                        googleTranslateElementInit();
                        clearInterval(checkGoogle);
                    }
                }, 500);

                if (trigger) {
                    trigger.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (switcher) switcher.classList.toggle('active');
                    });
                }

                items.forEach(item => {
                    item.addEventListener('click', function() {
                        const langGroup = this.getAttribute('data-lang');
                        
                        // 1. SET COOKIES IN JAVASCRIPT: Immediate effect for PWA/Standalone
                        const expiry = 60 * 60 * 24 * 365; // 1 year
                        const secure = window.location.protocol === 'https:' ? '; secure' : '';
                        const googVal = (langGroup === 'en') ? '' : '/en/' + langGroup;
                        
                        document.cookie = `obenlo_lang=${langGroup}; path=/; max-age=${expiry}; samesite=Lax${secure}`;
                        
                        if (langGroup === 'en') {
                            // Clear cookies properly to reset to English
                            document.cookie = `googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; samesite=Lax${secure}`;
                            document.cookie = `googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=.${window.location.hostname}; samesite=Lax${secure}`;
                        } else {
                            // Set for both root and specific domain to ensure Google Translate sees it
                            document.cookie = `googtrans=${googVal}; path=/; max-age=${expiry}; samesite=Lax${secure}`;
                            document.cookie = `googtrans=${googVal}; path=/; max-age=${expiry}; domain=.${window.location.hostname}; samesite=Lax${secure}`;
                        }

                        // 2. REDIRECT: Server will confirm and clean up URL
                        const url = new URL(window.location.href);
                        url.searchParams.set('lang', langGroup);
                        window.location.href = url.toString();
                    });
                });

                document.addEventListener('click', (e) => {
                   if (switcher && !switcher.contains(e.target)) {
                       switcher.classList.remove('active');
                   }
                });

                // Aggressive suppression of rogue elements
                const purge = () => {
                    const topBar = document.querySelector('.goog-te-banner-frame');
                    if (topBar) topBar.remove();
                    
                    document.body.style.top = '0px';
                    document.body.style.marginTop = '0px';
                    document.documentElement.style.marginTop = '0px';
                };

                setInterval(purge, 500);
            })();
        </script>
        <?php
    }

    public function set_locale($locale)
    {
        if ($this->current_lang === 'es') {
            return 'es_ES';
        } elseif ($this->current_lang === 'fr') {
            return 'fr_FR';
        }
        return $locale; // Return original locale (usually en_US)
    }

    public function handle_query_language()
    {
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field($_GET['lang']);
            if (in_array($lang, array('en', 'es', 'fr'))) {
                $expiry = time() + (365 * 24 * 60 * 60);
                $is_secure = is_ssl();
                
                // Set Obenlo Native Cookie (PHP 7.3+ Array Format for PWA/Lax compatibility)
                setcookie('obenlo_lang', $lang, array(
                    'expires' => $expiry,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $is_secure,
                    'httponly' => false,
                    'samesite' => 'Lax'
                ));

                // Set Google Translate Cookie (googtrans)
                $goog_val = ($lang === 'en') ? '' : '/en/' . $lang;
                $domain = '.' . $_SERVER['HTTP_HOST'];
                
                $goog_args = array(
                    'expires' => $expiry,
                    'path' => '/',
                    'domain' => $domain,
                    'secure' => $is_secure,
                    'httponly' => false,
                    'samesite' => 'Lax'
                );

                setcookie('googtrans', $goog_val, $goog_args);
                
                // Also set for current domain for max compatibility
                $goog_args['domain'] = '';
                setcookie('googtrans', $goog_val, $goog_args);

                // Redirect to clean up the URL and trigger engine with new cookies
                $redirect_url = remove_query_arg('lang');
                wp_safe_redirect($redirect_url);
                exit;
            }
        }
    }

    public function handle_set_language()
    {
        if (isset($_POST['lang'])) {
            $lang = sanitize_text_field($_POST['lang']);
            if (in_array($lang, array('en', 'es', 'fr'))) {
                setcookie('obenlo_lang', $lang, array(
                    'expires' => time() + (365 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => is_ssl(),
                    'samesite' => 'Lax'
                ));
                wp_send_json_success(array('lang' => $lang));
            }
        }
        wp_send_json_error('Invalid language');
    }

    private function load_translations()
    {
        // Load hardcoded dictionary first, then potentially merge with user-defined translations
        $this->translations = array(
            'fr' => array(
                // Menu & Global
                'Switch to hosting' => 'Passer en mode hôte',
                'Login' => 'Connexion',
                'Trips' => 'Voyages',
                'Wishlists' => 'Favoris',
                'Log out' => 'Se déconnecter',
                'Log in / Sign up' => 'Se connecter / S\'inscrire',
                'Log in' => 'Se connecter',
                'Sign up' => 'S\'inscrire',
                'Host Dashboard' => 'Tableau de bord hôte',
                'Account' => 'Compte',
                'Messages' => 'Messages',
                'Support / Dispute' => 'Support / Litige',
                'Help / Support' => 'Aide / Support',
                'Help Center' => 'Centre d\'aide',
                'Site Admin' => 'Admin du site',
                'Search listings, categories, locations or hosts...' => 'Chercher par lieu, catégorie ou hôte...',
                'Stay' => 'Séjour',
                'Experience' => 'Expérience',
                'Service' => 'Service',
                'Event' => 'Événement',
                'Show all' => 'Tout afficher',
                'Listings by' => 'Annonces de',
                'Hosted by' => 'Hôte :',
                'Starting from' => 'À partir de',
                'total' => 'total',
                'reviews' => 'avis',
                'review' => 'avis',
                'Home' => 'Accueil',
                'Contact' => 'Contact',
                
                // Dashboard Layout
                'Overview' => 'Aperçu',
                'My Listings' => 'Mes annonces',
                'Bookings' => 'Réservations',
                'Storefront' => 'Boutique',
                'Reviews' => 'Avis',
                'Broadcasts' => 'Annonces',
                'Availability' => 'Disponibilité',
                'Payout Settings' => 'Paiements',
                'Support' => 'Support',
                'View Storefront' => 'Voir ma boutique',
                'Dashboard Overview' => 'Aperçu du tableau de bord',
                'Account Status:' => 'Statut du compte :',
                'Complete Verification →' => 'Compléter la vérification →',
                'Update Payouts' => 'Modifier les paiements',
                'Preview My Storefront' => 'Préparer ma boutique',
                
                // Dashboard Stats
                'Total Earnings' => 'Gains totaux',
                'Completed Bookings' => 'Réservations terminées',
                'Active Listings' => 'Annonces actives',
                'Live on Obenlo' => 'En ligne sur Obenlo',
                'Total Bookings' => 'Total des réservations',
                'Lifetime volume' => 'Volume total',
                'Avg. Rating' => 'Note moy.',
                'Host Reputation' => 'Réputation de l\'hôte',
                'Recent Bookings' => 'Réservations récentes',
                'Performance' => 'Performance',
                'Your response rate and booking conversion stats will appear here as your hosting history grows.' => 'Votre taux de réponse et vos stats de conversion apparaîtront ici au fur et à mesure.',
                
                // Listings Tab
                '+ Add New Listing' => '+ Ajouter une annonce',
                "You haven't created any listings yet." => 'Vous n\'avez pas encore créé d\'annonce.',
                'Create Your First Listing' => 'Créer votre première annonce',
                'Listing' => 'Annonce',
                'Category' => 'Catégorie',
                'Status' => 'Statut',
                'Units/Sessions' => 'Unités/Séances',
                'Actions' => 'Actions',
                'units' => 'unités',
                '+ Add unit' => '+ Ajouter une unité',
                'Edit' => 'Modifier',
                'View' => 'Voir',
                'Delete' => 'Supprimer',
                'Are you sure you want to delete this listing?' => 'Êtes-vous sûr de vouloir supprimer cette annonce ?',
                'Edit Unit' => 'Modifier l\'unité',
                'Are you sure you want to delete this unit?' => 'Êtes-vous sûr de vouloir supprimer cette unité ?',
                
                // Bookings Tab
                'Search by confirmation code or last 4 digits…' => 'Chercher par code de confirmation ou 4 derniers chiffres...',
                'Clear' => 'Effacer',
                'Export date:' => 'Date d\'export :',
                'Export CSV' => 'Exporter CSV',
                'You have no bookings yet.' => 'Vous n\'avez pas encore de réservations.',
                'Booking ID' => 'ID Réservation',
                'Dates / Details' => 'Dates / Détails',
                'Guest' => 'Voyageur',
                'Total' => 'Total',
                'Confirmation Code' => 'Code de confirmation',
                'Message Guest' => 'Contacter le voyageur',
                'ID:' => 'ID :',
                'CHECKED IN' => 'ARRIVÉ',
                'Approve this booking?' => 'Approuver cette réservation ?',
                'Approve' => 'Approuver',
                'Mark as completed?' => 'Marquer comme terminé ?',
                'Complete' => 'Terminer',
                'Check in this guest?' => 'Enregistrer l\'arrivée de ce voyageur ?',
                'Check In' => 'Arrivée',
                'Decline this booking?' => 'Refuser cette réservation ?',
                'Decline' => 'Refuser',
                'No actions' => 'Aucune action',
                'Action completed successfully!' => 'Action terminée avec succès !',
                'An unexpected error occurred. Please try again.' => 'Une erreur inattendue est survenue. Veuillez réessayer.',
                'Unauthorized: You do not have permission to perform this action.' => 'Non autorisé : vous n\'avez pas la permission d\'effectuer cette action.',
                'Security check failed. Please refresh the page and try again.' => 'Échec du contrôle de sécurité. Veuillez rafraîchir la page et réessayer.',
                'Invalid booking or listing reference.' => 'Référence de réservation ou d\'annonce invalide.',
                'Missing required information for this action.' => 'Informations requises manquantes pour cette action.',
                'Invalid listing reference.' => 'Référence d\'annonce invalide.',
                'Guest count exceeds capacity.' => 'Le nombre de voyageurs dépasse la capacité.',
                'Selected dates are unavailable (Host vacation).' => 'Dates sélectionnées indisponibles (vacances de l\'hôte).',
                'Host is not available on this day.' => 'L\'hôte n\'est pas disponible ce jour-là.',
                'Selected time is outside operating hours.' => 'L\'heure sélectionnée est en dehors des heures d\'ouverture.',
                'These dates/times are already booked.' => 'Ces dates/heures sont déjà réservées.',
                'Error creating booking. Please try again.' => 'Erreur lors de la création de la réservation. Veuillez réessayer.',
                'Invalid payment method selected.' => 'Mode de paiement sélectionné invalide.',
                'No bookings found for the selected date.' => 'Aucune réservation trouvée pour la date sélectionnée.',
                'Please select a valid date for export.' => 'Veuillez sélectionner une date valide pour l\'exportation.',
                'Price (Per Night)' => 'Prix (par nuit)',
                'Capacity/Max Guests' => 'Capacité / Max Voyageurs',
                'Amenities' => 'Équipements',
                'Price (Per Ticket)' => 'Prix (par ticket)',
                'Price (Per Person/Ticket)' => 'Prix (par personne/ticket)',
                'Total Tickets Available' => 'Total de tickets disponibles',
                'Max Tickets/Participants' => 'Max tickets / Participants',
                "What's Included" => 'Ce qui est inclus',
                'Price (Per Hour/Session)' => 'Prix (par heure/séance)',
                'Max Clients per Slot' => 'Max clients par créneau',
                'Event Fee ($)' => 'Frais d\'événement ($)',
                'Max Guests' => 'Max voyageurs',
                'Suggested Donation ($)' => 'Donation suggérée ($)',
                'Max Donors/Supporters (Optional)' => 'Max donateurs (facultatif)',
                'Donation Details' => 'Détails de la donation',
                'Price (Base)' => 'Prix (base)',
                'Storefront Settings' => 'Paramètres de la boutique',
                'View Live Storefront' => 'Voir ma boutique en ligne',
                'Customize how your host profile appears to guests. A professional storefront builds trust and increases bookings.' => 'Personnalisez votre profil d\'hôte. Une boutique professionnelle renforce la confiance et augmente les réservations.',
                'Public Profile' => 'Profil public',
                'Host / Store Name' => 'Nom de l\'hôte / boutique',
                'Host Location' => 'Localisation',
                'e.g. New York, NY' => 'ex: Paris, France',
                'Tagline (Catchy Hook)' => 'Slogan (accrocheurs)',
                'e.g. Luxury Haircare in the heart of Paris' => 'ex: Coiffure de luxe au cœur d\'Haïti',
                'Host Specialties' => 'Spécialités de l\'hôte',
                'e.g. Organic, Pet Friendly, Multilingual' => 'ex: Bio, Animaux acceptés, Multilingue',
                'Separate your specialties with commas.' => 'Séparez vos spécialités par des virgules.',
                'Description / Bio' => 'Description / Bio',
                'Tell guests about yourself or your hospitality business...' => 'Présentez-vous ou parlez de votre activité aux voyageurs...',
                'Featured Video (YouTube/Vimeo)' => 'Vidéo en vedette (YouTube/Vimeo)',
                'Share a welcoming video with your future guests.' => 'Partagez une vidéo de bienvenue avec vos futurs voyageurs.',
                'Instagram Profile' => 'Profil Instagram',
                'Facebook Page' => 'Page Facebook',
                'Branding & Identity' => 'Branding & Identité',
                'Host Logo' => 'Logo de l\'hôte',
                'Remove' => 'Supprimer',
                'Store Banner' => 'Bannière de la boutique',
                'Update My Storefront' => 'Mettre à jour ma boutique',
                'Support & Assistance' => 'Support & Assistance',
                'Ticket submitted successfully! Our team will review it and get back to you.' => 'Ticket envoyé avec succès ! Notre équipe va l\'examiner et vous reviendra.',
                'Open New Ticket' => 'Ouvrir un nouveau ticket',
                'Subject' => 'Sujet',
                'How can we help?' => 'Comment pouvons-nous vous aider ?',
                'Message Detail' => 'Détails du message',
                'Describe your issue or question...' => 'Décrivez votre problème ou question...',
                'Create Ticket' => 'Créer un ticket',
                'Support History' => 'Historique du support',
                'No support history found.' => 'Aucun historique de support trouvé.',
                'Last updated: %s' => 'Dernière mise à jour : %s',
                'View Conversation →' => 'Voir la conversation →',
                'Business Performance' => 'Performance de l\'activité',
                'to' => 'à',
                'From' => 'Du',
                'To' => 'Au',
                'Filter Stats' => 'Filtrer les stats',
                'Reset' => 'Réinitialiser',
                'Available Balance' => 'Solde disponible',
                'Ready for withdrawal' => 'Prêt pour retrait',
                'Period Earnings (Net)' => 'Gains de la période (Nets)',
                'After platform commission' => 'Après commission de la plateforme',
                'In selected period' => 'Sur la période sélectionnée',
                'Revenue Growth Trend' => 'Tendance de croissance des revenus',
                'Withdraw Available Balance (%s)' => 'Retirer le solde disponible (%s)',
                'Minimum %s required to withdraw' => 'Minimum de %s requis pour retirer',
                'Earnings (Net)' => 'Gains (Nets)',
                'Payout History' => 'Historique des paiements',
                'Payout Preferences' => 'Préférences de paiement',
                'Payout Method' => 'Mode de paiement',
                'Select Method...' => 'Choisir un mode...',
                'Availability Settings' => 'Paramètres de disponibilité',
                'Set your default weekly business hours and block out specific dates for vacations or maintenance.' => 'Définissez vos horaires d\'ouverture hebdomadaires et bloquez des dates spécifiques pour vos vacances.',
                'Business Hours' => 'Heures d\'ouverture',
                'Monday' => 'Lundi',
                'Tuesday' => 'Mardi',
                'Wednesday' => 'Mercredi',
                'Thursday' => 'Jeudi',
                'Friday' => 'Vendredi',
                'Saturday' => 'Samedi',
                'Sunday' => 'Dimanche',
                'Vacation / Blocked Dates' => 'Vacances / Dates bloquées',
                'Start Date' => 'Date de début',
                'End Date' => 'Date de fin',
                'Reason (Optional)' => 'Raison (Optionnel)',
                'e.g. Renovation' => 'ex: Rénovation',
                '+ Add Blocked Date Range' => '+ Ajouter une période bloquée',
                'Save Availability Settings' => 'Enregistrer les paramètres de disponibilité',
                'Inbox' => 'Boîte de réception',
                'Platform Broadcasts' => 'Annonces de la plateforme',
                'Stay updated with official announcements from the Obenlo team.' => 'Restez informé des annonces officielles de l\'équipe Obenlo.',
                'Policies & Rules' => 'Politiques & Règles',
                'Use Obenlo Global Policies (Standard)' => 'Utiliser les politiques globales d\'Obenlo (standard)',
                'Set Custom Policies' => 'Définir des politiques personnalisées',
                'Cancellation Policy' => 'Politique d\'annulation',
                'Refund Policy' => 'Politique de remboursement',
                'House Rules / Other' => 'Règlement intérieur / Autre',
                'Save Payout Preferences' => 'Enregistrer les préférences de paiement',
                'Are you sure you want to request a payout of your entire available balance?' => 'Êtes-vous sûr de vouloir demander le paiement de la totalité de votre solde disponible ?',
                'Processing...' => 'Traitement...',
                'Withdraw Earnings' => 'Retirer les gains',
                'Error submitting request. Please try again.' => 'Erreur lors de l\'envoi de la demande. Veuillez réessayer.',

                'Search' => 'Rechercher',
                'Subject:' => 'Sujet :',
                'Brief summary of the issue' => 'Bref résumé du problème',
                'Describe the Issue:' => 'Décrivez le problème :',
                'Please provide as much detail as possible...' => 'Veuillez fournir autant de détails que possible...',
                'Evidence / Photo (Optional):' => 'Preuve / Photo (facultatif) :',
                'If this is a dispute, please upload photos to support your claim.' => 'S\'il s\'agit d\'un litige, veuillez télécharger des photos pour appuyer votre demande.',
                'Submit Ticket to %s' => 'Soumettre le ticket à %s',
                'Last 30 Days' => 'Les 30 derniers jours',
                'Date' => 'Date',
                'Amount' => 'Montant',
                'Payment Details' => 'Détails de paiement',
                'Saving...' => 'Enregistrement...',
                'unauthorized' => 'non autorisé',
                'security_failed' => 'échec de sécurité',
                '&larr; View All FAQs' => '&larr; Voir toutes les FAQ',
                'Contact Support' => 'Contacter le support',
                'Your safety is our top priority. We implement world-class standards to keep our community secure.' => 'Votre sécurité est notre priorité absolue. Nous mettons en œuvre des normes de classe mondiale pour assurer la sécurité de notre communauté.',
                'Secure Payments' => 'Paiements sécurisés',
                'Every transaction is encrypted and managed through our secure payment partners.' => 'Chaque transaction est cryptée et gérée par nos partenaires de paiement sécurisés.',
                'Verified Profiles' => 'Profils vérifiés',
'We encourage users to verify their identities to build a more transparent community.' => 'Nous encourageons les utilisateurs à vérifier leur identité pour construire une communauté plus transparente.',
                '24/7 Support' => 'Support 24/7',
                'Our dedicated team is always here to help with any safety concerns.' => 'Notre équipe dédiée est toujours là pour vous aider en cas de problème de sécurité.',
                'Report a Concern' => 'Signaler un problème',
                'If you experience any issues or feel unsafe, please contact our emergency support team immediately.' => 'Si vous rencontrez des problèmes ou si vous ne vous sentez pas en sécurité, veuillez contacter immédiatement notre équipe de support d\'urgence.',
                'Add New Unit/Session' => 'Ajouter une nouvelle unité/séance',
                'Add-ons (Optional Upsells)' => 'Suppléments (Ventes incitatives optionnelles)',
                'Address / Location' => 'Adresse / Lieu',
                'Are you sure you want to approve this booking?' => 'Êtes-vous sûr de vouloir approuver cette réservation ?',
                'Are you sure you want to decline this booking?' => 'Êtes-vous sûr de vouloir refuser cette réservation ?',
                'Are you sure you want to delete this listing?' => 'Êtes-vous sûr de vouloir supprimer cette annonce ?',
                'Are you sure you want to mark this booking as checked-in?' => 'Êtes-vous sûr de vouloir enregistrer l\'arrivée de ce voyageur ?',
                'Booking Status' => 'Statut de réservation',
                'Bookings Management' => 'Gestion des réservations',
                'Manage your bookings, approve requests, and keep track of your guest schedule.' => 'Gérez vos réservations, approuvez les demandes et suivez le planning de vos voyageurs.',
                'No reviews yet.' => 'Pas encore d\'avis.',
                'Search bookings...' => 'Rechercher des réservations...',
                'See what your guests are saying about your hospitality.' => 'Découvrez ce que vos voyageurs disent de votre accueil.',
                'View all your active and pending listings. Add new units or manage existing ones.' => 'Consultez toutes vos annonces actives et en attente. Ajoutez de nouvelles unités ou gérez les existantes.',
                'About this Unit / Session' => 'À propos de cette unité / séance',
                'About your Business / Property' => 'À propos de votre entreprise / propriété',
                'Adding a specific bookable unit to:' => 'Ajout d\'une unité réservable à :',
                'Base Price' => 'Prix de base',
                'Basic Information' => 'Informations de base',
                'Business Profile Mode' => 'Mode profil d\'entreprise',
                'Business / Property Name' => 'Nom de l\'entreprise / propriété',
                'By saving, you agree to Obenlo\'s hosting standard and quality guidelines.' => 'En enregistrant, vous acceptez les normes d\'hébergement et les directives de qualité d\'Obenlo.',
                'Click to upload photos' => 'Cliquez pour télécharger des photos',
                'Create the main property, experience, or service. (You will add specific bookable units later).' => 'Créez la propriété, l\'expérience ou le service principal. (Vous ajouterez des unités réservables plus tard).',
                'Demo Listing Configuration' => 'Configuration de l\'annonce démo',
                'Demo Host Name' => 'Nom de l\'hôte démo',
                'Demo Host Tagline' => 'Slogan de l\'hôte démo',
                'Demo Host Location' => 'Localisation de l\'hôte démo',
                'Demo Host Bio' => 'Bio de l\'hôte démo',
                'Describe this specific unit, room, or session...' => 'Décrivez cette unité, chambre ou séance spécifique...',
                'Describe your overall business, property, or service group...' => 'Décrivez votre entreprise, propriété ou groupe de services global...',
                'Edit Listing: %s' => 'Modifier l\'annonce : %s',
                'Edit Unit/Session: %s' => 'Modifier l\'unité/séance : %s',
                'Event Schedule & Location' => 'Horaires et lieu de l\'événement',
                'Event Type' => 'Type d\'événement',
                'Event Address' => 'Adresse de l\'événement',
                'Virtual Meeting Link' => 'Lien de réunion virtuelle',
                'Specific Scheduled Time (e.g., Monday 8 April, 4pm-10pm)' => 'Horaire programmé spécifique (ex: lundi 8 avril, 16h-22h)',
                'Requires Booking Time Slots' => 'Nécessite des créneaux de réservation',
                'Check this if you want the calendar to automatically show available time slots during your business hours (e.g. for Haircuts, Spa treatments).' => 'Cochez cette case si vous souhaitez que le calendrier affiche automatiquement les créneaux disponibles pendant vos heures d\'ouverture.',
                'Save Listing Now' => 'Enregistrer l\'annonce maintenant',
                'Save Unit Now' => 'Enregistrer l\'unité maintenant',
                'Back to Listings' => 'Retour aux annonces',
                'Invalid listing.' => 'Annonce invalide.',
                '-- Select a Category --' => '-- Choisir une catégorie --',
                'e.g. Tulum, Mexico' => 'ex : Port-au-Prince, Haïti',
            ),
            'es' => array(
                // Menu & Global
                'Switch to hosting' => 'Cambiar a modo anfitrión',
                'Trips' => 'Viajes',
                'Wishlists' => 'Favoritos',
                'Log out' => 'Cerrar sesión',
                'Log in / Sign up' => 'Iniciar sesión / Registrarse',
                'Log in' => 'Iniciar sesión',
                'Sign up' => 'Registrarse',
                'Host Dashboard' => 'Panel de Anfitrión',
                'Account' => 'Cuenta',
                'Messages' => 'Mensajes',
                'Support / Dispute' => 'Soporte / Disputa',
                'Help / Support' => 'Ayuda / Soporte',
                'Site Admin' => 'Admin del sitio',
                'Search listings, categories, locations or hosts...' => 'Buscar anuncios, categorías, lugares o anfitriones...',
                'Stay' => 'Estancia',
                'Experience' => 'Experiencia',
                'Service' => 'Servicio',
                'Event' => 'Evento',
                'Show all' => 'Mostrar todo',
                'Listings by' => 'Anuncios de',
                'Hosted by' => 'Anfitrión:',
                'Starting from' => 'Desde',
                'total' => 'total',
                'reviews' => 'reseñas',
                'review' => 'reseña',
                
                // Dashboard Layout
                'Overview' => 'Resumen',
                'My Listings' => 'Mis anuncios',
                'Bookings' => 'Reservas',
                'Storefront' => 'Escaparate',
                'Reviews' => 'Reseñas',
                'Broadcasts' => 'Anuncios',
                'Availability' => 'Disponibilidad',
                'Payout Settings' => 'Pagos',
                'Support' => 'Soporte',
                'View Storefront' => 'Ver escaparate',
                'Dashboard Overview' => 'Resumen del panel',
                'Account Status:' => 'Estado de la cuenta:',
                'Complete Verification →' => 'Completar verificación →',
                'Update Payouts' => 'Actualizar pagos',
                'Preview My Storefront' => 'Ver mi escaparate',
                
                // Dashboard Stats
                'Total Earnings' => 'Ganancias totales',
                'Completed Bookings' => 'Reservas completadas',
                'Active Listings' => 'Anuncios activos',
                'Live on Obenlo' => 'En línea en Obenlo',
                'Total Bookings' => 'Total de reservas',
                'Lifetime volume' => 'Volumen total',
                'Avg. Rating' => 'Calificación prom.',
                'Host Reputation' => 'Reputación del anfitrión',
                'Recent Bookings' => 'Reservas recientes',
                'Performance' => 'Rendimiento',
                'Your response rate and booking conversion stats will appear here as your hosting history grows.' => 'Tu tasa de respuesta y estadísticas de conversión aparecerán aquí a medida que crezca tu historial.',
                
                // Listings Tab
                '+ Add New Listing' => '+ Añadir nuevo anuncio',
                "You haven't created any listings yet." => 'Aún no has creado ningún anuncio.',
                'Create Your First Listing' => 'Crea tu primer anuncio',
                'Listing' => 'Anuncio',
                'Category' => 'Categoría',
                'Status' => 'Estado',
                'Units/Sessions' => 'Unidades/Sesiones',
                'Actions' => 'Acciones',
                'units' => 'unidades',
                '+ Add unit' => '+ Añadir unidad',
                'Edit' => 'Editar',
                'View' => 'Ver',
                'Delete' => 'Eliminar',
                'Are you sure you want to delete this listing?' => '¿Estás seguro de que deseas eliminar este anuncio?',
                'Edit Unit' => 'Editar unidad',
                'Are you sure you want to delete this unit?' => '¿Estás seguro de que deseas eliminar esta unidad?',
                
                // Bookings Tab
                'Search by confirmation code or last 4 digits…' => 'Buscar por código de confirmación o últimos 4 dígitos...',
                'Clear' => 'Limpiar',
                'Export date:' => 'Fecha de exportación:',
                'Export CSV' => 'Exportar CSV',
                'You have no bookings yet.' => 'Aún no tienes reservas.',
                'Booking ID' => 'ID de Reserva',
                'Dates / Details' => 'Fechas / Detalles',
                'Guest' => 'Huésped',
                'Total' => 'Total',
                'Confirmation Code' => 'Código de confirmación',
                'Message Guest' => 'Enviar mensaje al huésped',
                'ID:' => 'ID:',
                'CHECKED IN' => 'REGISTRADO',
                'Approve this booking?' => '¿Aprobar esta reserva?',
                'Approve' => 'Aprobar',
                'Mark as completed?' => '¿Marcar como completada?',
                'Complete' => 'Completar',
                'Check in this guest?' => '¿Registrar entrada de este huésped?',
                'Check In' => 'Entrada',
                'Decline this booking?' => '¿Rechazar esta reserva?',
                'Decline' => 'Rechazar',
                'No actions' => 'Sin acciones',
                'Action completed successfully!' => '¡Acción completada con éxito!',
                'An unexpected error occurred. Please try again.' => 'Ocurrió un error inesperado. Por favor, inténtelo de nuevo.',
                'Unauthorized: You do not have permission to perform this action.' => 'No autorizado: no tiene permiso para realizar esta acción.',
                'Security check failed. Please refresh the page and try again.' => 'Error en el control de seguridad. Por favor, actualice la página e inténtelo de nuevo.',
                'Invalid booking or listing reference.' => 'Referencia de reserva o anuncio no válida.',
                'Missing required information for this action.' => 'Falta información requerida para esta acción.',
                'Invalid listing reference.' => 'Referencia de anuncio no válida.',
                'Guest count exceeds capacity.' => 'El número de huéspedes excede la capacidad.',
                'Selected dates are unavailable (Host vacation).' => 'Las fechas seleccionadas no están disponibles (vacaciones del anfitrión).',
                'Host is not available on this day.' => 'El anfitrión no está disponible este día.',
                'Selected time is outside operating hours.' => 'La hora seleccionada está fuera del horario de atención.',
                'These dates/times are already booked.' => 'Estas fechas/horas ya están reservadas.',
                'Error creating booking. Please try again.' => 'Error al crear la reserva. Por favor, inténtelo de nuevo.',
                'Invalid payment method selected.' => 'Método de pago seleccionado no válido.',
                'No bookings found for the selected date.' => 'No se encontraron reservas para la fecha seleccionada.',
                'Please select a valid date for export.' => 'Por favor, seleccione una fecha válida para la exportación.',
                'Price (Per Night)' => 'Precio (por noche)',
                'Capacity/Max Guests' => 'Capacidad / Máx Huéspedes',
                'Amenities' => 'Servicios',
                'Price (Per Ticket)' => 'Precio (por ticket)',
                'Price (Per Person/Ticket)' => 'Precio (por persona/ticket)',
                'Total Tickets Available' => 'Total de boletos disponibles',
                'Max Tickets/Participants' => 'Máx boletos / Participantes',
                "What's Included" => 'Qué está incluido',
                'Price (Per Hour/Session)' => 'Precio (por hora/sesión)',
                'Max Clients per Slot' => 'Máx clientes por turno',
                'Event Fee ($)' => 'Tarifa de evento ($)',
                'Max Guests' => 'Máx huéspedes',
                'Suggested Donation ($)' => 'Donación sugerida ($)',
                'Max Donors/Supporters (Optional)' => 'Máx donantes (opcional)',
                'Donation Details' => 'Detalles de la donación',
                'Price (Base)' => 'Precio (base)',
                'Storefront Settings' => 'Ajustes del escaparate',
                'View Live Storefront' => 'Ver escaparate en vivo',
                'Customize how your host profile appears to guests. A professional storefront builds trust and increases bookings.' => 'Personaliza cómo aparece tu perfil ante los huéspedes. Un escaparate profesional genera confianza y aumenta las reservas.',
                'Public Profile' => 'Perfil público',
                'Host / Store Name' => 'Nombre del anfitrión / tienda',
                'Host Location' => 'Ubicación',
                'e.g. New York, NY' => 'ej: Madrid, España',
                'Tagline (Catchy Hook)' => 'Eslogan (gancho)',
                'e.g. Luxury Haircare in the heart of Paris' => 'ej: Peluquería de lujo en el corazón de Madrid',
                'Host Specialties' => 'Especialidades del anfitrión',
                'e.g. Organic, Pet Friendly, Multilingual' => 'ej: Orgánico, Mascotas permitidas, Multilingüe',
                'Separate your specialties with commas.' => 'Separa tus especialidades con comas.',
                'Description / Bio' => 'Descripción / Bio',
                'Tell guests about yourself or your hospitality business...' => 'Cuéntale a los huéspedes sobre ti o tu negocio de hospitalidad...',
                'Featured Video (YouTube/Vimeo)' => 'Video destacado (YouTube/Vimeo)',
                'Share a welcoming video with your future guests.' => 'Comparte un video de bienvenida con tus futuros huéspedes.',
                'Instagram Profile' => 'Perfil de Instagram',
                'Facebook Page' => 'Página de Facebook',
                'Branding & Identity' => 'Branding e Identidad',
                'Host Logo' => 'Logo del anfitrión',
                'Remove' => 'Eliminar',
                'Store Banner' => 'Banner de la tienda',
                'Update My Storefront' => 'Actualizar mi escaparate',
                'Support & Assistance' => 'Soporte y Asistencia',
                'Ticket submitted successfully! Our team will review it and get back to you.' => '¡Ticket enviado con éxito! Nuestro equipo lo revisará y se pondrá en contacto contigo.',
                'Open New Ticket' => 'Abrir nuevo ticket',
                'Subject' => 'Asunto',
                'How can we help?' => '¿Cómo podemos ayudarte?',
                'Message Detail' => 'Detalles del mensaje',
                'Describe your issue or question...' => 'Describe tu problema o pregunta...',
                'Create Ticket' => 'Crear un ticket',
                'Support History' => 'Historial de soporte',
                'No support history found.' => 'No se encontró historial de soporte.',
                'Last updated: %s' => 'Última actualización: %s',
                'View Conversation →' => 'Ver conversación →',
                'Business Performance' => 'Rendimiento comercial',
                'to' => 'a',
                'From' => 'Desde',
                'To' => 'Hasta',
                'Filter Stats' => 'Filtrar estadísticas',
                'Reset' => 'Restablecer',
                'Available Balance' => 'Saldo disponible',
                'Ready for withdrawal' => 'Listo para retiro',
                'Period Earnings (Net)' => 'Ganancias del período (Netas)',
                'After platform commission' => 'Después de la comisión de la plataforma',
                'In selected period' => 'En el período seleccionado',
                'Revenue Growth Trend' => 'Tendencia de crecimiento de ingresos',
                'Withdraw Available Balance (%s)' => 'Retirar saldo disponible (%s)',
                'Minimum %s required to withdraw' => 'Mínimo de %s requerido para retirar',
                'Earnings (Net)' => 'Ganancias (Netas)',
                'Payout History' => 'Historial de pagos',
                'Payout Preferences' => 'Preferencias de pago',
                'Payout Method' => 'Método de pago',
                'Select Method...' => 'Seleccionar método...',
                'Availability Settings' => 'Ajustes de disponibilidad',
                'Set your default weekly business hours and block out specific dates for vacations or maintenance.' => 'Define tus horarios comerciales semanales y bloquea fechas específicas para vacaciones o mantenimiento.',
                'Business Hours' => 'Horario comercial',
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo',
                'Vacation / Blocked Dates' => 'Vacaciones / Fechas bloqueadas',
                'Start Date' => 'Fecha de inicio',
                'End Date' => 'Fecha de fin',
                'Reason (Optional)' => 'Motivo (Opcional)',
                'e.g. Renovation' => 'ej: Renovación',
                '+ Add Blocked Date Range' => '+ Añadir rango de fechas bloqueadas',
                'Save Availability Settings' => 'Guardar ajustes de disponibilidad',
                'Inbox' => 'Bandeja de entrada',
                'Platform Broadcasts' => 'Anuncios de la plataforma',
                'Stay updated with official announcements from the Obenlo team.' => 'Mantente al día con los anuncios oficiales del equipo de Obenlo.',
                'Policies & Rules' => 'Políticas y reglas',
                'Use Obenlo Global Policies (Standard)' => 'Usar las políticas globales de Obenlo (estándar)',
                'Set Custom Policies' => 'Establecer políticas personalizadas',
                'Cancellation Policy' => 'Política de cancelación',
                'Refund Policy' => 'Política de reembolso',
                'House Rules / Other' => 'Reglas de la casa / Otros',
                'Save Payout Preferences' => 'Guardar preferencias de pago',
                'Are you sure you want to request a payout of your entire available balance?' => '¿Estás seguro de que deseas solicitar un pago de todo tu saldo disponible?',
                'Processing...' => 'Procesando...',
                'Withdraw Earnings' => 'Retirar ganancias',
                'Error submitting request. Please try again.' => 'Error al enviar la solicitud. Por favor, inténtelo de nuevo.',

                'Search' => 'Buscar',
                'Subject:' => 'Asunto:',
                'Brief summary of the issue' => 'Resumen breve del problema',
                'Describe the Issue:' => 'Describe el problema:',
                'Please provide as much detail as possible...' => 'Por favor, proporciona tantos detalles como sea posible...',
                'Evidence / Photo (Optional):' => 'Evidencia / Foto (Opcional):',
                'If this is a dispute, please upload photos to support your claim.' => 'Si se trata de una disputa, sube fotos para respaldar tu reclamación.',
                'Submit Ticket to %s' => 'Enviar ticket a %s',
                'Last 30 Days' => 'Últimos 30 días',
                'Date' => 'Fecha',
                'Amount' => 'Monto',
                'Payment Details' => 'Detalles de pago',
                'Saving...' => 'Guardando...',
                '&larr; View All FAQs' => '&larr; Ver todas las preguntas frecuentes',
                'Contact Support' => 'Contactar a soporte',
                '24/7 Support' => 'Soporte 24/7',
                'Report a Concern' => 'Reportar una preocupación',
                'Contact Safety Team' => 'Contactar al equipo de seguridad',
                'The %s Blog' => 'El blog de %s',
                'Thanks for subscribing!' => '¡Gracias por suscribirte!',
                'Your email address' => 'Tu dirección de correo electrónico',
                'Subscribe' => 'Suscribirse',
                'You' => 'Tú',
                'How Obenlo Works' => 'Cómo funciona Obenlo',
                'For Guests' => 'Para huéspedes',
                'For Hosts' => 'Para anfitriones',
                'Become a Host' => 'Conviértete en anfitrión',
                'Explore Obenlo' => 'Explorar Obenlo',
                'Add New Unit/Session' => 'Añadir nueva unidad/sesión',
                'Add-ons (Optional Upsells)' => 'Complementos (Ventas adicionales opcionales)',
                'Address / Location' => 'Dirección / Ubicación',
                'Are you sure you want to approve this booking?' => '¿Estás seguro de que deseas aprobar esta reserva?',
                'Are you sure you want to decline this booking?' => '¿Estás seguro de que deseas rechazar esta reserva?',
                'Are you sure you want to delete this listing?' => '¿Estás seguro de que deseas eliminar este anuncio?',
                'Are you sure you want to mark this booking as checked-in?' => '¿Estás seguro de que deseas registrar la entrada de este huésped?',
                'Booking Status' => 'Estado de la reserva',
                'Bookings Management' => 'Gestión de reservas',
                'Manage your bookings, approve requests, and keep track of your guest schedule.' => 'Gestiona tus reservas, aprueba solicitudes y haz un seguimiento de la agenda de tus huéspedes.',
                'No reviews yet.' => 'Aún no hay reseñas.',
                'Search bookings...' => 'Buscar reservas...',
                'See what your guests are saying about your hospitality.' => 'Mira lo que dicen tus huéspedes sobre tu hospitalidad.',
                'View all your active and pending listings. Add new units or manage existing ones.' => 'Mira todos tus anuncios activos y pendientes. Añade nuevas unidades o gestiona las existentes.',
                'About this Unit / Session' => 'Acerca de esta unidad / sesión',
                'About your Business / Property' => 'Acerca de tu negocio / propiedad',
                'Adding a specific bookable unit to:' => 'Añadiendo una unidad reservable a:',
                'Base Price' => 'Precio base',
                'Basic Information' => 'Información básica',
                'Business Profile Mode' => 'Modo de perfil de negocio',
                'Business / Property Name' => 'Nombre del negocio / propiedad',
                'By saving, you agree to Obenlo\'s hosting standard and quality guidelines.' => 'Al guardar, aceptas los estándares de alojamiento y las pautas de calidad de Obenlo.',
                'Click to upload photos' => 'Haz clic para subir fotos',
                'Create the main property, experience, or service. (You will add specific bookable units later).' => 'Crea la propiedad, experiencia o servicio principal. (Añadirás unidades reservables más tarde).',
                'Demo Listing Configuration' => 'Configuración de anuncio de demostración',
                'Demo Host Name' => 'Nombre del anfitrión de demostración',
                'Demo Host Tagline' => 'Eslogan del anfitrión de demostración',
                'Demo Host Location' => 'Ubicación del anfitrión de demostración',
                'Demo Host Bio' => 'Biografía del anfitrión de demostración',
                'Describe this specific unit, room, or session...' => 'Describe esta unidad, habitación o sesión específica...',
                'Describe your overall business, property, or service group...' => 'Describe tu negocio, propiedad o grupo de servicios en general...',
                'Edit Listing: %s' => 'Editar anuncio: %s',
                'Edit Unit/Session: %s' => 'Editar unidad/sesión: %s',
                'Event Schedule & Location' => 'Horario y ubicación del evento',
                'Event Type' => 'Tipo de evento',
                'Event Address' => 'Dirección del evento',
                'Virtual Meeting Link' => 'Enlace de reunión virtual',
                'Specific Scheduled Time (e.g., Monday 8 April, 4pm-10pm)' => 'Horario programado específico (ej. lunes 8 de abril, 4pm-10pm)',
                'Requires Booking Time Slots' => 'Requiere turnos de reserva',
                'Check this if you want the calendar to automatically show available time slots during your business hours (e.g. for Haircuts, Spa treatments).' => 'Marca esto si deseas que el calendario muestre automáticamente los turnos disponibles durante tus horas comerciales.',
                'Save Listing Now' => 'Guardar anuncio ahora',
                'Save Unit Now' => 'Guardar unidad ahora',
                'Back to Listings' => 'Volver a los anuncios',
                'Invalid listing.' => 'Anuncio no válido.',
                '-- Select a Category --' => '-- Selecciona una categoría --',
                'e.g. Tulum, Mexico' => 'ej. Tulum, México',
            ),
        );

        // Merge with dynamic translations from options
        $dynamic_es = get_option('obenlo_i18n_es', array());
        if (!empty($dynamic_es)) {
            $this->translations['es'] = array_merge($this->translations['es'], $dynamic_es);
        }

        $dynamic_fr = get_option('obenlo_i18n_fr', array());
        if (!empty($dynamic_fr)) {
            $this->translations['fr'] = array_merge($this->translations['fr'], $dynamic_fr);
        }
    }

    public function translate_custom_strings($translated_text, $text, $domain)
    {
        if ($this->current_lang === 'en') {
            return $translated_text;
        }

        // Check our custom dictionary first
        if (isset($this->translations[$this->current_lang][$text])) {
            return $this->translations[$this->current_lang][$text];
        }

        return $translated_text;
    }

    public function translate_custom_strings_context($translated_text, $text, $context, $domain)
    {
        return $this->translate_custom_strings($translated_text, $text, $domain);
    }

    public function translate_dynamic_content($content)
    {
        if (empty($content) || !is_string($content)) {
            return $content;
        }

        // 1. Check dictionary (Manual Translations)
        if ($this->current_lang !== 'en' && isset($this->translations[$this->current_lang][$content])) {
            return $this->translations[$this->current_lang][$content];
        }

        // 2. Automate Fallback for Listing Titles, Summaries, etc.
        // If it's NOT in the manual dictionary, we let the bridge handle it.
        // This includes translating French listing titles into English for English users.
        if (get_option('obenlo_enable_google_translate', '0') === '1') {
            return $this->auto_translate_fallback($content, $this->current_lang);
        }

        return $content;
    }

    private function auto_translate_fallback($text, $target_lang)
    {
        if (empty($text) || !is_string($text) || strlen($text) < 2) {
            return $text;
        }

        $cache_key = 'obenlo_tr_' . $target_lang . '_' . md5($text);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        // Bridge to public translation endpoint
        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=" . $target_lang . "&dt=t&q=" . urlencode($text);
        $response = wp_remote_get($url, array('timeout' => 5));

        if (is_wp_error($response)) {
            return $text;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (!empty($result) && isset($result[0][0][0])) {
            $translated = $result[0][0][0];
            // Cache for 24 hours to balance freshness and performance
            set_transient($cache_key, $translated, DAY_IN_SECONDS);
            return $translated;
        }

        return $text;
    }

    public function translate_meta_content($value, $object_id, $meta_key, $single)
    {
        // Skip internal/protected meta (non-Obenlo)
        if (strpos($meta_key, '_') === 0 && strpos($meta_key, '_obenlo_') !== 0) {
            return $value;
        }

        // Also Skip specific Obenlo numeric/ID fields that don't need translation
        $skip_keys = array('_obenlo_price', '_obenlo_capacity', '_obenlo_total_price', '_obenlo_booking_status');
        if (in_array($meta_key, $skip_keys)) {
            return $value;
        }

        // Only translate strings
        if (is_string($value)) {
            // Priority 1: Dictionary (Manual)
            if ($this->current_lang !== 'en' && isset($this->translations[$this->current_lang][$value])) {
                return $this->translations[$this->current_lang][$value];
            }
            // Priority 2: Auto-Translate (Bridge)
            if (get_option('obenlo_enable_google_translate', '0') === '1') {
                return $this->auto_translate_fallback($value, $this->current_lang);
            }
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                if (is_string($v)) {
                    if ($this->current_lang !== 'en' && isset($this->translations[$this->current_lang][$v])) {
                        $value[$k] = $this->translations[$this->current_lang][$v];
                    } elseif (get_option('obenlo_enable_google_translate', '0') === '1') {
                        $value[$k] = $this->auto_translate_fallback($v, $this->current_lang);
                    }
                }
            }
        }

        return $value;
    }

    public function translate_menu_items($items)
    {
        if ($this->current_lang === 'en' || empty($items)) {
            return $items;
        }

        foreach ($items as $item) {
            if (isset($this->translations[$this->current_lang][$item->title])) {
                $item->title = $this->translations[$this->current_lang][$item->title];
            }
        }

        return $items;
    }
}
