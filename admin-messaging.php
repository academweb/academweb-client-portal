<div class="wrap">
<div <!--style="float: left; width: 200px-->">
    <img src="<?php echo plugins_url( 'images/logo_bj_trans_150.png', __FILE__ ); ?>">
</div>
<p /><p />
<div style="width: 720px">
		<div>
            <b>Select Client:</b>
            <select id="admin_messaging_clients" style="width: 328px">

			<?php
				$args = array(
					'meta_query' => array(
		        		array('key' => '_is_archived','value' => 0,'compare' => 'LIKE')
		     		),'orderby'      => 'display_name','order'        => 'ASC');

				$users = get_users( $args );

				foreach($users as $user){
				    echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
			    }
			?>
			</select>
			
			<p />

            <b>Select Message Thread:</b>
            <select id="admin_messaging_messages"> </select>
			
		</div>

        Admin Messaging to Client: <span id="client_company_name"></span>
        <p /><b>Respond to Message</b>
        <div id="respond_message">
            <textarea id="respond_message_content" style="width: 75%; float: left"></textarea><br />
            <button id="admin_messaging_send_message" style="float: right; margin-top: -10px;" class="btn">Send Message</button>
        </div>

		<!--<div id="admin_message_subject"></div>-->

		<div id="admin_message_content"></div>

</div>
<?php



?>

</div>