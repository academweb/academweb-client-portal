<?php

/*
*	Plugin name: Academ Client Portal
*/

class clientMessage {
    public $message_id = 0;
    public $parent_id = 0;
	public $message_subject = "";
    public $message_content = "";
	public $sending_date = "";
	public $to_admin = 0;
    public $is_read = 0;
}

class projectFiles {
	public $project_id = 0;
	public $file_link = "";
	public $file_path = "";
	public $upload_date = "";
	public $pdf_file_name = "";
	public $is_read = "0";
}

function modify_contact_methods($profile_fields) {
    $profile_fields['client_emails'] = 'Client Emails';
    return $profile_fields;
}
add_filter('user_contactmethods', 'modify_contact_methods');

function onlyregistered_func() {
    if(!is_user_logged_in()) {
        auth_redirect();
    }
}
add_action('get_header', 'onlyregistered_func');


function academ_client_portal_admin_menu() {
	add_menu_page( 'Client Portal', 'B&J Admin', 'manage_options', 'academ-client-portal/admin-main.php', '', 'dashicons-tickets', 6  );
	add_submenu_page('academ-client-portal/admin-main.php', 'Messaging', 'Messaging', 'manage_options', 'academ-client-portal/admin-messaging.php');
	add_submenu_page('academ-client-portal/admin-main.php', 'Client List', 'Client List', 'manage_options', 'academ-client-portal/admin-client-list.php');
	add_options_page('Client Portal', 'B&J Admin', 'manage_options', 'academ-client-portal/admin-main.php');
}
add_action( 'admin_menu', 'academ_client_portal_admin_menu' );


function loadjs(){
    wp_enqueue_script( 'jquery-cookie', plugins_url() . '/academ-client-portal/jquery-cookie.js', array( 'jquery' ), '01042018', true );
    wp_enqueue_script( 'formatDate', plugins_url() . '/academ-client-portal/formatDate.js', array( 'jquery' ), '13042018', true );
}
add_action( 'admin_enqueue_scripts', 'loadjs' );
add_action( 'wp_enqueue_scripts', 'loadjs' );

function academ_client_portal_styles()  
{  
    wp_register_style( 'custom-style', plugins_url( '/style.css', __FILE__ ), array(), '20170923', 'all' );
    wp_enqueue_style( 'custom-style' );
}  
add_action( 'admin_enqueue_scripts', 'academ_client_portal_styles' );


add_action('admin_print_footer_scripts', 'select_client_action_javascript', 99);

