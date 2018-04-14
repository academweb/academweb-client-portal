<div class="wrap">

<?php

global $wpdb;

if ( isset( $_POST['fileup_nonce'] ) && wp_verify_nonce( $_POST['fileup_nonce'], 'pdf_file_upload' ) ) {
	if ( !function_exists( 'wp_handle_upload' ) ) 
		require_once( ABSPATH . 'wp-admin/includes/file.php' );

	$file = &$_FILES['pdf_file_upload'];
	$overrides = array( 'test_form' => false );

	$movefile = wp_handle_upload( $file, $overrides );

	if ( $movefile && empty($movefile['error']) ) {
		echo "File uploaded successfully.\n";
		$file_path = $movefile['file'];
		$file_link = $movefile['url'];
		
	} else {
		echo "Error loading file: " . $movefile['error'];
		return;
	}

	
	$project_id = $_POST['admin_main_project_list'];
	$pdf_file_name = $_POST['pdf_file_name'];
	
	
	$sql = "select project_id, project_name, project_links from wp_client_portal_projects where project_id = $project_id";
	$results = $wpdb->get_results( $sql );
	
	$i = 0;	
	
	$plinks = $results[0]->project_links;

	if (!empty($plinks)) { 
		$plinks = unserialize($plinks);
		/*echo '<pre>Decode from DB: ';
		print_r($plinks);
		echo '</pre>';*/
		
		foreach ($plinks as $plink) {
			$pFiles[$i] = new projectFiles;
			$pFiles[$i]->project_id = $project_id;
			$pFiles[$i]->file_link = $plink->file_link;
			$pFiles[$i]->file_path = $plink->file_path;
			$pFiles[$i]->upload_date = $plink->upload_date;
			$pFiles[$i]->pdf_file_name = $plink->pdf_file_name;
			$i++;		
		}
	}

	$upload_date = new DateTime();
	$upload_date = $upload_date->format('Y-m-d h:i:s');
	
	$pFiles[$i] = new projectFiles;
	$pFiles[$i]->project_id = $project_id;
	$pFiles[$i]->file_link = $file_link;
	$pFiles[$i]->file_path = $file_path;
	$pFiles[$i]->upload_date = $upload_date;
	$pFiles[$i]->pdf_file_name = $pdf_file_name;

	$pFiles = serialize($pFiles);
	
	$sql = "UPDATE wp_client_portal_projects set project_links = '$pFiles', last_update = '$upload_date' WHERE project_id = $project_id";
	//echo 'sql = ' . $sql;
	$update_links = $wpdb->get_results( $sql );

    $user_id = get_current_user_id();

    /*    $client_emails =  get_user_meta( $cuser_id, "_client_emails", true );


    if (!empty($client_emails)) {
        $emails = unserialize($client_emails);

        for ($i = 0; $i < count($emails); $i++) {
            echo "&#8226; $emails[$i] <a href='?delete_email=$emails[$i]&i=$i'> X </a><br />";
        }

    }*/

    $sql = "INSERT INTO wp_client_portal_messages (parent_id, message_subject, message_content, user_id, sending_date)
					  VALUES (0, \"$new_project_name - New project was created\", \"$new_project_name - New project was created\", $user_id, \"$upload_date\")";

    $insert_message = $wpdb->get_results( $sql );

	// echo '<pre>JSON: ';
	// print_r($pFiles);
	// echo '</pre>';

	


}

?>


<div style="width: 1000px">
	<div style="text-align: center">
		<img src="<?php echo plugins_url( 'images/logo_bj_trans_150.png', __FILE__ ); ?>">
	</div>
	<div>
		<select id="admin_main_clients" style=" width: 800px;">
			<option value="0">Choose client</option>
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

				foreach ($users as $user) {
					$user_name = $user->display_name;
					echo '<option value="' . $user->ID . '">' . $user_name . '</option>';
				}
			?>
		</select>
		<a href="admin.php?page=academ-client-portal%2Fadmin-client-list.php">
			<input type="button" value="Add New Company" class="btn" />
		</a>
	</div>
</div>


<div id="admin_main" style="width: 1000px;display: none">
	<div style="float:left; width: 500px;">
		<div id="admin_main_company_name"> </div>
		
		<h3>Create a New Project</h3>
		<input type="text" name="new_project_name" id="new_project_name"  placeholder="New Project Name" style="width: 100%"/>
		<p />
		<textarea name="new_project_description" id="new_project_description" placeholder="Project Description" style="width: 100%"></textarea>
		<p />
		<input type="button" id="new_project_create" name="new_project_create" value="Send Message" class="btn" style="width: 100%"/>

		<div id="admin_main_client_projects" style="overflow-y: scroll; width: 500px; height: 400px" >
			
		</div>
	</div>


	<div style="float:right; width: 480px;" id="admin_upload_files">
		<form id="upload_pdf_form" enctype="multipart/form-data" action="" method="POST">
			<h3>Upload a PDF</h3>
			<br />
			
			<select name="admin_main_project_list" id="admin_main_project_list" style="width: 100%;"></select>
			
			<p />

			<input type="text" name="pdf_file_name" id="pdf_file_name" placeholder="PDF Name" style="width: 100%" />
			
			<div style="text-align: center; height: 60px; vertical-align: middle; cursor: pointer; ">
				Click to Upload File
				<?php wp_nonce_field( 'pdf_file_upload', 'fileup_nonce' ); ?>
				<input name="pdf_file_upload" id="pdf_file_upload" type="file" style="width: 100%" />
				<br />
				<input type="submit" value="Upload PDF" class="btn" style="width: 100%;" />
			</div>
		</form>
	</div>

    <div style="float: right; width: 480px; margin-top: 50px;">
        <h2>Messages From Client:</h2>
        <div id="admin_main_message_from_client" style="overflow-y: scroll; width: 480px; height: 150px; color: #00529B">

        </div>

        <div id="admin_main_send_new_message_to_client">
        <h2>Send New Message to Client</h2>

            <input type="text" name="admin_main_new_message_subject" id="admin_main_new_message_subject"  placeholder="Subject" style="width: 100%"/>
            <p />
            <textarea name="admin_main_new_message_content" id="admin_main_new_message_content" placeholder="Message Content" style="width: 100%"></textarea>
            <p />
            <input type="button" id="admin_main_send_message" name="admin_main_send_message" value="Send Message" class="btn" style="width: 100%"/>
        </div>
    </div>

</div>

<?php 
	


?>

</div>