<?php

class OsShortcodesHelper {

	// [latepoint_calendar]
	public static function shortcode_latepoint_calendar( array $atts = [] ): string {
		$atts   = shortcode_atts( [
			'date'           => 'now',
			'show_services'  => false,
			'show_agents'    => false,
			'show_locations' => false,
			'view'           => 'month'
		], $atts );
		$output = '';
		try {
			$target_date = new OsWpDateTime( $atts['date'] );
		} catch ( Exception $e ) {
			$target_date = new OsWpDateTime( 'now' );
		}

		$restrictions = [];
		if ( $atts['show_services'] ) {
			$restrictions['show_services'] = $atts['show_services'];
		}
		if ( $atts['show_agents'] ) {
			$restrictions['show_agents'] = $atts['show_agents'];
		}
		if ( $atts['show_locations'] ) {
			$restrictions['show_locations'] = $atts['show_locations'];
		}
		$output .= OsEventsHelper::events_grid( $target_date, [], $atts['view'], $restrictions );

		return $output;
	}

	// [latepoint_resources]
	public static function shortcode_latepoint_resources( $atts ) {
		$atts = shortcode_atts( array(
			'id'                        => false,
			'button_caption'            => esc_html__( 'Book Now', 'latepoint' ),
			'items'                     => 'services', // services, agents, locations, bundles
			'item_ids'                  => '',
			'group_ids'                 => '',
			'columns'                   => 4,
			'limit'                     => false,
			'button_border_radius'      => false,
			'button_bg_color'           => false,
			'button_text_color'         => false,
			'button_font_size'          => false,
			'show_locations'            => false,
			'show_agents'               => false,
			'show_services'             => false,
			'show_service_categories'   => false,
			'selected_location'         => false,
			'selected_bundle'           => false,
			'selected_agent'            => false,
			'selected_service'          => false,
			'selected_duration'         => false,
			'selected_total_attendees'  => false,
			'selected_service_category' => false,
			'calendar_start_date'       => false,
			'selected_start_date'       => false,
			'selected_start_time'       => false,
			'hide_side_panel'           => false,
			'hide_summary'              => false,
			'hide_image'                => false,
			'hide_price'                => false,
			'hide_description'          => false,
			'source_id'                 => false,
			'classname'                 => false,
			'btn_classes'               => false,
			'btn_wrapper_classes'       => false
		), $atts );

        if ($atts['items'] == 'bundles' && $atts['selected_service']) {
	        $atts['selected_service'] = false;
        }
		if ($atts['items'] == 'services' && $atts['selected_bundle']) {
			$atts['selected_bundle'] = false;
		}

		// Data attributes setup
		$data_atts = '';
		if ( ( $atts['items'] != 'locations' ) && $atts['show_locations'] ) {
			$data_atts .= 'data-show-locations="' . esc_attr($atts['show_locations']) . '" ';
		}
		if ( ( $atts['items'] != 'agents' ) && $atts['show_agents'] ) {
			$data_atts .= 'data-show-agents="' . esc_attr($atts['show_agents']) . '" ';
		}
		if ( ( $atts['items'] != 'services' ) && $atts['show_services'] ) {
			$data_atts .= 'data-show-services="' . esc_attr($atts['show_services']) . '" ';
		}
		if ( ( $atts['items'] != 'services' ) && $atts['show_service_categories'] ) {
			$data_atts .= 'data-show-service-categories="' . esc_attr($atts['show_service_categories']) . '" ';
		}
		if ( ( $atts['items'] != 'locations' ) && $atts['selected_location'] ) {
			$data_atts .= 'data-selected-location="' . esc_attr($atts['selected_location']) . '" ';
		}
		if ( ( $atts['items'] != 'agents' ) && $atts['selected_agent'] ) {
			$data_atts .= 'data-selected-agent="' . esc_attr($atts['selected_agent']) . '" ';
		}
		if ( ( $atts['items'] != 'bundles' ) && $atts['selected_bundle'] ) {
			$data_atts .= 'data-selected-bundle="' . esc_attr($atts['selected_bundle']) . '" ';
		}
		if ( ( $atts['items'] != 'services' ) && $atts['selected_service'] ) {
			$data_atts .= 'data-selected-service="' . esc_attr($atts['selected_service']) . '" ';
		}
		if ( $atts['selected_duration'] ) {
			$data_atts .= 'data-selected-duration="' . esc_attr($atts['selected_duration']) . '" ';
		}
		if ( $atts['selected_total_attendees'] ) {
			$data_atts .= 'data-selected-total-attendees="' . esc_attr($atts['selected_total_attendees']) . '" ';
		}
		if ( ( $atts['items'] != 'services' ) && $atts['selected_service_category'] ) {
			$data_atts .= 'data-selected-service-category="' . esc_attr($atts['selected_service_category']) . '" ';
		}
		if ( $atts['calendar_start_date'] ) {
			$data_atts .= 'data-calendar-start-date="' . esc_attr($atts['calendar_start_date']) . '" ';
		}
		if ( $atts['selected_start_date'] ) {
			$data_atts .= 'data-selected-start-date="' . esc_attr($atts['selected_start_date']) . '" ';
		}
		if ( $atts['selected_start_time'] ) {
			$data_atts .= 'data-selected-start-time="' . esc_attr($atts['selected_start_time']) . '" ';
		}
		if ( $atts['hide_side_panel'] == 'yes' ) {
			$data_atts .= 'data-hide-side-panel="yes" ';
		}
		if ( $atts['hide_summary'] == 'yes' ) {
			$data_atts .= 'data-hide-summary="yes" ';
		}
		if ( $atts['source_id'] ) {
			$data_atts .= 'data-source-id="' . esc_attr($atts['source_id']) . '" ';
		}

		$block_classes = $atts['classname'] ? " " . $atts['classname'] : "";
		$resource_item_classes = $atts['id'] ? ' resource-item-' . $atts['id'] : '';

		$btn_wrapper_classes = $atts['btn_wrapper_classes'] ?: " wp-block-button";
		$btn_classes = $atts['btn_classes'] ?: " wp-block-button__link";

		$output = '<div class="latepoint-resources-items-w resources-columns-' . esc_attr($atts['columns']) . esc_attr($block_classes) . '">';

		if ( $atts['item_ids'] ) {
			$ids            = OsUtilHelper::explode_and_trim( $atts['item_ids'] );
			$clean_item_ids = OsUtilHelper::clean_numeric_ids( $ids );
		} else {
			$clean_item_ids = [];
		}
		if ( $atts['group_ids'] ) {
			$ids             = OsUtilHelper::explode_and_trim( $atts['group_ids'] );
			$clean_group_ids = OsUtilHelper::clean_numeric_ids( $ids );
		} else {
			$clean_group_ids = [];
		}
		switch ( $atts['items'] ) {
			case 'services':
				$services = new OsServiceModel();
				if ( $atts['limit'] && is_numeric( $atts['limit'] ) ) {
					$services->set_limit( $atts['limit'] );
				}
				if ( $clean_item_ids ) {
					$services->where( [ 'id' => $clean_item_ids ] );
				}
				if ( $clean_group_ids ) {
					$services->where( [ 'category_id' => $clean_group_ids ] );
				}
				$services = $services->should_be_active()->order_by( 'order_number asc' )->get_results_as_models();
				foreach ( $services as $service ) {
					$output .= '<div class="resource-item '. $resource_item_classes .'">';
					if ($atts['hide_image'] !== 'yes' && !empty( $service->description_image_id )) {
						$output .= '<div class="ri-media" style="background-image: url(' . $service->get_description_image_url() . ')"></div>';
					}
					$output .= '<div class="ri-name"><h3>' . $service->name . '</h3></div>';

					if ($atts['hide_price'] !== 'yes' && $service->price_min > 0) {
						$service_price_formatted = ( $service->price_min != $service->price_max ) ? __( 'Starts at', 'latepoint' ) . ' ' . $service->price_min_formatted : $service->price_min_formatted;
						$output .= '<div class="ri-price">' . $service_price_formatted . '</div>';
					}
					if ($atts['hide_description'] !== 'yes' && ! empty( $service->short_description)) {
						$output .=  '<div class="ri-description">' . wp_kses_post($service->short_description) . '</div>';
					}
					$output .= '<div class="ri-buttons ' . esc_attr($btn_wrapper_classes) . '">
						<a href="#" ' . $data_atts . ' class="latepoint-book-button os_trigger_booking ' . esc_attr($btn_classes) . '" data-selected-service="' . esc_attr($service->id) . '">' . wp_kses_post($atts['button_caption']) . '</a>
					</div>';
					$output .= '</div>';
				}
				break;
			case 'agents':
				$agents = new OsAgentModel();
				if ( $atts['limit'] && is_numeric( $atts['limit'] ) ) {
					$agents->set_limit( $atts['limit'] );
				}
				if ( $atts['item_ids'] ) {
					$ids = OsUtilHelper::explode_and_trim( $atts['item_ids'] );
					$ids = OsUtilHelper::clean_numeric_ids( $ids );
					if ( $ids ) {
						$agents->where( [ 'id' => $ids ] );
					}
				}
				if ( $clean_item_ids ) {
					$agents->where( [ 'id' => $clean_item_ids ] );
				}
				$agents = $agents->should_be_active()->get_results_as_models();
				foreach ( $agents as $agent ) {
					$output .= '<div class="resource-item '. esc_attr($resource_item_classes) .' ri-centered">';
					$output .= ! empty( $agent->avatar_image_id ) ? '<div class="ri-avatar" style="background-image: url(' . $agent->get_avatar_url() . ')"></div>' : '';
					$output .= '<div class="ri-name"><h3>' . $agent->full_name . '</h3></div>';
					$output .= ! empty( $agent->title ) ? '<div class="ri-title">' . $agent->title . '</div>' : '';
					$output .= ! empty( $agent->short_description ) ? '<div class="ri-description">' . wp_kses_post($agent->short_description) . '</div>' : '';
					$output .= '<div class="ri-buttons ' . esc_attr($btn_wrapper_classes) . '">
						<a href="#" ' . $data_atts . ' class="latepoint-book-button os_trigger_booking latepoint-btn-block ' . esc_attr($btn_classes) . '" data-selected-agent="' . esc_attr($agent->id) . '">' . wp_kses_post($atts['button_caption']) . '</a>
					</div>';
					$output .= '</div>';
				}
				break;
			case 'locations':
				$locations = new OsLocationModel();
				if ( $atts['limit'] && is_numeric( $atts['limit'] ) ) {
					$locations->set_limit( $atts['limit'] );
				}
				if ( $clean_item_ids ) {
					$locations->where( [ 'id' => $clean_item_ids ] );
				}
				if ( $clean_group_ids ) {
					$locations->where( [ 'category_id' => $clean_group_ids ] );
				}
				$locations = $locations->should_be_active()->order_by( 'order_number asc' )->get_results_as_models();
				foreach ( $locations as $location ) {
					$output .= '<div class="resource-item '. esc_attr($resource_item_classes) .'">';
					$output .= ! empty( $location->full_address ) ? '<div class="ri-map">' . $location->get_google_maps_iframe( 200 ) . '</div>' : '';
					$output .= '<div class="ri-name"><h3>' . $location->name . '</h3></div>';
					$output .= ! empty( $location->full_address ) ? '<div class="ri-description">' . $location->full_address . '<a href="' . $location->get_google_maps_link() . '" target="_blank" class="ri-external-link"><i class="latepoint-icon latepoint-icon-external-link"></i></a></div>' : '';
					$output .= '<div class="ri-buttons ' . $btn_wrapper_classes . '">
						<a href="#" ' . $data_atts . ' class="latepoint-book-button os_trigger_booking ' . esc_attr($btn_classes) . '" data-selected-location="' . esc_attr($location->id) . '">' . wp_kses_post($atts['button_caption']) . '</a>
					</div>';
					$output .= '</div>';
				}
				break;
			case 'bundles':
				$bundles = new OsBundleModel();

				if ( $clean_item_ids ) {
					$bundles->where( [ 'id' => $clean_item_ids ] );
				}

				if ( $atts['limit'] && is_numeric( $atts['limit'] ) ) {
					$bundles->set_limit( $atts['limit'] );
				}

				$bundles = $bundles->should_be_active()->order_by( 'order_number asc' )->get_results_as_models();
                $bundles = is_array($bundles) ? $bundles : [$bundles];

				ob_start();
				foreach ( $bundles as $bundle ) {
				?>
                    <div class="resource-item <?php echo esc_attr($resource_item_classes); ?>">
                        <div class="ri-name">
                            <h3><?php echo $bundle->name; ?></h3>
                        </div>
                        <?php if ($atts['hide_price'] !== 'yes' && $price = $bundle->get_formatted_charge_amount()) { ?>
                            <div class="ri-price"><?php echo $price; ?></div>
                        <?php } ?>

						<?php if ($atts['hide_description'] !== 'yes' && $description = $bundle->short_description ) { ?>
                            <div class="ri-description"><?php echo $description; ?></div>
						<?php } ?>
                        <div class="ri-buttons <?php echo esc_attr($btn_wrapper_classes) ?>">
                            <a href="#" <?php echo $data_atts ?>
                               class="latepoint-book-button os_trigger_booking latepoint-btn-block <?php echo esc_attr($btn_classes); ?>"
                               data-selected-bundle="<?php echo $bundle->id; ?>" >
								<?php echo $atts['button_caption']; ?>
                            </a>
                        </div>
                    </div>
				<?php }

				$output .= ob_get_clean();
				break;
		}
		$output .= '</div>';

		return $output;
	}