function select_client_action_javascript() {
	?>
	<script>
	jQuery(document).ready(function($) {

        $( '#admin_main_clients' ).change(function() {
            console.log("#admin_main_clients");
            var user_id = $( '#admin_main_clients' ).val();

            $.cookie("user_id", user_id);

            if (user_id != 0) {
                $( "#admin_main" ).css('display', 'inline-block');
            } else {
                $( "#admin_main" ).css('display', 'none');
            }

            var data = {
                action: 'admin_main_clients_action',
                dataType: JSON,
                user_id: user_id
            };

            jQuery.post( ajaxurl, data, function(response) {
                if (response) {
                    var responseObj = jQuery.parseJSON(response);

                    $( "#admin_main_project_list" ).empty();
                    $( "#admin_main_client_projects" ).empty();
                    company_name = $( "#admin_main_clients option:selected" ).text();
                    $( "#admin_main_company_name" ).html( "<h2>" + company_name + "</h2>");
                    console.log('responseObj.length = ' + responseObj.length);
                    for (var i = 0; i < responseObj.length; i++) {
                        project_id = responseObj[i].project_id;
                        project_name = responseObj[i].project_name;
                        description = responseObj[i].description;
                        last_update = responseObj[i].last_update;

                        $( "#admin_main_project_list" ).append( "<option value=\"" + project_id + "\">" + project_name + "</option>");
                        $( "#admin_main_client_projects" ).append( "<h2>" + project_name + " <a href=#>[edit]</a>"
                            + "</h2><b>Last Update: </b>"
                            + last_update + "<br /><b> Description:</b> "
                            + description + " <span class=\"project-description-edit\" data-id=\"" + project_id + "\" data-description=\"" + description + "\" style=\"color: blue\; cursor: pointer;\"> [edit] </span> <br />");

                        var plinks = responseObj[i].project_links;

                        if (plinks) {
                            plinks.sort(function(a, b){
                                var dateA=new Date(a.upload_date), dateB=new Date(b.upload_date);
                                return dateA-dateB; //sort by date ascending
                            });
                            plinks.reverse();

                            console.log(plinks);

                            for (var n = 0; n < plinks.length; n++) {
                                plink = plinks[n];
                                var pdf_file_name = plink["pdf_file_name"];


                                if ((pdf_file_name.substr(pdf_file_name.length - 4, pdf_file_name.length)) == ".pdf") {
                                    pdf_file_name = pdf_file_name.substr(0, pdf_file_name.length - 4);
                                }
                                pdf_file_link = plink["file_link"];

                                //var pdf_file_upload_date = plink["upload_date"];

                                var upload_date = new Date(plink["upload_date"]);
                                upload_date = upload_date.format("%m-%d-%y");

                                $( "#admin_main_client_projects" ).append("<a href='" + pdf_file_link + "'> &#8226; " + pdf_file_name + "</a>");

                                $( "#admin_main_client_projects" ).append("<span style='font-weight: bold;'> Uploaded: " + upload_date + "</span>");
                                $( "#admin_main_client_projects" ).append("<span style=\"color: red;  cursor: pointer;\"> Rename</span>");
                                $( "#admin_main_client_projects" ).append("<span style=\"color: #000000; font-weight: bold; cursor: pointer;\"> | </span>");

                                $( "#admin_main_client_projects" ).append("<span class=\"remove_pdf_from_project\" data-project_id=\"" + project_id +"\"  data-link=\"" + pdf_file_link + "\" style=\"color: red; cursor: pointer;\"> X Remove PDF </span>");



                                /*if (plink["is_read"] == 0 ) {
                                    $( "#admin_main_client_projects" ).append("<span style='color: red;'> *new upload*</span>");
                                }*/

                                $( "#admin_main_client_projects" ).append("<br />");

                            }
                        }


                    }

                } else {
                    $( "#admin_main_client_projects" ).append( "<h2>No project found</h2>");
                }

            });

            var data = {
                action: 'admin_main_messages_action',
                dataType: JSON,
                user_id: user_id
            };

            jQuery.post( ajaxurl, data, function(response) {
                //console.log('admin main messages' + response);
                $( "#admin_main_message_from_client" ).empty();
                if (response) {
                    var responseObj = jQuery.parseJSON(response);

                    for (var i = 0; i < responseObj.length; i++) {
                        var message_id = responseObj[i].message_id;
                        var message_subject = responseObj[i].message_subject;
                        var message_content = responseObj[i].message_content;
                        //var sending_date = responseObj[i].sending_date;
                        var sending_date = new Date(responseObj[i].sending_date);
                        sending_date = sending_date.format("%m-%d-%y");

                        var is_read = responseObj[i].is_read;
                        //console.log(message_subject);
                        $( "#admin_main_message_from_client" ).append("<br />&#8226; <b>Subject:</b> " + message_subject + "<b> | </b> First Message Date: " + sending_date);
                        if (is_read == 0) {
                            var st = "display: inline-block;  color: red; font-size: 0.8em; cursor: pointer;";
                            $( "#admin_main_message_from_client" ).append(
                                "<div class=admin_main_message_is_read style=\"" + st + "\" data-id=" + message_id + "> &nbsp;*New* [<u>mark as read</u>]</div>");
                        }
                    }
                } else {
                    $( "#admin_main_message_from_client" ).append("<h2>No message!</h2>");
                }

            });

        });

        /*************************************** Global events ***************************************/

        if ($.cookie("user_id")) {
            var user_id = $.cookie("user_id");
            console.log('user_id=' + user_id);
            $( '#admin_main_clients' ).val( user_id );
            $( "#admin_main_clients" ).trigger( "change" );
        }

        /******************************************* end **********************************************/

		/*
		**  Delete pdf-link from project
        */

        $('#admin_main_client_projects').on('click',  ".remove_pdf_from_project", function() {

            var result = confirm("Want to delete?");
            if (result) {
                var link = $(this).data("link");
                var project_id = $(this).data("project_id");
                console.log(project_id + ' ' + link);

                var data = {
                    action: 'delete_pdf_link_from_project_action',
                    project_id: project_id,
                    link: link
                };
                jQuery.post( ajaxurl, data, function(response) {
                    console.log(response);
                    $( '#admin_main_clients' ).trigger( 'change' );
                });

            }

        });

        /*
        ** Update project description
        */

        $('#admin_main_client_projects').on('click',  ".project-description-edit", function() {
            var project_id = $(this).data("id");
            var description = $(this).data("description");
            var desc_dialog = prompt('Edit project description', description);
            console.log('project-description-edit ' + desc_dialog);

            var data = {
                action: 'update_project_description_action',
                project_id: project_id,
                description: desc_dialog
            };
            jQuery.post( ajaxurl, data, function(response) {
                console.log(response);
                $( '#admin_main_clients' ).trigger( 'change' );
            });

        });


        $('#upload_pdf_form').submit(function(event) {
            // if ($("#myarea").text() === "" ) {
            //     event.preventDefault();
            // }
            /*event.preventDefault();
            console.log('upload_pdf_form');
            alert('upload_pdf_form');*/
        });


		$( '#pdf_file_upload' ).change(function() {
    	    //console.log("#pdf_file_upload");
            var client_id = $( '#admin_main_clients' ).val();
    	    var project_id = $( '#project_list' ).val();
			$( '#pdf_file_name' ).val(this.files[0].name);
    		
    		//    for (var i = 0; i < this.files.length; i++){
	     	//    		var file =  this.files[i];
		    //     console.group("File "+ i);
		    //     console.log("name : " + file.name);
		    //     console.log("size : " + file.size);
		    //     console.log("type : " + file.type);
		    //     console.log("date : " + file.lastModified);
		    //     console.groupEnd();
    		// }

    		var data = {
				action: 'upload_pdf_action',
				dataType: JSON,
				project_id: project_id,
				//file_name: this.files[0]
				//last_update: last_update,
				//user_id: user_id
			};

			jQuery.post( ajaxurl, data, function(response) {
				//var responseObj = jQuery.parseJSON(response);
				console.log(response);
				//$( "#admin_message_content" ).append( "&nbsp;&nbsp;&nbsp;" + message_content + "<br />" );
				//$( '#admin_messaging_messages' ).trigger( 'change' );
			});

		});



		$( "#admin_messaging_clients" ).change(function() {
 			$( "#admin_message_subject" ).html( "" );
			$( "#admin_message_content" ).html( "" );
 			var client_id = $( "#admin_messaging_clients option:selected" ).val();
 			var client_name = $( "#admin_messaging_clients option:selected" ).text();
	    	var data = {
				action: 'select_client_action',
				user_id: client_id
			};
			jQuery.post( ajaxurl, data, function(response) {
				$( "#admin_messaging_messages" ).html( response );
				$( "#client_company_name" ).html( '<b>' + client_name + '</b>' );
				$( '#admin_messaging_messages' ).trigger( 'change' );
			});
		});

        $( '#admin_messaging_clients' ).trigger( 'change' );

		$( "#admin_messaging_messages" ).change(function() {
			
			$( "#admin_message_content" ).empty();

			var message_id = $( this ).val();
			
			var data = {
				action: 'select_message_action',
				dataType: JSON,
				message_id: message_id
			};
			jQuery.post( ajaxurl, data, function(response) {
				if (response) {
					console.log(response);
					var responseObj = jQuery.parseJSON(response);
					
					
					responseObj.forEach(function(entry) {
		    			//console.log(entry);	    			
		    			var message_subject = entry.message_subject;
						var message_content = entry.message_content;
						var message_parent = entry.parent_id;
						var sending_date = entry.sending_date;
						var to_admin = entry.to_admin;
						
						var message_to_admin = 
						'<div class="message_to_admin">' 
							+ message_content 
						+ '<div class="message_to_admin_date">' 
							+ sending_date + '</div></div>';
					var message_to_client = 
						'<div class="message_to_client">'
							+ message_content
						+ '<div class="message_to_client_date">'
							+ sending_date 
						+ '</div></div>';

						if ( to_admin == "1" ) {
							$( "#admin_message_content" ).append( message_to_admin );	
						} else {
							$( "#admin_message_content" ).append( message_to_client );	
						}
					});
				} else {
					$( "#admin_message_content" ).html( "<b>No message found</b>" );
				}


				
			});
		});


        $( "#admin_main_send_message" ).click(function() {
            var message_subject = $( "#admin_main_new_message_subject" ).val();
            var message_content = $( "#admin_main_new_message_content" ).val();
            var user_id = $( "#admin_main_clients" ).val();
            var data = {
                action: 'admin_main_send_message_action',
                dataType: JSON,
                message_subject: message_subject,
                message_content: message_content,
                user_id: user_id
            };
            jQuery.post( ajaxurl, data, function(response) {
                console.log(response);
                $( '#admin_main_clients' ).trigger( 'change' );
                $( "#admin_main_new_message_subject" ).empty();
                $( "#admin_main_new_message_content" ).empty();
                alert("Message sent");
            });
        });

		$( "#admin_messaging_send_message" ).click(function() {
			var message_content = $( "#respond_message_content" ).val();
			var message_id = $( "#admin_messaging_messages" ).val();
			var user_id = $( "#admin_messaging_clients" ).val();
			var data = {
				action: 'send_message_to_client_action',
				dataType: JSON,
				message_id: message_id,
				message_content: message_content,
				user_id: user_id
			};
			jQuery.post( ajaxurl, data, function(response) {
				//var responseObj = jQuery.parseJSON(response);
				console.log(response);
				//$( "#admin_message_content" ).append( "&nbsp;&nbsp;&nbsp;" + message_content + "<br />" );

                $( '#respond_message_content' ).empty();
                $( '#admin_messaging_messages' ).trigger( 'change' );
			});
		});


		$( "#new_project_create" ).click(function() {
			var new_project_name = $( "#new_project_name" ).val();
			var new_project_description = $( "#new_project_description" ).val();
			var user_id = $( "#admin_main_clients  option:selected" ).val();
			var data = {
				action: 'new_project_create_action',
				dataType: JSON,
				new_project_name: new_project_name,
				new_project_description: new_project_description,
				user_id: user_id
			};
			jQuery.post( ajaxurl, data, function(response) {
				console.log(response);
				$( '#admin_main_clients' ).trigger( 'change' );
			});
		});





        $('#admin_main_message_from_client').on('click',  ".admin_main_message_is_read", function() {

            var message_id = $(this).data("id");
            console.log(message_id);
            var data = {
                action: 'admin_main_message_is_read_action',
                dataType: JSON,
                message_id: message_id
            };
            jQuery.post( ajaxurl, data, function(response) {
                $( '#admin_main_clients' ).trigger( 'change' );
            });

        });

	});
	</script>
	<?php
}


