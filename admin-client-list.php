<?php


if( isset( $_POST['create_new_client'] ) ) {

	$userdata = array(
		'user_login' => $_POST['new_client_name'],
		'user_pass'  => $_POST['new_client_password'],
		'user_email' => $_POST['new_client_email'],
		'first_name' => $_POST['new_client_name'],
		'nickname'   => $_POST['new_client_name'],
	);

	$user_id = wp_insert_user( $userdata );
	$is_archived = 0;
	add_user_meta( $user_id, '_is_archived', $is_archived, true );
    $client_emails = "";
	add_user_meta( $user_id, '_client_emails', $client_emails, true );
}

if( isset( $_GET['userdel'] ) && !empty( $_GET['userdel'] ) ) {
	$user_id = $_GET['userdel'];
	update_user_meta($user_id, '_is_archived', 1);
}

if( isset( $_GET['userreactive'] ) && !empty( $_GET['userreactive'] ) ) {
	$user_id = $_GET['userreactive'];
	update_user_meta($user_id, '_is_archived', 0);
}

?>

<div class="wrap">
<div id="client_list" style="float: left; width: 50%;">
	<div id="active_client_list"> 
		<h1>Active Client List</h1>
		<?php
			
			$args = array(
				'meta_query' => array(
            		array(
               			'key' => '_is_archived',
               			'value' => 0,
               			'compare' => 'LIKE'
            		)
         		),
				'orderby'      => 'display_name',
				'order'        => 'ASC'
			);

			$users = get_users( $args );
			
			echo '<ul style="list-style: disc; margin-left: 15px;">';
			foreach($users as $user){
			    $user_name = $user->display_name;
			    $user_id = $user->ID;
			    echo '<li>' . $user_name 
			    . ' <a href="http://client-portal.thewdev.tech/wp-admin/admin.php?page=academ-client-portal%2Fadmin-client-list.php&userdel='. $user_id . '">X</a>'
			    . '</li>';
		    }
		    echo "</ul>";
		?>
	</div>

	<div id="archived_client_list"> 
		<h1>Archived Client List</h1>

		<?php
			
			$args = array(
				'meta_query' => array(
            		array(
               			'key' => '_is_archived',
               			'value' => 1,
               			'compare' => 'LIKE'
            		)
         		),
				'orderby'      => 'display_name',
				'order'        => 'ASC'
			);

			$users = get_users( $args );

			echo '<ul style="list-style: disc; margin-left: 15px;">';
			foreach($users as $user){
			    $user_name = $user->display_name;
			    $user_id = $user->ID;
			    echo '<li>' . $user_name 
			    . ' <a href="http://client-portal.thewdev.tech/wp-admin/admin.php?page=academ-client-portal%2Fadmin-client-list.php&userreactive='. $user_id . '">[reactive?]</a>'
			    . '</li>';
		    }
		    echo "</ul>";
		?>

	</div>
</div>

<div id="create_new_client" style="float: right; width: 50%;">
	<div id="create_a_new_client"> 
		<h1>Create a New Client</h1>
		<form method="post" action="">
			<input type="text" name="new_client_name" id="new_client_name" placeholder="New Client Name" required /> <p />
			<input type="text" name="new_client_email" id="new_client_email" placeholder="New Client Email" required /> <p />
			<input type="text" name="new_client_password" id="new_client_password" placeholder="New Client Password" required /> <p />
			<input type="submit" name="create_new_client" id="create_new_client" value="Create New Client" class="btn" />
		</form>
	</div>
</div>



<?php

	

?>

</div>