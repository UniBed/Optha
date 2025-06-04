<?php
/**
 * Plugin Name: Optha Vision Assessment
 * Description: Provides a vision assessment form and collects lead data for lens replacement surgery suitability.
 * Version: 1.3.0
 * Author: OpenAI Codex
 * License: GPLv2 or later
 * Update URI: https://github.com/UniBed/Optha
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'OPTHA_PLUGIN_FILE', __FILE__ );
define( 'OPTHA_VERSION', '1.3.0' );

require_once plugin_dir_path( __FILE__ ) . 'inc/github-updater.php';

/**
 * Register custom post type to store leads.
 */
function optha_register_lead_cpt() {
    $labels = array(
        'name'          => 'Vision Leads',
        'singular_name' => 'Vision Lead',
    );

    $args = array(
        'public'       => false,
        'show_ui'      => true,
        'labels'       => $labels,
        'supports'     => array( 'title' ),
    );

    register_post_type( 'optha_lead', $args );
}
add_action( 'init', 'optha_register_lead_cpt' );

function optha_enqueue_styles() {
    wp_register_style( 'optha-assessment', plugins_url( 'style.css', __FILE__ ), array(), OPTHA_VERSION );
    wp_enqueue_style( 'optha-assessment' );
}
add_action( 'wp_enqueue_scripts', 'optha_enqueue_styles' );
/**
 * Returns a random fun eye fact.
 */
function optha_get_fun_fact() {
    $facts = array(
        "Your eyes blink about 12 times every minute!",
        "The human eye can distinguish around 10 million colors.",
        "Eyes are the second most complex organ after the brain.",
        "You see with your brain, not just your eyes."
    );
    return $facts[ array_rand( $facts ) ];
}

/**
 * Estimate eye age. This is purely for fun and not a medical assessment.
 */
function optha_calculate_eye_age( $age, $condition, $wear_glasses ) {
    $eye_age = $age;
    switch ( $condition ) {
        case 'none':
            $eye_age -= 5;
            break;
        case 'near':
        case 'far':
            $eye_age -= 2;
            break;
        case 'cataracts':
        case 'presbyopia':
            $eye_age += 5;
            break;
    }
    if ( ! $wear_glasses ) {
        $eye_age -= 2;
    }
    return max( 18, $eye_age );
}

function optha_calculate_eye_score($line1, $line2, $orientation, $correct_orientation) {
    $score = 0;
    if ( strtoupper(trim($line1)) === "OPTHA" ) {
        $score++;
    }
    if ( strtoupper(trim($line2)) === "VISION" ) {
        $score++;
    }
    if ( $orientation === $correct_orientation ) {
        $score++;
    }
    return $score;
}


/**
 * Shortcode to display the assessment form.
 */
function optha_vision_assessment_shortcode() {
    $message = '';
    if ( isset( $_GET['optha_assessment'] ) && $_GET['optha_assessment'] === 'thanks' ) {
        $message = get_transient( 'optha_assessment_message' );
        delete_transient( 'optha_assessment_message' );
    }
    $orientations = array( 'up' => 0, 'right' => 90, 'down' => 180, 'left' => 270 );
    $correct_orientation = array_rand( $orientations );

    ob_start();

    if ( $message ) {
        echo '<div class="optha-result"><p>' . esc_html( $message ) . '</p>';
        echo '<p class="optha-fun-fact"><em>Fun fact: ' . esc_html( optha_get_fun_fact() ) . '</em></p></div>';
    } else {
    ?>
    <form class="optha-assessment-form et_pb_contact_form clearfix" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <div class="optha-step">
            <p>Hello! Let's check your eyes. What's your name?</p>
            <p><input type="text" name="optha_name" required /></p>
            <p><button class="optha-next">Next</button></p>
        </div>
        <div class="optha-step">
            <p>And your email so we can send your results:</p>
            <p><input type="email" name="optha_email" required /></p>
            <p><button class="optha-next">Next</button></p>
        </div>
        <div class="optha-step">
            <p>How old are you?</p>
            <p><input type="number" name="optha_age" min="1" required /></p>
            <p><button class="optha-next">Next</button></p>
        </div>
        <div class="optha-step">
            <p>Do any of these apply to you?</p>
            <p><select name="optha_condition" id="optha_condition">
                    <option value="none">None</option>
                    <option value="near">Near-sightedness</option>
                    <option value="far">Far-sightedness</option>
                    <option value="cataracts">Cataracts</option>
                    <option value="presbyopia">Presbyopia (age-related loss of near focus)</option>
            </select></p>
            <p><label><input type="checkbox" name="optha_wear_glasses" value="yes" /> I currently wear glasses or contacts</label></p>
            <p><button class="optha-next">Next</button></p>
        </div>
        <div class="optha-step">
            <p>Read the lines below (no zooming in!)</p>
            <div class="optha-vision-line" style="font-size:32px;">OPTHA</div>
            <p><input type="text" name="optha_line1" required /></p>
            <div class="optha-vision-line" style="font-size:24px;">VISION</div>
            <p><input type="text" name="optha_line2" required /></p>
            <p><button class="optha-next">Next</button></p>
        </div>
        <div class="optha-step">
            <p>Which way is the <strong>E</strong> pointing?</p>
            <div class="optha-vision-line"><span class="optha-e" style="transform: rotate(<?php echo (int) $orientations[$correct_orientation]; ?>deg);">E</span></div>
            <p>
                <label><input type="radio" name="optha_orientation" value="up" required /> Up</label>
                <label><input type="radio" name="optha_orientation" value="right" /> Right</label>
                <label><input type="radio" name="optha_orientation" value="down" /> Down</label>
                <label><input type="radio" name="optha_orientation" value="left" /> Left</label>
            </p>
            <input type="hidden" name="optha_correct_orientation" value="<?php echo esc_attr( $correct_orientation ); ?>" />
            <input type="hidden" name="return_url" value="<?php echo esc_url( get_permalink() ); ?>" />
            <input type="hidden" name="action" value="optha_assess" />
            <?php wp_nonce_field( 'optha_assess', 'optha_nonce' ); ?>
            <p><button type="submit">See Results</button></p>
        </div>
    </form>
    <p style="font-size:small;">This assessment is for informational purposes only and does not constitute medical advice.</p>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
        const form = document.querySelector('.optha-assessment-form');
        if(!form) return;
        const steps = form.querySelectorAll('.optha-step');
        let current = 0;
        function showStep(n){
            steps.forEach((s,i)=>{s.style.display = i===n?'block':'none';});
        }
        form.addEventListener('click',function(e){
            if(e.target.classList.contains('optha-next')){
                e.preventDefault();
                if(current < steps.length-1){
                    current++;
                    showStep(current);
                }
            }
        });
        showStep(0);
    });
    </script>
    <?php }
    return ob_get_clean();
}
add_shortcode( 'vision_assessment', 'optha_vision_assessment_shortcode' );