/**
 *  Delete pdf-link from project
 */

add_action('wp_ajax_delete_pdf_link_from_project_action', 'delete_pdf_link_from_project_action_callback');
function delete_pdf_link_from_project_action_callback()
{

    global $wpdb;

    if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
        $project_id = $_POST['project_id'];
        $link_for_delete = $_POST['link'];

        $sql = "SELECT project_id, project_links from wp_client_portal_projects where project_id = $project_id";
        $project = $wpdb->get_results( $sql );

        if ($project) {
            $links = $project[0]->project_links;
            $links = unserialize($links);

            for ( $i = 0; $i < count( $links ); $i++ ) {
                $link = $links[$i]->file_link;
                if( $link == $link_for_delete ) {
                    unset( $links[$i] );
                }
            }

            $links = serialize($links);

            $sql = "UPDATE wp_client_portal_projects SET project_links='$links' where project_id = $project_id";
            $update_project_links = $wpdb->get_results( $sql );

            $update_project_links = json_encode( $update_project_links );
            echo $update_project_links;
        }
    }
    wp_die();
}

/**
 *  Update project description
 */

add_action('wp_ajax_update_project_description_action', 'update_project_description_action_callback');
function update_project_description_action_callback()
{

    global $wpdb;

    if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
        $project_id = $_POST['project_id'];
        $description = $_POST['description'];

        $sql = "UPDATE wp_client_portal_projects SET description=\"$description\" where project_id = $project_id";

        $update_desc = $wpdb->get_results($sql);
    }
    $update_desc = json_encode($sql);
    echo $update_desc;

    wp_die();
}