	// [latepoint_book_form]
	public static function shortcode_latepoint_book_form( $atts, $content = "" ) {

		$atts  = shortcode_atts( self::get_default_booking_atts(), $atts );
		$element_classes = ['latepoint-inline-form'];
		$element_classes[] = (empty($atts['hide_side_panel']) || $atts['hide_side_panel'] == 'no') ? 'latepoint-show-side-panel' : 'latepoint-hide-side-panel';
		$output = '<div class="latepoint-book-form-wrapper os-loading os_init_booking_form" id="latepointBookForm_'.esc_attr(uniqid()).'" ' . self::generate_data_atts_string_from_atts($atts) . '>
						<div class="latepoint-w '.esc_attr(implode(' ', $element_classes)).'">
							<div class="latepoint-booking-form-element">
								<div class="latepoint-side-panel"></div>
								<div class="latepoint-form-w"></div>
							</div>
						</div>
					</div>';

		return $output;
	}


	// [latepoint_book_button]
	public static function shortcode_latepoint_book_button( $atts, $content = "" ) {
		$atts = shortcode_atts( array_merge( self::get_default_booking_atts(), [
			'id'                  => false,
			'caption'             => __( 'Book Appointment', 'latepoint' ),
			'is_inherit'          => false,
			'align'               => false,
			'bg_color'            => false,
			'text_color'          => false,
			'font_size'           => false,
			'border'              => false,
			'border_radius'       => false,
			'margin'              => false,
			'padding'             => false,
			'css'                 => false,
			'classname'           => false,
			'btn_classes'         => false,
			'btn_wrapper_classes' => false
		] ), $atts );

		$btn_wrapper_classes = [];
		$btn_wrapper_classes[] = $atts['btn_wrapper_classes'] ?: "wp-block-button";
		if($atts['align']) $btn_wrapper_classes[] = "latepoint-book-button-align-{$atts['align']}";
		if($atts['classname']) $btn_wrapper_classes[] = $atts['classname'];

		$btn_classes   = [];
		$btn_classes[] = $atts['btn_classes'] ?: "wp-block-button__link";
		if($atts['id']) $btn_classes[] = 'latepoint-book-button-' . $atts['id'];

		$data_atts = self::generate_data_atts_string_from_atts($atts);

		$styles = [];
        # if not inherit - show button styles
        if (!$atts['is_inherit']) {
            if ($atts['bg_color']) $styles[] = "background-color: " . esc_attr($atts['bg_color']);
            if ($atts['text_color']) $styles[] = "color: " . esc_attr($atts['text_color']);
            if ($atts['font_size']) $styles[] = "font-size: " . esc_attr($atts['font_size']);
            if ($atts['border']) $styles[] = "border: " . esc_attr($atts['border']);
            if ($atts['border_radius']) $styles[] = "border-radius: " . esc_attr($atts['border_radius']);
            if ($atts['margin']) $styles[] = "margin: " . esc_attr($atts['margin']);
            if ($atts['padding']) $styles[] = "padding: " . esc_attr($atts['padding']);
            if ($atts['css']) $styles[] = $atts['css'];
        }
		$style_attr = !empty($styles) ? ' style="' . esc_attr(implode('; ', $styles)) . '"' : '';

		$before_html = '<div class="latepoint-book-button-wrapper ' . esc_attr(implode(' ', $btn_wrapper_classes)) . '">';
		$after_html = '</div>';

		return $before_html . '<a href="#" class="latepoint-book-button os_trigger_booking ' .
		       esc_attr(implode(' ', $btn_classes)) . '"' . $style_attr . ' ' . $data_atts . '>' .
		       esc_html($atts['caption']) . '</a>' . $after_html;
	}

