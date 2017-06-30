<?php

class Revue_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'revue_widget',
			__( 'Revue inschrijfformulier widget', REVUE_TRANS_DOMAIN ),
			array(
				'description' => __( 'Laat gebruikers inschrijven op jouw Revue lijst', REVUE_TRANS_DOMAIN ),
			)
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		echo revue_subscribe_form();
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );

		if ( ! _revue_key_provided() ) {
			echo '<p>' . __( 'Vul je API key in onder Settings > Revue', 'text_domain' ) . '</p>';
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

function revue_register_widget() {
	register_widget( 'Revue_Widget' );
}

add_action( 'widgets_init', 'revue_register_widget' );