/**
 * Handle form submission.
 */
function optha_handle_assessment() {
    if ( ! isset( $_POST['optha_nonce'] ) || ! wp_verify_nonce( $_POST['optha_nonce'], 'optha_assess' ) ) {
        wp_die( 'Nonce verification failed' );
    }

    $name      = sanitize_text_field( $_POST['optha_name'] );
    $email     = sanitize_email( $_POST['optha_email'] );
    $age       = intval( $_POST['optha_age'] );
    $condition = sanitize_text_field( $_POST['optha_condition'] );
    $glasses   = isset( $_POST['optha_wear_glasses'] ) ? 'Yes' : 'No';
    $line1   = sanitize_text_field( $_POST['optha_line1'] );
    $line2   = sanitize_text_field( $_POST['optha_line2'] );
    $orientation = sanitize_text_field( $_POST['optha_orientation'] );
    $correct_orientation = sanitize_text_field( $_POST['optha_correct_orientation'] );

    $candidate = false;
    $score = optha_calculate_eye_score( $line1, $line2, $orientation, $correct_orientation );
    if ( $age >= 45 && in_array( $condition, array( 'cataracts', 'presbyopia' ), true ) && $score >= 2 ) {
        $candidate = true;
    }

    $assessment = $candidate ?
        'You may be a good candidate for lens replacement surgery. We will contact you soon.' :
        'Based on your answers you may not be a typical candidate. We will review your information.';

    $eye_age = optha_calculate_eye_age( $age, $condition, $glasses === "Yes" );
    $post_id = wp_insert_post( array(
        'post_type'  => 'optha_lead',
        'post_title' => $name,
        'post_status' => 'publish'
    ) );

    if ( $post_id ) {
        update_post_meta( $post_id, 'email', $email );
        update_post_meta( $post_id, 'age', $age );
        update_post_meta( $post_id, 'condition', $condition );
        update_post_meta( $post_id, 'wear_glasses', $glasses );
        update_post_meta( $post_id, 'assessment', $assessment );
        update_post_meta( $post_id, 'line1', $line1 );
        update_post_meta( $post_id, 'line2', $line2 );
        update_post_meta( $post_id, 'orientation', $orientation );
        update_post_meta( $post_id, 'score', $score );
        update_post_meta( $post_id, 'eye_age', $eye_age );
    }

    $admin_email = get_option( 'admin_email' );
    $message     = "Name: $name\nEmail: $email\nAge: $age\nCondition: $condition\nWears glasses: $glasses\nAssessment: $assessment
Score: $score
Eye Age: $eye_age";
    $response_message = $assessment . " Your estimated eye age is " . $eye_age . ". This is not a substitute for a professional eye exam.";
    wp_mail( $admin_email, 'New Vision Assessment Lead', $message );

    set_transient( 'optha_assessment_message', $response_message, 30 );

    $redirect = isset( $_POST['return_url'] ) ? esc_url_raw( $_POST['return_url'] ) : home_url();
    wp_redirect( add_query_arg( 'optha_assessment', 'thanks', $redirect ) );
    exit;
}
add_action( 'admin_post_nopriv_optha_assess', 'optha_handle_assessment' );
add_action( 'admin_post_optha_assess', 'optha_handle_assessment' );

/**
 * Flush rewrite rules on activation/deactivation.
 */
function optha_activate() {
    optha_register_lead_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'optha_activate' );

function optha_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'optha_deactivate' );

?>
