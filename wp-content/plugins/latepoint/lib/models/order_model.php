<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsOrderModel extends OsModel {
	public $items;

	public $id,
		$subtotal = 0,
		$total = 0,
		$confirmation_code,
		$status,
		$fulfillment_status,
		$payment_status,
		$source_id,
		$source_url,
		$customer_id,
		$customer_comment,
		$price_breakdown,
		$initial_payment_data = '',
		$initial_payment_data_arr,
		$coupon_code,
		$coupon_discount = 0,
		$tax_total = 0,
		$keys_to_manage = [],
		$created_at,
		$updated_at;

	function __construct( $id = false ) {
		parent::__construct();
		$this->table_name = LATEPOINT_TABLE_ORDERS;

		if ( $id ) {
			$this->load_by_id( $id );
		}
	}

	public function should_not_be_cancelled() {
		return $this->where( [ $this->table_name . '.status !=' => LATEPOINT_ORDER_STATUS_CANCELLED ] );
	}


	public function get_initial_payment_data_value( string $key ): string {
		if ( ! isset( $this->initial_payment_data_arr ) ) {
			$this->initial_payment_data_arr = json_decode( $this->initial_payment_data, true );
		}

		return $this->initial_payment_data_arr[ $key ] ?? '';
	}


	public function get_nice_status_name(): string {
		return OsOrdersHelper::get_nice_order_status_name( $this->status );
	}

	public function get_nice_payment_status_name(): string {
		return OsOrdersHelper::get_nice_order_payment_status_name( $this->payment_status );
	}

	public function get_nice_fulfillment_status_name(): string {
		return OsOrdersHelper::get_nice_order_fulfillment_status_name( $this->fulfillment_status );
	}

	/**
	 * @param array $statuses
	 *
	 * @return OsInvoiceModel|OsInvoiceModel[]
	 */
	public function get_invoices( array $statuses = [] ) {
		$invoices = new OsInvoiceModel();
		if ( ! empty( $statuses ) ) {
			$invoices = $invoices->where_in( 'status', $statuses );
		}

		return $invoices->where( [ 'order_id' => $this->id ] )->get_results_as_models();
	}

	public function manage_by_key_url( string $for = 'customer' ): string {
		return OsOrdersHelper::generate_direct_manage_order_url( $this, $for );
	}

	public function set_initial_payment_data_value( string $key, string $value, bool $save = true ) {
		$this->initial_payment_data_arr         = json_decode( $this->initial_payment_data, true );
		$this->initial_payment_data_arr[ $key ] = $value;
		$this->initial_payment_data             = wp_json_encode( $this->initial_payment_data_arr );
		if ( $save && !$this->is_new_record() ) {
			$this->update_attributes( [ 'initial_payment_data' => $this->initial_payment_data ] );
		}
	}


	public function is_single_booking(): bool {
		$order_items = $this->get_items();

		return ( count( $order_items ) == 1 && $order_items[0]->is_booking() );
	}


	public function filter_allowed_records(): OsModel {
		if ( ! OsRolesHelper::are_all_records_allowed() ) {
			$this->select( LATEPOINT_TABLE_ORDERS . '.*' )->join( LATEPOINT_TABLE_ORDER_ITEMS, [ 'order_id' => LATEPOINT_TABLE_ORDERS . '.id' ] )->join( LATEPOINT_TABLE_BOOKINGS, [ 'order_item_id' => LATEPOINT_TABLE_ORDER_ITEMS . '.id' ] )->group_by( LATEPOINT_TABLE_ORDERS . '.id' );
			if ( ! OsRolesHelper::are_all_records_allowed( 'agent' ) ) {
				$this->filter_where_conditions( [ LATEPOINT_TABLE_BOOKINGS . '.agent_id' => OsRolesHelper::get_allowed_records( 'agent' ) ] );
			}
			if ( ! OsRolesHelper::are_all_records_allowed( 'location' ) ) {
				$this->filter_where_conditions( [ LATEPOINT_TABLE_BOOKINGS . '.location_id' => OsRolesHelper::get_allowed_records( 'location' ) ] );
			}
			if ( ! OsRolesHelper::are_all_records_allowed( 'service' ) ) {
				$this->filter_where_conditions( [ LATEPOINT_TABLE_BOOKINGS . '.service_id' => OsRolesHelper::get_allowed_records( 'service' ) ] );
			}
		}

		return $this;
	}

	/**
	 * @return OsBundleModel[]
	 */
	public function get_bundles_from_order_items(bool $force_db_call = false): array {
		$order_bundles = [];
		foreach ( $this->get_items($force_db_call) as $order_item ) {
			if ( $order_item->is_bundle() ) {
				$order_bundles[ $order_item->id ] = $order_item->build_original_object_from_item_data();
			}
		}

		return $order_bundles;
	}

	/**
	 * @return OsBookingModel[]
	 */
	public function get_bookings_from_order_items(bool $force_db_call = false): array {
		$order_bookings = [];
		foreach ( $this->get_items($force_db_call) as $order_item ) {
			if ( $order_item->is_booking() ) {
				$item_data = json_decode( $order_item->item_data, true );
				if ( ! empty( $item_data['id'] ) ) {
					// existing booking
					$order_bookings[ $order_item->id ] = $order_item->retrieve_original_object();
				} else {
					$order_bookings[ $order_item->get_form_id() ] = $order_item->build_original_object_from_item_data();
				}
			}
		}

		return $order_bookings;
	}


	public function get_total_amount_paid_from_transactions($in_database_format = false) {
		if ( $this->is_new_record() ) {
			return 0;
		}
		$transactions_model = new OsTransactionModel();
		$transactions       = $transactions_model->select( 'amount' )->where( [ 'order_id' => $this->id ] )->get_results();
		$total              = 0;
		foreach ( $transactions as $transaction ) {
			$total += (float) $transaction->amount;
		}

		if($in_database_format){
			$total = OsMoneyHelper::pad_to_db_format( $total );
		}

		return $total;
	}

	public function delete( $id = false ) {
		if ( ! $id && isset( $this->id ) ) {
			$id = $this->id;
		}


		$transactions = new OsTransactionModel();
		$transactions->delete_where( [ 'order_id' => $id ] );

		$order_items = new OsOrderItemModel();
		$order_items = $order_items->where( [ 'order_id' => $id ] )->get_results_as_models();

		if ( ! empty( $order_items ) ) {
			foreach ( $order_items as $order_item ) {
				$bookings           = new OsBookingModel();
				$bookings_to_delete = $bookings->where( [ 'order_item_id' => $order_item->id ] )->get_results_as_models();
				if ( $bookings_to_delete ) {
					foreach ( $bookings_to_delete as $booking_to_delete ) {
						$booking_id_to_delete = $booking_to_delete->id;

						/**
						 * Fires right before a booking is about to be deleted
						 *
						 * @param {integer} $booking_id ID of the booking that will be deleted
						 *
						 * @since 5.0.0
						 * @hook latepoint_booking_will_be_deleted
						 *
						 */
						do_action( 'latepoint_booking_will_be_deleted', $booking_id_to_delete );
						$booking_to_delete->delete();
					}
				}
			}
		}
		$order_items = new OsOrderItemModel();
		$order_items->delete_where( [ 'order_id' => $id ] );

		$order_metas = new OsOrderMetaModel();
		$order_metas->delete_where( [ 'object_id' => $id ] );

		$process_jobs = new OsProcessJobModel();
		$process_jobs->delete_where( [ 'object_id' => $id, 'object_model_type' => 'order' ] );


		return parent::delete( $id );
	}

	public function delete_meta_by_key( $meta_key ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsOrderMetaModel();

		return $meta->delete_by_key( $meta_key, $this->id );
	}

	public function get_meta_by_key( $meta_key, $default = false ) {
		if ( $this->is_new_record() ) {
			return $default;
		}

		$meta = new OsOrderMetaModel();

		return $meta->get_by_key( $meta_key, $this->id, $default );
	}

	public function save_meta_by_key( $meta_key, $meta_value ) {
		if ( $this->is_new_record() ) {
			return false;
		}

		$meta = new OsOrderMetaModel();

		return $meta->save_by_key( $meta_key, $meta_value, $this->id );
	}

	public function determine_payment_status() {
		if ( $this->total > 0 ) {
			$total_paid = $this->get_total_amount_paid_from_transactions();
			if ( empty( $total_paid ) ) {
				$this->update_attributes( [ 'payment_status' => LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID ] );
			} else if ( $total_paid < $this->total ) {
				$this->update_attributes( [ 'payment_status' => LATEPOINT_ORDER_PAYMENT_STATUS_PARTIALLY_PAID ] );
			} else {
				$this->update_attributes( [ 'payment_status' => LATEPOINT_ORDER_PAYMENT_STATUS_FULLY_PAID ] );
			}
		}
	}

	public function get_default_order_status(): string {
		return OsOrdersHelper::get_default_order_status();
	}


	public function get_default_payment_status(): string {
		return LATEPOINT_ORDER_PAYMENT_STATUS_NOT_PAID;
	}

	public function get_default_payment_time(): string {
		return LATEPOINT_PAYMENT_TIME_LATER;
	}

	public function get_default_fulfillment_status(): string {
		return LATEPOINT_ORDER_FULFILLMENT_STATUS_NOT_FULFILLED;
	}

	protected function before_save() {
		// TODO check for uniqueness
		if ( empty( $this->confirmation_code ) ) {
			$this->confirmation_code = strtoupper( OsUtilHelper::random_text( 'distinct', 7 ) );
		}
		if ( empty( $this->payment_status ) ) {
			$this->payment_status = $this->get_default_payment_status();
		}
		if ( empty( $this->payment_time ) ) {
			$this->payment_time = $this->get_default_payment_time();
		}
		if ( empty( $this->fulfillment_status ) ) {
			$this->fulfillment_status = $this->get_default_fulfillment_status();
		}
		if ( empty( $this->source_url ) ) {
			$this->source_url = OsUtilHelper::get_referrer();
		}
		if ( empty( $this->status ) ) {
			$this->status = $this->get_default_order_status();
		}
		if ( empty( $this->ip_address ) ) {
			$this->ip_address = OsUtilHelper::get_user_ip();
		}
	}


	public function get_total_balance_due( bool $recalculate_total = false ) {
		$total    = $this->get_total( $recalculate_total );
		$payments = $this->get_total_amount_paid_from_transactions();

		return $total - $payments;
	}

	public function get_total(
		bool $recalculate = false, array $options = [
		'apply_taxes'   => true,
		'apply_coupons' => true
	]
	) {
		if ( $recalculate ) {
			$this->total = $this->recalculate_total();
		}

		return $this->total;
	}

	public function get_subtotal( bool $recalculate = false ) {
		if ( $recalculate ) {
			$this->subtotal = $this->recalculate_subtotal();
		}

		return $this->subtotal;
	}

	public function get_deposit_amount_to_charge( array $options = [] ) {
		$cart = $this->view_as_cart();

		return $cart->deposit_amount_to_charge( $options );
	}

	public function recalculate_total() {
		$cart = $this->view_as_cart();
		$cart->calculate_prices();

		return $cart->get_total();
	}

	public function recalculate_subtotal() {
		$cart = $this->view_as_cart();
		$cart->calculate_prices();

		return $cart->get_subtotal();
	}

	public function view_as_cart(): OsCartModel {
		$cart              = new OsCartModel();
		$cart->coupon_code = $this->coupon_code ?? '';
		if ( ! empty( $this->id ) ) {
			$cart->order_id = $this->id;
		}
		$cart->order_forced_customer_id = empty( $this->customer_id ) ? 'new' : $this->customer_id;
		$items                          = $this->get_items();
		foreach ( $items as $item ) {
			$cart->add_item( $item->view_as_cart_item(), false );
		}

		return $cart;
	}

	public function generate_first_level_data_vars(): array {
		$vars = [
			'id'                 => $this->id,
			'confirmation_code'  => $this->confirmation_code,
			'customer_comment'   => $this->customer_comment,
			'status'             => $this->status,
			'fulfillment_status' => $this->fulfillment_status,
			'payment_status'     => $this->payment_status,
			'source_id'          => $this->source_id,
			'source_url'         => $this->source_url,
			'total'              => OsMoneyHelper::format_price( $this->get_total() ),
			'subtotal'           => OsMoneyHelper::format_price( $this->get_subtotal() ),
			'created_datetime'   => $this->format_created_datetime_rfc3339(),
		];

		return $vars;
	}


	public function properties_to_query(): array {
		return [
			'status'             => __( 'Order Status', 'latepoint' ),
			'fulfillment_status' => __( 'Fulfillment Status', 'latepoint' ),
			'payment_status'     => __( 'Payment Status', 'latepoint' ),
		];
	}


	public function generate_data_vars(): array {

		$vars                 = $this->get_first_level_data_vars();
		$vars['customer']     = $this->customer->get_data_vars();
		$vars['transactions'] = [];

		$transactions = $this->get_transactions();
		if ( $transactions ) {
			foreach ( $transactions as $transaction ) {
				$vars['transactions'][] = $transaction->get_data_vars();
			}
		}
		$order_items = $this->get_items();
		if ( $order_items ) {
			foreach ( $order_items as $order_item ) {
				$vars['order_items'][] = $order_item->get_data_vars();
			}
		}

		return $vars;
	}


	public function get_key_to_manage_for(string $for): string {
		if($this->is_new_record()) return '';
		if(!empty($this->keys_to_manage[$for])) return $this->keys_to_manage[$for];
		$key = OsMetaHelper::get_order_meta_by_key( 'key_to_manage_for_' . $for, $this->id );
		if ( empty( $key ) ) {
			$key = OsUtilHelper::generate_key_to_manage();
			OsMetaHelper::save_order_meta_by_key( 'key_to_manage_for_' . $for, $key, $this->id );
		}
		$this->keys_to_manage[$for] = $key;
		return $key;
	}


	protected function params_to_sanitize() {
		return [
			'subtotal'        => 'money',
			'total'           => 'money',
			'coupon_discount' => 'money',
			'tax_total'       => 'money',
		];
	}


	public function get_transactions(): array {
		$transactions_model = new OsTransactionModel();
		$transactions       = $transactions_model->where( [ 'order_id' => $this->id ] )->get_results_as_models();

		return $transactions;
	}


	/**
	 * @param bool $force_recalculate
	 * @param array $rows_to_hide
	 *
	 * @return array[]
	 */
	public function generate_price_breakdown_rows( array $rows_to_hide = [], bool $force_recalculate = false ): array {
		$rows = [
			'before_subtotal' => [],
			'subtotal'        => [],
			'after_subtotal'  => [],
			'total'           => [],
			'payments'        => [],
			'balance'         => []
		];

		$existing_rows = [];

		// try to get existing price breakdown from order record
		if ( ! $force_recalculate && ! $this->is_new_record() ) {
			$existing_price_breakdown = empty( $this->price_breakdown ) ? '' : $this->price_breakdown;
			$existing_rows            = json_decode( $existing_price_breakdown, true );
		}

		if ( ! empty( $existing_rows ) ) {
			// merge existing rows with balance and payments
			$rows = array_merge( $rows, $existing_rows );
		} else {
			// recalculate because there is nothing existing or because it's forced
			$cart              = $this->view_as_cart();
			$recalculated_rows = $cart->generate_price_breakdown_rows( $rows_to_hide );
			$rows              = array_merge( $rows, $recalculated_rows );
		}


		// payments and balance have to always be recalculated, even if requested for existing booking
		if ( ! in_array( 'payments', $rows_to_hide ) ) {
			$total_payments_amount = $this->get_total_amount_paid_from_transactions();
			$rows['payments']      = [
				[
					'label'     => __( 'Payments and Credits', 'latepoint' ),
					'raw_value' => OsMoneyHelper::pad_to_db_format( $total_payments_amount ),
					'value'     => ( ( $total_payments_amount > 0 ) ? '-' : '' ) . OsMoneyHelper::format_price( $total_payments_amount, true, false ),
					'type'      => ( $total_payments_amount > 0 ) ? 'credit' : ''
				]
			];
		}
		if ( ! in_array( 'balance', $rows_to_hide ) ) {
			$balance_due_amount = $this->get_total_balance_due( $this->is_new_record() );
			$rows['balance']    = [
				'label'     => __( 'Balance Due', 'latepoint' ),
				'raw_value' => OsMoneyHelper::pad_to_db_format( $balance_due_amount ),
				'value'     => OsMoneyHelper::format_price( $balance_due_amount, true, false ),
				'style'     => 'total'
			];
		}

		/**
		 * Filters rows for price breakdown of the order
		 *
		 * @param {array} $rows Price breakdown rows to be filtered
		 * @param {OsOrderModel} $order Order model for which price breakdown rows are requested
		 * @param {array} $rows_to_hide Rows to hide on the breakdown
		 * @param {bool} $force_recalculate tells whether price rows should be recalculated
		 *
		 * @returns {array} Filtered array of price breakdown rows
		 * @since 5.0.0
		 * @hook latepoint_order_price_breakdown_rows
		 *
		 */
		return apply_filters( 'latepoint_order_price_breakdown_rows', $rows, $this, $rows_to_hide, $force_recalculate );
	}


	public function get_customer() : OsCustomerModel {
		if ( $this->customer_id ) {
			if ( ! isset( $this->customer ) || ( isset( $this->customer ) && ( $this->customer->id != $this->customer_id ) ) ) {
				$this->customer = new OsCustomerModel( $this->customer_id );
			}
		} else {
			$this->customer = new OsCustomerModel();
		}

		return $this->customer;
	}

	/**
	 * @return OsOrderItemModel[]
	 */
	public function get_items( bool $pull_from_db = false ): array {
		// only call DB when needed
		if ( ( ! isset( $this->items ) || ( $pull_from_db ) && ! empty( $this->id ) ) ) {
			$this->items = OsOrdersHelper::get_items_for_order_id( $this->id );
		}

		if ( empty( $this->items ) ) {
			$this->items = [];
		}

		return $this->items;
	}

	public function get_print_link( $key = false ) {
		return ( $key ) ? OsRouterHelper::build_admin_post_link( [
			'manage_order_by_key',
			'print'
		], [ 'key' => $key ] ) : OsRouterHelper::build_admin_post_link( [
			'customer_cabinet',
			'print_order_info'
		], [ 'latepoint_order_id' => $this->id ] );
	}


	protected function allowed_params( $role = 'admin' ) {
		$allowed_params = array(
			'id',
			'subtotal',
			'total',
			'status',
			'fulfillment_status',
			'payment_status',
			'source_id',
			'confirmation_code',
			'source_url',
			'initial_payment_data',
			'customer_id',
			'customer_comment',
			'coupon_code',
			'coupon_discount',
			'tax_total',
			'updated_at',
			'created_at'
		);

		return $allowed_params;
	}


	protected function params_to_save( $role = 'admin' ) {
		$params_to_save = array(
			'id',
			'subtotal',
			'total',
			'status',
			'fulfillment_status',
			'payment_status',
			'source_id',
			'confirmation_code',
			'source_url',
			'initial_payment_data',
			'customer_id',
			'customer_comment',
			'price_breakdown',
			'coupon_code',
			'coupon_discount',
			'tax_total',
			'updated_at',
			'created_at'
		);

		return $params_to_save;
	}


	protected function properties_to_validate() {
		$validations = array(
			'customer_id' => array( 'presence' ),
			'status'      => array( 'presence' ),
		);

		return $validations;
	}
}