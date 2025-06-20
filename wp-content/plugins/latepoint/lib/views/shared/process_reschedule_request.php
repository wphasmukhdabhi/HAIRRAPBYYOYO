<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

/**
 * @var $booking OsBookingModel
 * @var $viewer string
 */
?>
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="reschedule-confirmation-wrapper">
	<div class="icon-w a-rotate-scale">
		<i class="latepoint-icon latepoint-icon-check"></i>
	</div>
	<h2 class="a-up-20 a-delay-1"><?php esc_html_e('Confirmation', 'latepoint'); ?></h2>
	<div class="desc a-up-20 a-delay-2"><?php esc_html_e('Your appointment has been rescheduled.', 'latepoint'); ?></div>
	<div class="rescheduled-date-time-info a-up-20 a-delay-3">
		<div class="info-label"><?php esc_html_e('New Appointment Time', 'latepoint'); ?></div>
		<div class="info-value">
			<?php
			if ($booking->start_date) {
				echo $booking->get_nice_datetime_for_summary($viewer);
			} ?>
		</div>
	</div>
</div>