add_action('wp_ajax_admin_main_message_is_read_action', 'admin_main_message_is_read_action_callback');
function admin_main_message_is_read_action_callback()
{

    global $wpdb;

    if (isset($_POST['message_id']) && !empty($_POST['message_id'])) {
        $message_id = $_POST['message_id'];
        $sql = "UPDATE wp_client_portal_messages SET is_read=1 where message_id = $message_id";
        $is_read = $wpdb->get_results($sql);
    }
    $is_read = json_encode($is_read);
    echo $is_read;

    wp_die();
}


add_action('wp_ajax_admin_main_messages_action', 'admin_main_messages_action_callback');
function admin_main_messages_action_callback() {

    global $wpdb;

    if ( isset( $_POST['user_id'] ) && !empty( $_POST['user_id']) ) {
        $user_id = $_POST['user_id'];
        $sql = "SELECT message_id, message_content, message_subject, sending_date, parent_id, user_id, is_read 
				FROM wp_client_portal_messages 
			WHERE user_id = $user_id AND to_admin = 1 
			ORDER BY sending_date DESC";

        $messages = $wpdb->get_results( $sql );

        if ($messages) {
            $aMessages[] = new clientMessage;
            $i = 0;
            foreach ($messages as $message) {
                $sending_date = new DateTime($message->sending_date);
                $sending_date = $sending_date->format('m/d/Y g:i A');

                $aMessages[$i]->message_id = $message->message_id;
                $aMessages[$i]->parent_id = $message->parent_id;
                $aMessages[$i]->message_subject = $message->message_subject;
                $aMessages[$i]->message_content = $message->message_content;
                $aMessages[$i]->sending_date = $sending_date;
                $aMessages[$i]->to_admin = $message->to_admin;
                $aMessages[$i]->is_read = $message->is_read;
                $i++;
            }
            $aMessages = json_encode($aMessages);
            echo $aMessages;
        } else {
            echo false;
        }
    }

    wp_die();
}