	// [latepoint_customer_dashboard]
	public static function shortcode_latepoint_customer_dashboard( $atts ) {
		$atts = shortcode_atts( array(
			'caption' => __( 'Book Appointment', 'latepoint' ),
			'hide_new_appointment_ui' => false,
		), $atts );
		$atts['hide_new_appointment_ui'] = $atts['hide_new_appointment_ui'] == 'yes' ?? false;

		$customerCabinetController = new OsCustomerCabinetController();
		$output                    = $customerCabinetController->dashboard($atts);

		return $output;
	}

	// [latepoint_customer_login]
	public static function shortcode_latepoint_customer_login( $atts ) {
		$atts = shortcode_atts( array(
			'caption' => __( 'Book Appointment', 'latepoint' )
		), $atts );

		$customerCabinetController = new OsCustomerCabinetController();
		$output                    = $customerCabinetController->login();

		return $output;
	}

	/**
	 * List of default booking attributes for booking button and form shortcodes
	 *
	 * @return false[]
	 */
	private static function get_default_booking_atts() : array {
		return [
			'show_locations'            => false,
			'show_agents'               => false,
			'show_services'             => false,
			'show_service_categories'   => false,
			'selected_location'         => false,
			'selected_agent'            => false,
			'selected_service'          => false,
			'selected_duration'         => false,
			'selected_total_attendees'  => false,
			'selected_service_category' => false,
			'selected_bundle'           => false,
			'calendar_start_date'       => false,
			'selected_start_date'       => false,
			'selected_start_time'       => false,
			'hide_side_panel'           => false,
			'hide_summary'              => false,
			'source_id'                 => false
		];
	}

	private static function generate_data_atts_string_from_atts( array $atts) : string {
		$data_atts = '';
		$defaults = self::get_default_booking_atts();
		foreach($defaults as $key => $value) {
			if(!empty($atts[$key])) $data_atts.= 'data-'.esc_html(str_replace('_', '-', $key)).'="'.esc_attr($atts[$key]).'" ';
		}
		return $data_atts;
	}

}