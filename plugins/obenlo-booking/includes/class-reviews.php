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
    }

    /**
     * Save the rating from the comment form
     */
    public function save_comment_rating( $comment_id, $comment_approved, $commentdata ) {
        if ( isset( $_POST['rating'] ) && ! empty( $_POST['rating'] ) ) {
            $rating = intval( $_POST['rating'] );
            add_comment_meta( $comment_id, '_obenlo_rating', $rating );
        }
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
}
