<?php
/**
 * Review & Rating Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Reviews {

    public function init() {
        // Save rating when a comment is posted
        add_action( 'comment_post', array( $this, 'save_comment_rating' ), 10, 3 );
        
        // Add rating to comment text in admin/backend if needed
        add_filter( 'comment_text', array( $this, 'display_rating_in_comment' ), 10, 2 );

        // Customize review notifications
        add_filter( 'comment_notification_subject', array( $this, 'custom_review_notification_subject' ), 10, 2 );
        add_filter( 'comment_notification_text', array( $this, 'custom_review_notification_text' ), 10, 2 );

        // Disable default synchronous review notifications for listing authors
        add_filter( 'notify_postauthor', array( $this, 'maybe_disable_core_review_notification' ), 10, 2 );
    }

    /**
     * Disable the default WordPress synchronous email for reviews.
     */
    public function maybe_disable_core_review_notification( $notify, $comment_id ) {
        $comment = get_comment( $comment_id );
        if ( $comment ) {
            $post = get_post( $comment->comment_post_ID );
            if ( $post && $post->post_type === 'listing' ) {
                return false; // Skip core sync email
            }
        }
        return $notify;
    }

    /**
     * Save the rating from the comment form
     */
    public function save_comment_rating( $comment_id, $comment_approved, $commentdata ) {
        if ( isset( $_POST['rating'] ) && ! empty( $_POST['rating'] ) ) {
            $rating = intval( $_POST['rating'] );
            add_comment_meta( $comment_id, '_obenlo_rating', $rating );
        }

        // Trigger our background notification (asynchronous)
        Obenlo_Booking_Notifications::schedule_review_notification( $comment_id );
    }

    /**
     * Display star rating in comment text (frontend/admin)
     */
    public function display_rating_in_comment( $comment_text, $comment ) {
        if ( is_admin() || strpos($_SERVER['REQUEST_URI'], 'wp-admin') !== false ) {
             $rating = get_comment_meta( $comment->comment_ID, '_obenlo_rating', true );
             if ( $rating ) {
                 $stars = str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating );
                 $comment_text = '<div class="obenlo-rating" style="color: #FFD700; font-weight: bold; margin-bottom: 5px;">' . $stars . ' (' . $rating . '/5)</div>' . $comment_text;
             }
        }
        return $comment_text;
    }

    /**
     * Get average rating for a listing
     */
    public static function get_listing_average_rating( $listing_id ) {
        $comments = get_comments( array(
            'post_id' => $listing_id,
            'status'  => 'approve',
            'parent'  => 0 // Only top-level reviews
        ) );

        if ( empty( $comments ) ) {
            return 0;
        }

        $total_rating = 0;
        $count = 0;
        foreach ( $comments as $comment ) {
            $rating = get_comment_meta( $comment->comment_ID, '_obenlo_rating', true );
            if ( $rating ) {
                $total_rating += intval( $rating );
                $count++;
            }
        }

        return $count > 0 ? round( $total_rating / $count, 1 ) : 0;
    }

    /**
     * Get total review count for a listing
     */
    public static function get_listing_review_count( $listing_id ) {
        $comments = get_comments( array(
            'post_id' => $listing_id,
            'status'  => 'approve',
            'parent'  => 0,
            'count'   => true
        ) );
        return $comments;
    }

    /**
     * Get average rating for a host
     */
    public static function get_host_average_rating( $host_id ) {
        $args = array(
            'post_type' => 'listing',
            'author'    => $host_id,
            'fields'    => 'ids',
            'posts_per_page' => -1,
            'suppress_filters' => false,
        );
        $listing_ids = get_posts( $args );

        if ( empty( $listing_ids ) ) {
            return 0;
        }

        $total_rating = 0;
        $total_count = 0;

        foreach ( $listing_ids as $listing_id ) {
            $comments = get_comments( array(
                'post_id' => $listing_id,
                'status'  => 'approve',
                'parent'  => 0
            ) );

            foreach ( $comments as $comment ) {
                $rating = get_comment_meta( $comment->comment_ID, '_obenlo_rating', true );
                if ( $rating ) {
                    $total_rating += intval( $rating );
                    $total_count++;
                }
            }
        }

        return $total_count > 0 ? round( $total_rating / $total_count, 1 ) : 0;
    }

    /**
     * Get total reviews for a host
     */
    public static function get_host_review_count( $host_id ) {
        $args = array(
            'post_type' => 'listing',
            'author'    => $host_id,
            'fields'    => 'ids',
            'posts_per_page' => -1,
            'suppress_filters' => false,
        );
        $listing_ids = get_posts( $args );

        if ( empty( $listing_ids ) ) {
            return 0;
        }

        $count = 0;
        foreach ( $listing_ids as $listing_id ) {
            $count += self::get_listing_review_count( $listing_id );
        }
        return $count;
    }

    /**
     * Customize the subject for review notifications
     */
    public function custom_review_notification_subject( $subject, $comment_id ) {
        $comment = get_comment( $comment_id );
        if ( ! $comment ) return $subject;

        $post = get_post( $comment->comment_post_ID );
        if ( $post && $post->post_type === 'listing' ) {
            return sprintf( '[Obenlo] New Review on "%s"', $post->post_title );
        }
        return $subject;
    }

    /**
     * Customize the text/body for review notifications
     * Removes WP branding and wp-admin links.
     */
    public function custom_review_notification_text( $text, $comment_id ) {
        $comment = get_comment( $comment_id );
        if ( ! $comment ) return $text;

        $post = get_post( $comment->comment_post_ID );
        if ( ! $post || $post->post_type !== 'listing' ) {
            return $text;
        }

        $author  = $comment->comment_author;
        $content = $comment->comment_content;
        $rating  = get_comment_meta( $comment_id, '_obenlo_rating', true );
        $stars   = $rating ? str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) : '';
        
        $new_text  = "<p>Hi,</p>";
        $new_text .= "<p>You have received a new review on your listing: <strong>" . esc_html( $post->post_title ) . "</strong></p>";
        $new_text .= "<p><strong>Author:</strong> " . esc_html( $author ) . "<br>";
        
        if ( $rating ) {
            $new_text .= "<strong>Rating:</strong> " . $stars . " (" . $rating . "/5)<br>";
        }
        
        $new_text .= "<strong>Review Content:</strong><br>" . nl2br( esc_html( $content ) ) . "</p>";
        $new_text .= "<p>You can view all reviews for this listing here:<br><a href='" . esc_url( get_permalink( $post->ID ) ) . "#comments' style='color:#e61e4d;'>" . esc_html( get_permalink( $post->ID ) ) . "#comments</a></p>";
        
        // Note: The global wp_mail filter in Obenlo_Booking_Notifications will wrap this in the HTML template.
        return $new_text;
    }
}