add_action('wp_ajax_admin_main_clients_action', 'admin_main_clients_action_callback');
function admin_main_clients_action_callback() {
	
	global $wpdb;
	if ( isset( $_POST['user_id'] ) && !empty( $_POST['user_id']) ) {
		$user_id = $_POST['user_id'];
		$sql = "SELECT project_id, project_name, description, last_update, project_links 
				FROM wp_client_portal_projects 
				WHERE user_id = " . $user_id . " ORDER BY last_update DESC";
		
		$projects = $wpdb->get_results( $sql ); 
			
		foreach ( $projects as $project ) {
			$project->project_links = unserialize( $project->project_links );
		}
		$projects = json_encode( $projects );
		echo $projects;
	}
	wp_die();
}

add_action('wp_ajax_new_project_create_action', 'new_project_create_action_callback');
function new_project_create_action_callback() {
	
	global $wpdb;

	if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
		$user_id = $_POST['user_id'];
		$new_project_name = $_POST['new_project_name'];
		$new_project_description = $_POST['new_project_description'];
		
		$last_update = new DateTime();
		$last_update = $last_update->format('Y-m-d h:i:s');
		
		$sql = "INSERT INTO wp_client_portal_projects (user_id, project_name, description, last_update)
				VALUES ($user_id, \"$new_project_name\", \"$new_project_description\", \"$last_update\")";

		$new_project = $wpdb->get_results( $sql );

        $sql = "INSERT INTO wp_client_portal_messages (parent_id, message_subject, message_content, user_id, sending_date)
					  VALUES (0, \"$new_project_name - New project was created\", \"$new_project_name - New project was created\", $user_id, \"$last_update\")";

        $insert_message = $wpdb->get_results( $sql );

        $user_info = get_userdata($user_id);
        $master_user_email = $user_info->user_email;

        $emails = array();

        $client_emails = get_user_meta( $user_id, "_client_emails", true );

        if ($client_emails != "") {
            $emails = unserialize($client_emails);
        }


        $multiple_to_recipients = $emails;

        add_filter( 'wp_mail_content_type', 'set_html_content_type' );

        wp_mail( $multiple_to_recipients, 'The subject', '<p>The <em>HTML</em> message</p>' );

        // Сбросим content-type, чтобы избежать возможного конфликта
        remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

        function set_html_content_type() {
            return 'text/html';
        }


        print_r($emails);
		
	}

	wp_die(); 
}

add_action('wp_ajax_upload_pdf_action', 'upload_pdf_action_callback');
function upload_pdf_action_callback() {
	
	global $wpdb;

	$pFiles[] = new projectFiles;

	if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {
		$project_id = $_POST['project_id'];
		$file_name = $_FILES;

		
		$sql = "select project_id, project_name, user_id, project_links, description, last_update from wp_client_portal_projects where project_id = $project_id";


		//$pFiles = json_encode($pFiles);
		//$pFiles = json_encode($project_links);
		$file_name = json_encode($file_name);
		print_r($file_name);
		//echo $pFiles;
	}

	wp_die(); 
}





add_action('wp_ajax_select_client_action', 'select_client_action_callback');
function select_client_action_callback() {
	global $wpdb;

	$user_id = $_POST['user_id'];
	$sql = "SELECT message_id, message_content, message_subject, sending_date, parent_id, user_id 
				FROM wp_client_portal_messages 
			WHERE user_id = $user_id AND parent_id = 0 
			ORDER BY sending_date DESC";

	$messages = $wpdb->get_results( $sql );
	$m = "";

	foreach ($messages as $message) {
		$m .= '<option value="' . $message->message_id . '">' . $message->message_subject . '</option>';
	}

	echo $m;

	wp_die(); 
}

add_action('wp_ajax_select_message_action', 'select_message_action_callback');
function select_message_action_callback() {
	global $wpdb;

	$fMessages[] = new clientMessage;

	if (isset($_POST['message_id']) && !empty($_POST['message_id'])) {
		$message_id = $_POST['message_id'];
		
		$sql = "SELECT message_id, message_content, message_subject, sending_date, parent_id, user_id, to_admin
					FROM wp_client_portal_messages 
					WHERE message_id = $message_id or parent_id = $message_id 
					ORDER BY sending_date DESC" ;

		$messages = $wpdb->get_results( $sql );

		$m = "";
		$i = 0;
		foreach ($messages as $message) {
			
			$sending_date = new DateTime($message->sending_date);
			$sending_date = $sending_date->format('m/d/Y g:i A');
			
			$fMessages[$i]->message_id = $message->message_id;
			$fMessages[$i]->parent_id = $message->parent_id;
			$fMessages[$i]->message_subject = $message->message_subject;
			$fMessages[$i]->message_content = $message->message_content;
			$fMessages[$i]->sending_date = $sending_date;
			$fMessages[$i]->to_admin = $message->to_admin;
			$i++;
		}

		$fMessages = json_encode($fMessages);
		//print_r($fMessages);
		echo $fMessages;
	}

	wp_die(); 
}

add_action('wp_ajax_send_message_to_client_action', 'send_message_to_client_action_callback');
function send_message_to_client_action_callback() {
	global $wpdb;

	$message_id = $_POST['message_id'];
	$message_content = $_POST['message_content'];
	$user_id = $_POST['user_id'];

	$sending_date = new DateTime();
	$sending_date = $sending_date->format('Y-m-d h:i:s');

	$sql = "INSERT INTO wp_client_portal_messages (parent_id, message_content, user_id, sending_date)
										   VALUES ($message_id, \"$message_content\", $user_id, \"$sending_date\")";
	
	$insert_message = $wpdb->get_results( $sql );
	
	//$rMessage = json_encode($rMessage);

	echo $message_content;

	wp_die(); 
}


add_action('wp_ajax_admin_main_send_message_action', 'admin_main_send_message_action_callback');
function admin_main_send_message_action_callback() {
    global $wpdb;

    $message_subject = $_POST['message_subject'];
    $message_content = $_POST['message_content'];
    $user_id = $_POST['user_id'];

    $sending_date = new DateTime();
    $sending_date = $sending_date->format('Y-m-d h:i:s');

    $sql = "INSERT INTO wp_client_portal_messages (message_subject, message_content, user_id, sending_date)
										   VALUES (\"$message_subject\", \"$message_content\", $user_id, \"$sending_date\")";

    $insert_message = $wpdb->get_results( $sql );

    echo $insert_message;

    wp_die();
}



?>