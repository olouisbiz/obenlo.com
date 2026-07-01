<?php
/**
 * Obenlo AI Affiliate Outreach
 *
 * Provides a dedicated administrator dashboard tab to email imported
 * affiliate listings and urge them to claim their native profile on Obenlo.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Affiliate_Outreach {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ], 30 );
    }

    public function register_menu() {
        add_submenu_page(
            'obenlo-ai-settings',
            'Affiliate Outreach',
            '📨 Affiliate Outreach',
            'manage_options',
            'obenlo-ai-affiliate-outreach',
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get all listings that are NOT natively owned (i.e. they are affiliate imports)
        $args = [
            'post_type'      => 'listing',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_obenlo_listing_engine',
                    'value'   => ['viator', 'seatgeek', 'travelpayouts', 'groupon', 'ticketmaster'],
                    'compare' => 'IN'
                ],
                [
                    'relation' => 'OR',
                    [
                        'key'     => '_obenlo_claim_pending',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key'     => '_obenlo_claim_pending',
                        'value'   => 'yes',
                        'compare' => '!='
                    ]
                ]
            ]
        ];

        $listings = get_posts( $args );
        ?>
        <div class="wrap">
            <h1>📨 Affiliate Outreach Hub</h1>
            <p>These are listings you have imported via Affiliate APIs. Send them an email to claim their business on Obenlo natively!</p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Listing Name</th>
                        <th>Location</th>
                        <th>API Engine</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $listings ) ) : ?>
                        <tr><td colspan="4">No un-claimed affiliate listings found. Go import some!</td></tr>
                    <?php else : ?>
                        <?php foreach ( $listings as $listing ) : 
                            $engine = get_post_meta( $listing->ID, '_obenlo_listing_engine', true );
                            $location = get_post_meta( $listing->ID, '_obenlo_listing_location', true );
                            $claim_url = home_url( '/login?claim_id=' . $listing->ID . '#signup' );
                            
                            $subject = rawurlencode("We've featured " . $listing->post_title . " on Obenlo!");
                            $body = rawurlencode(
                                "Hello!\n\n" .
                                "We recently featured your business, " . $listing->post_title . ", on Obenlo - the premium global discovery network.\n\n" .
                                "You can view your listing here: " . get_permalink( $listing->ID ) . "\n\n" .
                                "We are currently redirecting our users to your affiliate link, but we'd love for you to claim your profile natively! " .
                                "By claiming your profile, you pay only 10% commission (instead of 25-30% on OTAs) and get full access to our native booking tools and AI SEO features.\n\n" .
                                "Click here to claim your business in 2 minutes: " . $claim_url . "\n\n" .
                                "Best regards,\nThe Obenlo Team"
                            );
                            
                            $mailto_link = "mailto:?subject={$subject}&body={$body}";
                        ?>
                            <tr>
                                <td><strong><a href="<?php echo get_permalink($listing->ID); ?>" target="_blank"><?php echo esc_html( $listing->post_title ); ?></a></strong></td>
                                <td><?php echo esc_html( $location ); ?></td>
                                <td><span style="background:#e5e7eb; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:bold; text-transform:uppercase;"><?php echo esc_html( $engine ); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_attr( $mailto_link ); ?>" class="button button-primary" style="background:#e61e4d; border-color:#e61e4d;">Draft Email Template</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
