<?php
	session_start();

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);	
	
	function check_system_date($date_to_check) {
		
		$d  =  date('d-m-Y', strtotime($date_to_check));
		if ($d == "01-Jan-1970") {$d = "";}
		return $d;
	}
	
	
	// include logout functionality
	//include 'fta/application_form_logout_include.php';

	// include db connection
	include('db/databaseConnection.php');
	$db = new DatabaseConnection();

	require_once('fta/EBS4WebService.php');	

	// create a new web service object for use later.
	$ws = new EBS4WebService();
	$a_token = $ws->getResponse();

	$pk = "";
	$is_resume = "No";
	
	// see if pk set
	if (isset($_SESSION['application_pk'])) {
		$pk = $_SESSION['application_pk'];
		$is_resume = "Yes";
	}
	
	if ($pk == "") {
	
		$sql = "INSERT INTO online_enrolment (created_on, enrolment_type)
							VALUES (NOW(),'FTA');";

		$result = $db->runSql($sql);
		// get back the last ID generated
		$pk = $db->getLastId();	

		
		$_SESSION['application_pk'] = $pk;	
	} 
	
	// now get that row
	$sql = "SELECT * FROM online_enrolment WHERE pk = $pk;";
	$result = $db->runSql($sql);	
	$oe = $result->fetch_assoc();
	
	$is_use_test = "";
	if(isset($_GET['is_use_test'])){ $is_use_test = $_GET['is_use_test']; }	
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">	
<html>


<head>
	<title>
		Leicester College :: Full Time Application 
	</title>


	<meta charset="utf-8" />
	<link rel="stylesheet" href="css/jquery-ui.css" />
	<script src="jquery/jquery-1.9.1.js"></script>
	<script src="jquery/jquery-ui.min.js"></script>		
	
	<!-- bootstrap  -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>		

	<!-- jSqignature -->
	<script src="jquery/jSignature.min.js"></script>			
	
	<meta charset="utf-8"> 		
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />		
	
	<style type="text/css">
	
		h4 {
			background-color:#009aca;
			color:white;
			padding:5px;
			border-radius:5px;
		}
	
	</style>
	

	<script type="text/javascript">
	
		// flag for knowing if a save has happened
		save_occurred = false;
	
		console.log("<?php echo "mit:" . $is_use_test; ?>");		
	
		function do_logout() {
		
			if (confirm("Are you sure you wish to logout?  (To continue this application later you can send yourself a link using the button at the bottom of the form)")) {
				window.location="logout.php";
			}
		
		}
	
	
		function close_all(){
		  $('.panel-collapse.in').collapse('hide');
		}
				
		function open_all(){
		  $('.panel-collapse:not(".in")').collapse('show');
		}

		function reset_error_class(element_id) {
			$("#" + element_id).css('color', 'black');
		}			
		
		// ensure a correct format of email address has been entered.
		function validatePostcode(postcode) { 
			var re = /[A-Z]{1,2}[0-9]{1,2}[ ]{0,1}[0-9][A-Z]{2}/i;
			return re.test(postcode);
		} 			

		// ensure a correct format of phone number has been entered.
		function validatePhone(phone_number) { 
			var re = /^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?\#(\d{4}|\d{3}))?$/;
			return re.test(phone_number);
		}		
	
		// ensure a correct format of email address has been entered.
		function validateEmail(email) { 
			var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return re.test(email);
		} 

		function toggle_travel_college() {
		
			// get the value of the checkbox
			var travel_college = $('#travel_college').val();			
	
			// hide the div first
			$('#travel_college_other_div').hide();

			// then show if needed
			if (travel_college == "Other") {$('#travel_college_other_div').show();}
		
		}
		
		function toggle_lsf_apply() {
		
			// get the value of the checkbox
			var financial_hardship = $('#financial_hardship').is(':checked');
	
			// hide the div first
			$('#lsf_apply_div').hide();

			// then show if needed
			if (financial_hardship) {$('#lsf_apply_div').show();}
		
		}
		
		
		function toggle_marketing_questions() {
		
			$('#marketing_questions').hide();
			
			var consent_contact_courses	= 	$('#consent_contact_courses').is(':checked');			
			var consent_contact_surveys	= $('#consent_contact_surveys').is(':checked');			
			var consent_contact_marketing = $('#consent_contact_marketing').is(':checked');			

			if (consent_contact_courses == true || consent_contact_surveys == true || consent_contact_marketing == true) {

				$('#marketing_questions').show();
		
			}			
		
		}

		function toggle_employment_status_questions() {

			// hide all first first
			$("#paid_employment_questions").hide();			
			$("#retirement_questions").hide();						
			$("#unemployment_questions").hide();					
		
			var employment_status = $('#employment_status').val();			
			
			if (employment_status == "Paid employment") {
				$("#paid_employment_questions").show();							
			}

			if (employment_status == "Retired") {
				$("#retirement_questions").show();							
			}

			if (employment_status == "Unemployed and available to start work" || employment_status == "Unemployed and NOT available to start work") {
				$("#unemployment_questions").show();							
			}
		
		}
	
		
		function toggle_quals_achieved_questions() {

			$('#quals_achieved_div').hide();
			
			var any_previous_quals = $('input[name=any_previous_quals]:checked').val();
		
			if (any_previous_quals == "Yes") {
				$('#quals_achieved_div').show();
			} 
		}		

		function toggle_quals_currently_studying_questions() {

			$('#quals_currently_studying_questions').hide();
		
			var is_currently_studying = $('input[name=is_currently_studying]:checked').val();
		
			if (is_currently_studying == "Yes") {
				$('#quals_currently_studying_questions').show();
			}
		
		}		


	
		function toggle_student_visa_questions() {
		
			$('#student_visa_questions').hide();
		
			var student_visa = $('input[name=student_visa]:checked').val();
			
			if (student_visa == "Yes") {
				$('#student_visa_questions').show();
			}
		}
					
		function toggle_asylum_questions() {
		
			$('#asylum_questions').hide();
		
			var language = $('input[name=asylum_seeker]:checked').val();
			
			if (language == "Yes") {
				$('#asylum_questions').show();
			}
		}


		function toggle_language_questions() {

			$('#language_questions').hide();
		
			var language = $('input[name=language]:checked').val();
			
			if (language == "No") {
				$('#language_questions').show();
			}
		}
	

		function toggle_disability_questions() {
		
			$('#disability_questions').hide();
		
			var consider_disability_difficulty = $('input[name=consider_disability_difficulty]:checked').val();
			
			if (consider_disability_difficulty == "Yes") {
				$('#disability_questions').show();
			}
		}


		function toggle_date_entry_questions() {
			
			// hide first
			$("#date_entry_uk_questions").hide();
					
			var uk_last_three_years = $('input[name=uk_last_three_years]:checked').val();
					
			// if yes then show
			if (uk_last_three_years == "No") {
				$("#date_entry_uk_questions").show();				
			}
		}	

		function set_lat_lng(position) {
			
			document.getElementById('lng').value = position.coords.longitude;
			document.getElementById('lat').value = position.coords.latitude;		

		}		
		
		//document.ready
		$(function() {

			// get lng and lat
			if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(set_lat_lng);
			} 		
			
			$('#confirm_email_address').bind("cut copy paste",function(e) {
					  e.preventDefault();
				  });		

			$('#email_address').bind("cut copy paste",function(e) {
					  e.preventDefault();
				  });
				  
		
			$("#signature").jSignature({
						'height': '200px',
						'decor-color': 'transparent',
						'background-color': 'transparent',
						'decor-color': 'transparent',
					});	
	
			var availableTags = <?php  include('fta/course_list_2.php'); ?>;
			
			$( "#course_1_select" ).autocomplete({
			  source: availableTags,
				select: function(event, ui) { 
						 
						 // set the hidden value with the UIOID of the selected course
						 document.getElementById("course_1_chosen").value = ui.item.uioid;
					}		  
			  
			});

			$( "#course_1_select" ).keypress(function() {
				changes_to_inputs_made = true;
			});		
			
			$( "#course_2_select" ).autocomplete({
			  source: availableTags,
				select: function(event, ui) { 
						 document.getElementById("course_2_chosen").value = ui.item.uioid;
					}		  
			});
			
			$( "#course_3_select" ).autocomplete({
			  source: availableTags,
				select: function(event, ui) { 
						 document.getElementById("course_3_chosen").value = ui.item.uioid;
					}		  
			});
		});	

		function check_disability() {

			var not_valid = false;
			$("#disability_heading").css('background-color', '#009aca');

			// consider_disability_difficulty
			var consider_disability_difficulty = $('input[name=consider_disability_difficulty]:checked').val();

			// disability
			var disability = $.trim(document.getElementById('disability').value);

			// learning_difficulty				
			var learning_difficulty	= $.trim(document.getElementById('learning_difficulty').value);
			
			if (consider_disability_difficulty == "" || consider_disability_difficulty == undefined) {
				$("#consider_disability_difficulty_label").css('color', '#de5555');
				not_valid = true;
			}				

			if (consider_disability_difficulty == "Yes") {

				if (disability == "" && learning_difficulty == "") {
		
					$("#disability_label").css('color', '#de5555');
					$("#learning_difficulty_label").css('color', '#de5555');

					not_valid = true;						
				}
			}

			// special_arrangement_exams
			var special_arrangement_exams	= $('input[name=special_arrangement_exams]:checked').val(); 
			
			if (special_arrangement_exams == "" || special_arrangement_exams == undefined) {
				$("#special_arrangement_exams_label").css('color', '#de5555');
				not_valid = true;
			}			
			
			// support_at_interview
			var support_at_interview	= $('input[name=support_at_interview]:checked').val(); 
			
			if (support_at_interview == "" || support_at_interview == undefined) {
				$("#support_at_interview_label").css('color', '#de5555');
				not_valid = true;
			}			

			// extra_support_reading_writing
			var extra_support_reading_writing	= $('input[name=extra_support_reading_writing]:checked').val();  
			
			if (extra_support_reading_writing == "" || extra_support_reading_writing == undefined) {
				$("#extra_support_reading_writing_label").css('color', '#de5555');
				not_valid = true;
			}
			
			
			// extra_support_numeracy
			var extra_support_numeracy	= $('input[name=extra_support_numeracy]:checked').val();   
			
			if (extra_support_numeracy == "" || extra_support_numeracy == undefined) {
				$("#extra_support_numeracy_label").css('color', '#de5555');
				not_valid = true;
			}			

			if (extra_support_reading_writing == "Yes" && extra_support_numeracy == "No") {

				document.getElementById("extra_support").value = "1";
			
			} else if (extra_support_reading_writing == "Yes" && extra_support_numeracy == "Yes") {
		
				document.getElementById("extra_support").value = "2";
			
			} else if (extra_support_reading_writing == "No" && extra_support_numeracy == "Yes") {

				document.getElementById("extra_support").value = "3";

			} else if (extra_support_reading_writing == "No" && extra_support_numeracy == "No") {

				document.getElementById("extra_support").value = "4";			
			}			
			
			
			// statement_of_needs
			var statement_of_needs	= $('input[name=statement_of_needs]:checked').val();   
			
			if (statement_of_needs == "" || statement_of_needs == undefined) {
				$("#statement_of_needs_label").css('color', '#de5555');
				not_valid = true;
			}
			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#disability_heading").css('background-color', '#61bd61');
			} else {
				$("#disability_heading").css('background-color', '#de5555');
			}
			
			return not_valid;				
		}

		function check_declaration() {

			var not_valid = false;
			$("#declaration_heading").css('background-color', '#009aca');
			
			var criminal_convictions = $('input[name=criminal_convictions]:checked').val();	

			if (criminal_convictions == "" || criminal_convictions == undefined) {
				$("#criminal_convictions_label").css('color', '#de5555');
				not_valid = true;
			}				

			// make sure t and c box has been checked.
			var accept_terms_conditions = $('#accept_terms_conditions').is(':checked');																	
			
			if (accept_terms_conditions == false) {
				$("#accept_terms_conditions_label").css('color', '#de5555');
				$("#accept_terms_conditions_link").css('color', '#de5555');
			
				not_valid = true;
			}
			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#declaration_heading").css('background-color', '#61bd61');
			} else {
				$("#declaration_heading").css('background-color', '#de5555');
			}
			
			return not_valid;					
		}
		
		
		function check_employment_status() {

			var not_valid = false;
			$("#employment_status_heading").css('background-color', '#009aca');
		
			// mandatory questions depending on emp status
			var date_employment_started_day = $.trim(document.getElementById('date_employment_started_day').value);
			var date_employment_started_month = $.trim(document.getElementById('date_employment_started_month').value);
			var date_employment_started_year = $.trim(document.getElementById('date_employment_started_year').value);				

			var date_employment_status_began = date_employment_started_year + "/" + date_employment_started_month  + "/" + date_employment_started_day;				
			// store this back into a hidden input so that serialize picks it up
			if (date_employment_status_began == "//") {date_employment_status_began = "1900/01/01";}
			document.getElementById('date_employment_status_began').value = date_employment_status_began;				
			
			
			var hours_per_week_employed = $.trim(document.getElementById('hours_per_week_employed').value);				
							
			var self_employed = $('input[name=self_employed]:checked').val();	
							
			var unemployment_started_day = $.trim(document.getElementById('unemployment_started_day').value);				
			var unemployment_started_month = $.trim(document.getElementById('unemployment_started_month').value);								
			var unemployment_started_year = $.trim(document.getElementById('unemployment_started_year').value);								

			var date_unemployment_status_began = unemployment_started_year + "/" + unemployment_started_month  + "/" + unemployment_started_day;				
			// store this back into a hidden input so that serialize picks it up
			if (date_unemployment_status_began == "//") {date_unemployment_status_began = "1900/01/01";}
			document.getElementById('date_unemployment_status_began').value = date_unemployment_status_began;	

			
			var unemployment_length = $.trim(document.getElementById('unemployment_length').value);								
							
			var receipt_jsa	= $('input[name=receipt_jsa]:checked').val();
			if (receipt_jsa == undefined) {receipt_jsa = "No";}
			
			var receipt_esa	= $('input[name=receipt_esa]:checked').val();
			if (receipt_esa == undefined) {receipt_esa = "No";}

			var receipt_universal_credit = $('input[name=receipt_universal_credit]:checked').val();
			if (receipt_universal_credit == undefined) {receipt_universal_credit = "No";}

			var receipt_other_benefit = $.trim(document.getElementById('receipt_other_benefit').value);							
			
			// employment status
			var employment_status = $.trim(document.getElementById('employment_status').value);
			
			if (employment_status == "") {
				$("#employment_status_label").css('color', '#de5555');
				not_valid = true;
			}			

			var retirement_started_day = $.trim(document.getElementById('retirement_started_day').value);				
			var retirement_started_month = $.trim(document.getElementById('retirement_started_month').value);								
			var retirement_started_year = $.trim(document.getElementById('retirement_started_year').value);								

			var date_retirement_status_began = retirement_started_year + "/" + retirement_started_month  + "/" + retirement_started_day;				
			// store this back into a hidden input so that serialize picks it up
			if (date_retirement_status_began == "//") {date_retirement_status_began = "1900/01/01";}
			document.getElementById('date_retirement_status_began').value = date_retirement_status_began;

			
			var retirement_length = $.trim(document.getElementById('retirement_length').value);				
			
			if (employment_status == "Paid employment") {
			
				if (date_employment_started_day == "" || date_employment_started_month == "" || date_employment_started_year == "") {
					$("#date_employment_started_label").css('color', '#de5555');
					not_valid = true;
				}

				if (hours_per_week_employed == "" ) {
					$("#hours_per_week_employed_label").css('color', '#de5555');
					not_valid = true;
				}

				if (self_employed == "" || self_employed == undefined) {
					$("#self_employed_label").css('color', '#de5555');
					not_valid = true;
				}					
			}

			if (employment_status == "Unemployed and available to start work" || employment_status == "Unemployed and NOT available to start work" ) {

				if (unemployment_started_day == "" || unemployment_started_month == "" || unemployment_started_year == "") {
					$("#unemployment_started_label").css('color', '#de5555');
					not_valid = true;
				}

				if (unemployment_length == "") {
					$("#unemployment_length_label").css('color', '#de5555');
					not_valid = true;
				}
			}
			
			if (employment_status == "Retired") {

				if (retirement_started_day == "" || retirement_started_month == "" || retirement_started_year == "") {
					$("#retirement_started_label").css('color', '#de5555');
					not_valid = true;
				}					

				if (retirement_length == "") {
					$("#retirement_length_label").css('color', '#de5555');
					not_valid = true;
				}					

			}

			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#employment_status_heading").css('background-color', '#61bd61');
			} else {
				$("#employment_status_heading").css('background-color', '#de5555');
			}
			
			return not_valid;				
		}
		
		
		function check_quals_currently_studying() {

			var not_valid = false;
			$("#quals_currently_studying_heading").css('background-color', '#009aca');

			var is_currently_studying = $('input[name=is_currently_studying]:checked').val();			
			
			if (is_currently_studying == undefined) {

				$("#is_currently_studying_label").css('color', '#de5555');
				not_valid = true;

			}

			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#quals_currently_studying_heading").css('background-color', '#61bd61');
			} else {
				$("#quals_currently_studying_heading").css('background-color', '#de5555');
			}
			
			return not_valid;	
		}
		
		function check_quals_achieved() {

			var not_valid = false;
			$("#quals_achieved_heading").css('background-color', '#009aca');
		
			var any_previous_quals = $('input[name=any_previous_quals]:checked').val();
			var highest_qual = $('input[name=highest_qual]:checked').val();
			
			if (any_previous_quals == undefined || any_previous_quals == "") {

				$("#any_previous_quals_label").css('color', '#de5555');
				$("#any_previous_quals_label").css('font-weight', 'bold');
				not_valid = true;

			} else if (any_previous_quals == "Yes") {
			
				if (highest_qual == undefined) {
					$("#highest_qual_label").css('color', '#de5555');
					$("#highest_qual_label").css('font-weight', 'bold');
					not_valid = true;
				}
			} else if (any_previous_quals == "No") {
			
				$('input:radio[name=highest_qual]').val(['99 No qualifications']);
				$('#highest_qual').prop('checked', true);
			
			}

	
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#quals_achieved_heading").css('background-color', '#61bd61');
			} else {
				$("#quals_achieved_heading").css('background-color', '#de5555');
			}
			
			return not_valid;	
		}
		
		
	
		
		function check_previous_study() {

			var not_valid = false;
			$("#previous_study_heading").css('background-color', '#009aca');
		
			var uln = Number(document.getElementById("uln").value);
			

			if ( isNaN(uln) ) {

				$("#uln_label").css('color', '#de5555');
				not_valid = true;				

			}
		
		
			var studied_before = $('input[name=studied_before]:checked').val();
	
			if (studied_before == "" || studied_before == undefined) {
				$("#studied_before_label").css('color', '#de5555');
				not_valid = true;
			}

			var choice_college = $('input[name=choice_college]:checked').val();
	
			if (choice_college == "" || choice_college == undefined) {
				$("#choice_college_label").css('color', '#de5555');
				not_valid = true;
			}

			
			// enrolled elsewhere
			var enrolled_other_establishment = $('input[name=enrolled_other_establishment]:checked').val();
		
			if (enrolled_other_establishment == "" || enrolled_other_establishment == undefined) {
				$("#enrolled_other_establishment_label").css('color', '#de5555');
				not_valid = true;
			}

			// last_school_college
			var last_school_college = $.trim(document.getElementById('last_school_college').value);
			
			if (last_school_college == "") {
				$("#last_school_college_label").css('color', '#de5555');
				not_valid = true;
			}				
			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#previous_study_heading").css('background-color', '#61bd61');
			} else {
				$("#previous_study_heading").css('background-color', '#de5555');
			}

			return not_valid;
		}

		function update_character_count(event, textarea_id) {
		
			// get the character length of the textarea
			var len = document.getElementById(textarea_id).value.length;
			
			document.getElementById(textarea_id +"_character_count").innerHTML = len;

			if (event.keyCode == 8 || event.keyCode == 13 || event.keyCode == 83) {

			// get the character length of the textarea
				len = document.getElementById(textarea_id).value.length;
				
				document.getElementById(textarea_id +"_character_count").innerHTML = len;
			}		

			// if length gets over 500, show in label
			if (len > 500) {
				$("#" + textarea_id + "_character_count_label").css('color', '#de5555');
			} else {
				$("#" + textarea_id + "_character_count_label").css('color', 'black');
			}

			return len;
		}

		
		function check_personal_statement() {

			var not_valid = false;
			$("#personal_statement_heading").css('background-color', '#009aca');


			// personal statement section
			var why_study = $.trim(document.getElementById('why_study').value);
	
			// check other boxes have been filled out if needs be
			if (why_study == "") {
				$("#why_study_label").css('color', '#de5555');
				not_valid = true;
			}				
			
			
			
			// work_experience section
			var work_experience = $.trim(document.getElementById('work_experience').value);
	
			// check other boxes have been filled out if needs be
			if (work_experience == "") {
				$("#work_experience_label").css('color', '#de5555');
				not_valid = true;
			}		

			
			var event = "";

			// check to see if lengths of statements have been exceeded
			var why_study_length = update_character_count(event,'why_study');

			var work_experience_length = update_character_count(event,'work_experience');			

		
			if (why_study_length > 500) {
				$("#why_study_label").css('color', '#de5555');
				not_valid = true;
			}
			
			

			if (work_experience_length > 500) {
				$("#work_experience_label").css('color', '#de5555');
				not_valid = true;
			}			
			
			
			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#personal_statement_heading").css('background-color', '#61bd61');
			} else {
				$("#personal_statement_heading").css('background-color', '#de5555');
			}

			return not_valid;			
		}
		
		
		function check_personal_details() {

			var not_valid = false;
			$("#personal_details_heading").css('background-color', '#009aca');

			// names
			var last_name  = $.trim(document.getElementById('last_name').value);
			var first_name = $.trim( document.getElementById('first_name').value);
			var middle_name = $.trim( document.getElementById('middle_name').value);				
			var previous_surname = $.trim( document.getElementById('previous_surname').value);				
			

			if (last_name == "") {
				$("#last_name_label").css('color', '#de5555');
				not_valid = true;
			}

			if (first_name == "") {
				$("#first_name_label").css('color', '#de5555');
				not_valid = true;
			}
		
			// title
			var learner_title = $.trim(document.getElementById('learner_title').value);

			if (learner_title == "") {
				$("#learner_title_label").css('color', '#de5555');
				not_valid = true;
			}

			// gender
			var learner_gender = $('input[name=learner_gender]:checked').val();
		
			if (learner_gender == "" || learner_gender == undefined) {
				$("#learner_gender_label").css('color', '#de5555');
				not_valid = true;
			}

			// gender identity
			var gender_identity = $.trim(document.getElementById('gender_identity').value);
		
			if (gender_identity == "") {
				$("#gender_identity_label").css('color', '#de5555');
				not_valid = true;
			}			
			
			// ethnicity
			var ethnicity = $.trim(document.getElementById('ethnicity').value);
		
			if (ethnicity == "") {
				$("#ethnicity_label").css('color', '#de5555');
				not_valid = true;
			}
		
			
			// date of birth
			// make sure each portion has been completed
			
			var date_of_birth_day = $.trim(document.getElementById('date_of_birth_day').value);
			var date_of_birth_month = $.trim(document.getElementById('date_of_birth_month').value);
			var date_of_birth_year = $.trim(document.getElementById('date_of_birth_year').value);
			
			if (date_of_birth_day == "" || date_of_birth_month == "" || date_of_birth_year == "") {
				$("#dob_label").css('color', '#de5555');
				not_valid = true;
			}		

			// set our hidden input
			var date_of_birth = date_of_birth_year + "/" + date_of_birth_month + "/" + date_of_birth_day;
			if (date_of_birth == "//") {date_of_birth = "1970/01/01";}
			document.getElementById('date_of_birth').value = date_of_birth;
		
			// ni number
			var national_insurance_number = $.trim(document.getElementById('national_insurance_number').value);

			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#personal_details_heading").css('background-color', '#61bd61');
			} else {
				$("#personal_details_heading").css('background-color', '#de5555');
			}
			
			return not_valid;				
			
		}
		
		function check_address_contact_details() {

			var not_valid = false;
			$("#address_contact_details_heading").css('background-color', '#009aca');			

			var living_independently = $('input[name=living_independently]:checked').val();			
			
			if (living_independently == "" || living_independently == undefined) {
				$("#living_independently_label").css('color', '#de5555');
				not_valid = true;
			}
					
			var in_care = $('input[name=in_care]:checked').val();			
			
			if (in_care == "" || in_care == undefined) {
				$("#in_care_label").css('color', '#de5555');
				not_valid = true;
			}
			
			var international_learner = $('input[name=international_learner]:checked').val();			
			
			if (international_learner == "" || international_learner == undefined) {
				$("#international_learner_label").css('color', '#de5555');
				not_valid = true;
			}
			
			
			// ---------------------------- permanent address ----------------------------------
			var permanent_address_line_1 = $.trim(document.getElementById('permanent_address_line_1').value);
			var permanent_address_line_2 = $.trim(document.getElementById('permanent_address_line_2').value);
			
			if (permanent_address_line_1 == "") {
				$("#permanent_address_line_1_label").css('color', '#de5555');
				not_valid = true;
			}				

			// ---------------------------- permanent town ----------------------------------
			var permanent_town_city = $.trim(document.getElementById('permanent_town_city').value);
			
			if (permanent_town_city == "") {
				$("#permanent_town_city_label").css('color', '#de5555');
				not_valid = true;
			}				

			// ---------------------------- permanent city ----------------------------------
			var permanent_county = $.trim(document.getElementById('permanent_county').value);
			
			if (permanent_county == "") {
				$("#permanent_county_label").css('color', '#de5555');
				not_valid = true;
			}				
		
			
			// ---------------------------- permanent postcode ----------------------------------
			var permanent_postcode_part_1 = $.trim(document.getElementById('permanent_postcode_part_1').value);
			var permanent_postcode_part_2 = $.trim(document.getElementById('permanent_postcode_part_2').value);
			
			var permanent_postcode = permanent_postcode_part_1 + " " + permanent_postcode_part_2;
			permanent_postcode = permanent_postcode.toUpperCase();
			
			if (permanent_postcode_part_1 == "" || permanent_postcode_part_2 == "") {
				$("#permanent_postcode_label").css('color', '#de5555');
				not_valid = true;
			}					

			// make sure a valid email address has been entered.  Only validating if NOT an international learner
			if (international_learner == "No" && !validatePostcode(permanent_postcode)) {
				$("#permanent_postcode_label").css('color', '#de5555');
				not_valid = true;
			}

			//set our hidden input
			document.getElementById("permanent_postcode").value = permanent_postcode;
			
			
			
			// termtime address
			var termtime_address_line_1 = $.trim(document.getElementById('termtime_address_line_1').value);				
			var termtime_address_line_2 = $.trim(document.getElementById('termtime_address_line_2').value);		
			var termtime_town_city = $.trim(document.getElementById('termtime_town_city').value);				
			var termtime_county = $.trim(document.getElementById('termtime_county').value);				
			
			var termtime_postcode_part_1 = $.trim(document.getElementById('termtime_postcode_part_1').value);								
			var termtime_postcode_part_2 = $.trim(document.getElementById('termtime_postcode_part_2').value);	

			var termtime_postcode = termtime_postcode_part_1 + " " + termtime_postcode_part_2;
			
			if ($.trim(termtime_postcode) != "") {

				if (international_learner == "No" && !validatePostcode(termtime_postcode)) {
					$("#termtime_postcode_label").css('color', '#de5555');
					not_valid = true;
				}

				if (termtime_address_line_1 == "") {
					$("#termtime_address_line_1_label").css('color', '#de5555');
					not_valid = true;					
				}

				if (termtime_town_city == "") {
					$("#termtime_town_city_label").css('color', '#de5555');
					not_valid = true;					
				}

				if (termtime_county == "") {
					$("#termtime_county_label").css('color', '#de5555');
					not_valid = true;					
				}					
				
			}					
		
			//set our hidden input
			document.getElementById("termtime_postcode").value = termtime_postcode;			
		
			// ---------------------------- home and mobile telephone ----------------------------------
			var home_telephone = $.trim(document.getElementById('home_telephone').value);
			var mobile_telephone = $.trim(document.getElementById('mobile_telephone').value);

			if (home_telephone == "" && mobile_telephone == "") {
				$("#home_telephone_label").css('color', '#de5555');
				$("#mobile_telephone_label").css('color', '#de5555');
				not_valid = true;
			}				
			
			if ( home_telephone != "" && !validatePhone(home_telephone) && international_learner == "No" ) {
				$("#home_telephone_label").css('color', '#de5555');
				not_valid = true;
			}

			if ( mobile_telephone != "" && !validatePhone(mobile_telephone) && international_learner == "No" ) {
				$("#mobile_telephone_label").css('color', '#de5555');
				not_valid = true;
			}
			
			
			// ---------------------------- email address ----------------------------------
			var email_address = $.trim(document.getElementById('email_address').value);
			var confirm_email_address = $.trim(document.getElementById('confirm_email_address').value);
			

			if (email_address == "") {
				$("#email_address_label").css('color', '#de5555');
				not_valid = true;
			}				

			// make sure a valid email address has been entered.  using a regex function above
			if (validateEmail(email_address) == false) {
				$("#email_address_label").css('color', '#de5555');
				not_valid = true;
			}

			// see if emails are the same
			var email_check = check_email_identical();

			if (!email_check || confirm_email_address == "") {
				$("#confirm_email_address_label").css('color', '#de5555');
				not_valid = true;
			}
			
			
			// emergency_contact_name
			var emergency_contact_name = $.trim(document.getElementById('emergency_contact_name').value);
			
			if (emergency_contact_name == "") {
				$("#emergency_contact_name_label").css('color', '#de5555');
				not_valid = true;
			}			
			
			// emergency_contact_telephone
			var emergency_contact_telephone = $.trim(document.getElementById('emergency_contact_telephone').value);
			
			if (emergency_contact_telephone == "") {
				$("#emergency_contact_telephone_label").css('color', '#de5555');
				not_valid = true;
			}			

			if ( emergency_contact_telephone == mobile_telephone ) {
				
				$("#emergency_contact_telephone_label_2").show();
				$("#emergency_contact_telephone_label").css('color', '#de5555');

				not_valid = true;
			}	

			if (international_learner == "No" && !validatePhone(emergency_contact_telephone)) {
				$("#emergency_contact_telephone_label").css('color', '#de5555');
				not_valid = true;
			}
			
		
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#address_contact_details_heading").css('background-color', '#61bd61');
			} else {
				$("#address_contact_details_heading").css('background-color', '#de5555');
			}
			
			return not_valid;				
		}

		function check_finance() {

			var not_valid = false;
			$("#finance_heading").css('background-color', '#009aca');
			
			// check lsf needs validating?
			var apply_lsf = $('#financial_hardship').is(':checked');																				
				
			if (apply_lsf == true) {

				var fsm_last_year = $('input[name=fsm_last_year]:checked').val();

				if (fsm_last_year == "" || fsm_last_year == undefined) {
					$("#fsm_last_year_label").css('color', '#de5555');
					not_valid = true;
				}

				var loan_24_applied = $('input[name=loan_24_applied]:checked').val();		
				
				if (loan_24_applied == "" || loan_24_applied == undefined) {
					$("#loan_24_applied_label").css('color', '#de5555');
					not_valid = true;
				}

				var marital_status = document.getElementById('marital_status').value;			
				
				if (marital_status == "" || marital_status == undefined) {
					$("#marital_status_label").css('color', '#de5555');
					not_valid = true;
				}
				
				var specific_hardship = $.trim(document.getElementById('specific_hardship').value);		
				
				if (specific_hardship == "" || specific_hardship == undefined) {
					$("#specific_hardship_label").css('color', '#de5555');
					not_valid = true;
				}

				var buying_own_kit = $('input[name=buying_own_kit]:checked').val();		
				

				if (buying_own_kit == "" || buying_own_kit == undefined) {
					$("#buying_own_kit_label").css('color', '#de5555');
					not_valid = true;
				}
				
			}

			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#finance_heading").css('background-color', '#61bd61');
			} else {
				$("#finance_heading").css('background-color', '#de5555');
			}
			
			return not_valid;		
		}
		
		
		function check_marketing() {

			var not_valid = false;
			$("#marketing_heading").css('background-color', '#009aca');

			// encouraged apply
			var encouraged_apply = $('#encouraged_apply').val();
			if (encouraged_apply == "" ) {
				$("#encouraged_apply_label").css('color', '#de5555');
				not_valid = true;
			}			
			
			var consent_contact_surveys = $('#consent_contact_surveys').is(':checked');																	
			var consent_contact_courses = $('#consent_contact_courses').is(':checked');																	
			var consent_contact_marketing = $('#consent_contact_marketing').is(':checked');		
			
	
			// if no contact then lets set the methods to false
			if (consent_contact_surveys == false && consent_contact_courses == false && consent_contact_marketing == false) {
			
				$('#contact_post').prop('checked', false);
				$('#contact_phone').prop('checked', false);
				$('#contact_email').prop('checked', false);
		
			}
			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#marketing_heading").css('background-color', '#61bd61');
			} else {
				$("#marketing_heading").css('background-color', '#de5555');
			}
			
			return not_valid;					
		}

		function check_course() {

			var not_valid = false;
			$("#course_heading").css('background-color', '#009aca');

			// first choice course title and course code
			var course_1_chosen = $.trim(document.getElementById('course_1_chosen').value);

			if (course_1_chosen == "") {

				$("#course_1_select_label").css('color', '#de5555');
				has_errors = true;
			}		

			// second choice course
			var course_2_chosen = $.trim(document.getElementById('course_2_chosen').value);

			// third choice course
			var course_3_chosen = $.trim(document.getElementById('course_3_chosen').value);
			
			// get the titles for overloading into the db
			var course_1_title  = $.trim(document.getElementById('course_1_select').value);
			var course_2_title  = $.trim(document.getElementById('course_2_select').value);
			var course_3_title  = $.trim(document.getElementById('course_3_select').value);			
			
			if (course_1_title == "") {

				$("#course_1_select_label").css('color', '#de5555');
				not_valid = true;
			}					
			
			
			// applying_apprenticeship
			var applying_apprenticeship = $('input[name=applying_apprenticeship]:checked').val();
			if (applying_apprenticeship == "" || applying_apprenticeship == undefined) {
				$("#applying_apprenticeship_label").css('color', '#de5555');
				not_valid = true;
			}	

			// have_employer
			var have_employer = $('input[name=have_employer]:checked').val();
			if (have_employer == "" || have_employer == undefined) {
				$("#have_employer_label").css('color', '#de5555');
				not_valid = true;
			}				
			
			// set the css for the heading
			if (not_valid == false) {
				$("#course_heading").css('background-color', '#61bd61');
			} else {
				$("#course_heading").css('background-color', '#de5555');
			}
			
			return not_valid;				
		}

		
		function check_residency_nationality() {

			var not_valid = false;
			$("#residency_nationality_heading").css('background-color', '#009aca');

			// language (radio button english or other)
			var language = $('input[name=language]:checked').val();
			if (language == "" || language == undefined) {
				$("#language_label").css('color', '#de5555');
				not_valid = true;
			}					
			
			var other_language = $.trim(document.getElementById('other_language').value);

			if (language == "No" && other_language == "") {
				$("#other_language_label").css('color', '#de5555');
				not_valid = true;
			}

			// ---------------------------- nationality ----------------------------------
			var nationality = $.trim(document.getElementById('nationality').value);
			
			if (nationality == "") {
				$("#nationality_label").css('color', '#de5555');
				not_valid = true;
			}				
			
			// uk resident last 3 years
			var uk_last_three_years = $('input[name=uk_last_three_years]:checked').val();
		
			if (uk_last_three_years == "" || uk_last_three_years == undefined) {
				$("#uk_last_three_years_label").css('color', '#de5555');
				not_valid = true;
			}
		
			//date of entry UK
			var date_entry_uk_day = $.trim(document.getElementById('date_entry_uk_day').value);			
			var date_entry_uk_month = $.trim(document.getElementById('date_entry_uk_month').value);						
			var date_entry_uk_year = $.trim(document.getElementById('date_entry_uk_year').value);							

			var date_entry_uk = date_entry_uk_year + "/" + date_entry_uk_month + "/" + date_entry_uk_day;
		
		
			// study visa
			var student_visa = $('input[name=student_visa]:checked').val();

			// asylum seeker?
			var asylum_seeker = $('input[name=asylum_seeker]:checked').val();
			
			// refugee?
			var refugee = $('input[name=refugee]:checked').val();
			
			// lived before coming to UK
			var live_before_uk = $.trim(document.getElementById('live_before_uk').value);

			//student visa when start studies date
			var when_start_studies_day = $.trim(document.getElementById('when_start_studies_day').value);			
			var when_start_studies_month = $.trim(document.getElementById('when_start_studies_month').value);						
			var when_start_studies_year = $.trim(document.getElementById('when_start_studies_year').value);				

			var when_start_studies = when_start_studies_year + "/" + when_start_studies_month + "/" + when_start_studies_day;
			
			//british_citizenship etc.
			var british_citizen = $('input[name=british_citizen]:checked').val();							

			var indefinite_leave = $('#indefinite_leave').is(':checked');									
			var right_of_abode = $('#right_of_abode').is(':checked');									
			var limited_leave = $('#limited_leave').is(':checked');																		
			var any_other_visa = $('#any_other_visa').is(':checked');																											

			// humanitarian protection etc.
			var humanitarian_protection = $('#humanitarian_protection').is(':checked');													
			var discretionary_leave = $('#discretionary_leave').is(':checked');																	
			var exceptional_leave = $('#exceptional_leave').is(':checked');																	
			var no_limit_on_stay = $('#no_limit_on_stay').is(':checked');
			var other_asylum_reason	= $('#other_asylum_reason').is(':checked');	
			
			if (british_citizen == "" || british_citizen == undefined) {
				$("#british_citizen_label").css('color', '#de5555');
				not_valid = true;
			}				
			
			// if 'No' to uk resident 
			if (uk_last_three_years == "No") {
			
				if (date_entry_uk_day == "" || date_entry_uk_month == "" || date_entry_uk_year == "") {
					$("#date_entry_uk_label").css('color', '#de5555');
					not_valid = true;
				}

				if (live_before_uk == "") {
					$("#live_before_uk_label").css('color', '#de5555');
					not_valid = true;
				}

				if (student_visa == undefined) {
					$("#student_visa_label").css('color', '#de5555');
					not_valid = true;
				}					

				if (student_visa == "Yes") {
					
					if (when_start_studies_day == "" || when_start_studies_month == "" || when_start_studies_year == "") {
						$("#when_start_studies_label").css('color', '#de5555');
						not_valid = true;
					}
				}					

				if (asylum_seeker == undefined) {
					$("#asylum_seeker_label").css('color', '#de5555');
					not_valid = true;
				}					

		
				if (refugee == undefined) {
					$("#refugee_label").css('color', '#de5555');
					not_valid = true;
				}
		
			}

			
			// store this back into a hidden input so that serialize picks it up
			if (when_start_studies == "//") {when_start_studies = "1900/01/01";}
			document.getElementById('when_start_studies').value = when_start_studies;

			if (date_entry_uk == "//") {date_entry_uk = "1900/01/01";}
			document.getElementById('date_entry_uk').value = date_entry_uk;
			
			//otherwise fine

			// set the css for the heading
			if (not_valid == false) {
				$("#residency_nationality_heading").css('background-color', '#61bd61');
			} else {
				$("#residency_nationality_heading").css('background-color', '#de5555');
			}
			
			return not_valid;				
		}
		

		function do_save() {
		
			var has_errors = false;

			var has_errors_ps = check_previous_study();
			var has_errors_course = check_course();
			var has_errors_pd = check_personal_details();
			var has_errors_address = check_address_contact_details();
			var has_errors_emp = check_employment_status();
			var has_errors_res = check_residency_nationality();
			var has_errors_personal_statement = check_personal_statement();
			var has_errors_quals_currently_studying = check_quals_currently_studying();
			var has_errors_qa = check_quals_achieved();
			var has_errors_dis = check_disability();
			var has_errors_finance = check_finance();
			var has_errors_marketing = check_marketing();
			var has_errors_dec = check_declaration();			
		
			if (has_errors_ps || has_errors_course || has_errors_pd || has_errors_address || has_errors_emp || has_errors_res || has_errors_personal_statement || has_errors_quals_currently_studying || has_errors_qa || has_errors_finance || has_errors_marketing || has_errors_dec) {
				has_errors = true;
			}
	
			
			var post_string = "";
			var signature = $('#signature').jSignature("getData","base30");

		
			post_string = post_string + $("#form_previous_study").serialize() + "&";
			post_string = post_string + $("#form_course").serialize() + "&";
			post_string = post_string + $("#form_personal_details").serialize() + "&";				
			post_string = post_string + $("#form_address_contact_details").serialize() + "&";					
			post_string = post_string + $("#form_employment_status").serialize() + "&";																	
			post_string = post_string + $("#form_residency_details").serialize() + "&";									
			post_string = post_string + $("#form_personal_statement").serialize() + "&";									
			post_string = post_string + $("#form_quals_currently_studying").serialize() + "&";									
			post_string = post_string + $("#form_quals_achieved").serialize() + "&";		
			post_string = post_string + $("#form_disability").serialize() + "&";		
			post_string = post_string + $("#form_finance").serialize() + "&";		
			post_string = post_string + $("#form_marketing").serialize() + "&";		
			post_string = post_string + $("#form_declaration").serialize() + "&signature=" + signature;

			
			// build ajax 

			// tell browser not to cache
			$.ajaxSetup ({cache: false});			
			
			$.ajax({
				url : "save_application.php",
				type: "POST",
				data : post_string,
				beforeSend: function(){
									   $("#saveLoaderDiv").show();
										$("#save_button").hide();
									   },
				success: function(data, textStatus, jqXHR) {

					$("#saveLoaderDiv").hide();
					$("#save_button").show();
			
			
					if ($.trim(data) != "1") {


						alert("There was a problem with saving this application.  Please contact the system administrator:" + data);
						

						return false;
					} else {

						// a save occurred, so we update our global var
						save_occurred = true;
						
						alert("Saved");					
					}
					

				}, error: function (jqXHR, textStatus, errorThrown){
					// error, then do something
					return false;
				}
			});			
			
			// otherwise success and we return true
			return true;
		}

		function do_application_submit() {
		
			if (!save_occurred) {
				alert('Please save your form before submitting.  All sections must be completed.');
				return;
			}
		
			var has_errors_ps = check_previous_study();
			var has_errors_course = check_course();
			var has_errors_pd = check_personal_details();
			var has_errors_address = check_address_contact_details();
			var has_errors_emp = check_employment_status();
			var has_errors_res = check_residency_nationality();
			var has_errors_personal_statement = check_personal_statement();
			var has_errors_quals_currently_studying = check_quals_currently_studying();
			var has_errors_qa = check_quals_achieved();
			var has_errors_dis = check_disability();
			var has_errors_finance = check_finance();
			var has_errors_marketing = check_marketing();
			var has_errors_dec = check_declaration();			
		
			if (has_errors_ps || has_errors_course || has_errors_pd || has_errors_address || has_errors_emp || has_errors_res || has_errors_personal_statement || has_errors_quals_currently_studying || has_errors_qa || has_errors_dis || has_errors_finance || has_errors_marketing || has_errors_dec) {
				alert("Please complete all sections of the application form before submitting");
				return;
			}		
		
			if (confirm("Are you sure you wish to submit this application?")) {

				// force a final save
				if (do_save()) {
			
					// and submit
					location.replace("do_submit.php");
				}
			}
			
		}	
		

		function set_existing_values() {
		
			$('input:radio[name=studied_before]').val(['<?php echo($oe['studied_before']); ?>']);
			$('input:radio[name=choice_college]').val(['<?php echo($oe['choice_college']); ?>']);
			$('input:radio[name=enrolled_other_establishment]').val(['<?php echo($oe['enrolled_other_establishment']); ?>']);
			document.getElementById('last_school_college').value = "<?php echo($oe['last_school_college']); ?>";

			// now the courses
			document.getElementById('course_1_chosen').value = "<?php echo($oe['first_choice_uioid']); ?>";			
			document.getElementById('course_2_chosen').value = "<?php echo($oe['second_choice_uioid']); ?>";				
			document.getElementById('course_3_chosen').value = "<?php echo($oe['third_choice_uioid']); ?>";				
			
			document.getElementById('course_1_select').value = "<?php echo($oe['first_choice_title']); ?>";			
			document.getElementById('course_2_select').value = "<?php echo($oe['second_choice_title']); ?>";				
			document.getElementById('course_3_select').value = "<?php echo($oe['third_choice_title']); ?>";	
			
			$('input:radio[name=applying_apprenticeship]').val(['<?php echo($oe['applying_apprenticeship']); ?>']);
			$('input:radio[name=have_employer]').val(['<?php echo($oe['have_employer']); ?>']);

			document.getElementById('learner_title').value = "<?php echo($oe['learner_title']); ?>";

			document.getElementById('first_name').value = "<?php echo($oe['first_name']); ?>";
			document.getElementById('middle_name').value = "<?php echo($oe['middle_name']); ?>";			
			document.getElementById('last_name').value = "<?php echo($oe['last_name']); ?>";
			document.getElementById('previous_surname').value = "<?php echo($oe['previous_surname']); ?>";
			$('input:radio[name=learner_gender]').val(['<?php echo($oe['learner_gender']); ?>']);
			document.getElementById('gender_identity').value = "<?php echo($oe['gender_identity']); ?>";			
			document.getElementById('ethnicity').value = "<?php echo($oe['ethnicity']); ?>";
			document.getElementById('religion_belief').value = "<?php echo($oe['religion_belief']); ?>";

			// date of birth
			var dob = "<?php echo check_system_date($oe['date_of_birth']); ?>";			
		
		
			if ($.trim(dob) !== "" && dob !== "01-01-1970") {
		
				var dob_array = dob.split("-");
			
				// split and set selects

				document.getElementById("date_of_birth_day").value = dob_array[0];
				document.getElementById("date_of_birth_month").value  = dob_array[1];
				document.getElementById("date_of_birth_year").value = dob_array[2];				
			}

			document.getElementById('national_insurance_number').value = "<?php echo($oe['national_insurance_number']); ?>";
			
			$('input:radio[name=international_learner]').val(['<?php echo($oe['international_learner']); ?>']);
			
			document.getElementById('mobile_telephone').value = "<?php echo($oe['mobile_telephone']); ?>";
			document.getElementById('home_telephone').value = "<?php echo($oe['home_telephone']); ?>";
			document.getElementById('email_address').value = "<?php echo($oe['email_address']); ?>";			
			
			document.getElementById('emergency_contact_name').value = "<?php echo($oe['emergency_contact_name']); ?>";			
			document.getElementById('emergency_contact_telephone').value = "<?php echo($oe['emergency_contact_telephone']); ?>";			
			
			document.getElementById('permanent_address_line_1').value = "<?php echo($oe['permanent_address_line_1']); ?>";
			document.getElementById('permanent_address_line_2').value = "<?php echo($oe['permanent_address_line_2']); ?>";
			document.getElementById('permanent_town_city').value = "<?php echo($oe['permanent_town_city']); ?>";
			document.getElementById('permanent_county').value = "<?php echo($oe['permanent_county']); ?>";

			var full_perm_postcode = "<?php echo($oe['permanent_postcode']); ?>";
			
			if (full_perm_postcode !== "") {			
			
				// split it by space
				var postcode_array = full_perm_postcode.split(" ");
				
				document.getElementById('permanent_postcode_part_1').value = postcode_array[0];
				document.getElementById('permanent_postcode_part_2').value = postcode_array[1];
			}
			
			$('input:radio[name=living_independently]').val(['<?php echo($oe['living_independently']); ?>']);
			$('input:radio[name=in_care]').val(['<?php echo($oe['in_care']); ?>']);

			document.getElementById('termtime_address_line_1').value = "<?php echo($oe['termtime_address_line_1']); ?>";
			document.getElementById('termtime_address_line_2').value = "<?php echo($oe['termtime_address_line_2']); ?>";			
			document.getElementById('termtime_town_city').value = "<?php echo($oe['termtime_town_city']); ?>";
			document.getElementById('termtime_county').value = "<?php echo($oe['termtime_county']); ?>";

			var full_termtime_postcode = "<?php echo($oe['termtime_postcode']); ?>";
			
			if (full_termtime_postcode !== "") {			
			
				// split it by space
				var termtime_postcode_array = full_termtime_postcode.split(" ");
				
				document.getElementById('termtime_postcode_part_1').value = termtime_postcode_array[0];
				document.getElementById('termtime_postcode_part_2').value = termtime_postcode_array[1];
			}			

			// employment status
			document.getElementById('employment_status').value = "<?php echo($oe['employment_status']); ?>";

			// date employment started
			document.getElementById('date_employment_status_began').value = "";
			
			// set hidden 
			var date_employment_status_began = "<?php echo check_system_date($oe['date_employment_status_began']); ?>";
	
			// and date selector portions
			if ($.trim(date_employment_status_began) == "" || date_employment_status_began == "01-01-1970") {

				document.getElementById('date_employment_started_day').value = "";
				document.getElementById('date_employment_started_month').value = "";
				document.getElementById('date_employment_started_year').value = "";
			}	else {

				// split the date from the db
				var date_employment_started_array = date_employment_status_began.split("-");

				document.getElementById('date_employment_started_day').value = date_employment_started_array[0];
				document.getElementById('date_employment_started_month').value = date_employment_started_array[1];
				document.getElementById('date_employment_started_year').value = date_employment_started_array[2];
			}

			document.getElementById('hours_per_week_employed').value = "<?php echo $oe['hours_per_week_employed']; ?>";

			$('input:radio[name=self_employed]').val(['<?php echo($oe['self_employed']); ?>']);
			
			// set hidden for unemployment date
			var date_unemployment_status_began = "<?php echo check_system_date($oe['date_unemployment_status_began']); ?>";

			// and date selector portions
			if ($.trim(date_unemployment_status_began) == "" || date_unemployment_status_began == "01-01-1970") {

				document.getElementById('unemployment_started_day').value = "";
				document.getElementById('unemployment_started_month').value = "";
				document.getElementById('unemployment_started_year').value = "";
			}	else {

				// split the date from the db
				var date_unemployment_started_array = date_unemployment_status_began.split("-");

				document.getElementById('unemployment_started_day').value = date_unemployment_started_array[0];
				document.getElementById('unemployment_started_month').value = date_unemployment_started_array[1];
				document.getElementById('unemployment_started_year').value = date_unemployment_started_array[2];
			}

			document.getElementById('unemployment_length').value = "<?php echo $oe['unemployment_length']; ?>";
			
			var receipt_jsa = "<?php echo $oe['receipt_jsa']; ?>";
			if (receipt_jsa == "on") {$('#receipt_jsa').prop("checked","true");}
			
			var receipt_esa = "<?php echo $oe['receipt_esa']; ?>";
			if (receipt_esa == "on") {$('#receipt_esa').prop("checked","true");}

			var receipt_universal_credit = "<?php echo $oe['receipt_universal_credit']; ?>";
			if (receipt_universal_credit == "on") {$('#receipt_universal_credit').prop("checked","true");}
			
			document.getElementById('receipt_other_benefit').value = "<?php echo $oe['receipt_other_benefit']; ?>";
			
			// set hidden for retirement date
			var date_retirement_status_began = "<?php echo check_system_date($oe['date_retirement_status_began']); ?>";			
			
			// and date selector portions
			if ($.trim(date_retirement_status_began) == "" || date_retirement_status_began == "01-01-1970") {

				document.getElementById('retirement_started_day').value = "";
				document.getElementById('retirement_started_month').value = "";
				document.getElementById('retirement_started_year').value = "";
			}	else {

				// split the date from the db
				var date_retirement_started_array = date_retirement_status_began.split("-");

				document.getElementById('retirement_started_day').value = date_retirement_started_array[0];
				document.getElementById('retirement_started_month').value = date_retirement_started_array[1];
				document.getElementById('retirement_started_year').value = date_retirement_started_array[2];
			}
			
			document.getElementById('retirement_length').value = "<?php echo $oe['retirement_length']; ?>";

			// now show the relevant questions for empl status
			toggle_employment_status_questions();			
			
			// residency 

			// language and other
			$('input:radio[name=language]').val(['<?php echo($oe['language']); ?>']);
			document.getElementById('other_language').value = "<?php echo $oe['other_language']; ?>";
			toggle_language_questions();
			
			$('input:radio[name=british_citizen]').val(['<?php echo($oe['british_citizen']); ?>']);

			document.getElementById('passport_number').value = "<?php echo($oe['passport_number']); ?>";
			document.getElementById('nationality').value = "<?php echo($oe['nationality']); ?>";		
		
			$('input:radio[name=student_visa]').val(['<?php echo($oe['student_visa']); ?>']);
			$('input:radio[name=uk_last_three_years]').val(['<?php echo($oe['uk_last_three_years']); ?>']);
		
			var date_entry_uk = "<?php echo check_system_date($oe['date_entry_uk']); ?>";			
			
			// and date selector portions
			if ($.trim(date_entry_uk) == "" || date_entry_uk == "01-01-1970") {

				document.getElementById('date_entry_uk_day').value = "";
				document.getElementById('date_entry_uk_month').value = "";
				document.getElementById('date_entry_uk_year').value = "";
			}	else {

				// split the date from the db
				var date_entry_uk_array = date_entry_uk.split("-");

				document.getElementById('date_entry_uk_day').value = date_entry_uk_array[0];
				document.getElementById('date_entry_uk_month').value = date_entry_uk_array[1];
				document.getElementById('date_entry_uk_year').value = date_entry_uk_array[2];
			}			
			
			toggle_date_entry_questions();
			
			document.getElementById('live_before_uk').value = "<?php echo($oe['live_before_uk']); ?>";
	
			var indefinite_leave = "<?php echo $oe['indefinite_leave']; ?>";
			if (indefinite_leave == "on") {$('#indefinite_leave').prop("checked","true");}			

			var right_of_abode = "<?php echo $oe['right_of_abode']; ?>";
			if (right_of_abode == "on") {$('#right_of_abode').prop("checked","true");}					
	
			var limited_leave = "<?php echo $oe['limited_leave']; ?>";
			if (limited_leave == "on") {$('#limited_leave').prop("checked","true");}		
	
			var any_other_visa = "<?php echo $oe['any_other_visa']; ?>";
			if (any_other_visa == "on") {$('#any_other_visa').prop("checked","true");}		

			$('input:radio[name=student_visa]').val(['<?php echo($oe['student_visa']); ?>']);
			$('input:radio[name=asylum_seeker]').val(['<?php echo($oe['asylum_seeker']); ?>']);
			
			var when_start_studies = "<?php echo check_system_date($oe['when_start_studies']); ?>";			
			
			// and date selector portions
			if ($.trim(when_start_studies) == "" || when_start_studies == "01-01-1970") {

				document.getElementById('when_start_studies_day').value = "";
				document.getElementById('when_start_studies_month').value = "";
				document.getElementById('when_start_studies_year').value = "";

			}	else {

				// split the date from the db
				var when_start_studies_array = when_start_studies.split("-");

				document.getElementById('when_start_studies_day').value = when_start_studies_array[0];
				document.getElementById('when_start_studies_month').value = when_start_studies_array[1];
				document.getElementById('when_start_studies_year').value = when_start_studies_array[2];
			}			
			
			toggle_student_visa_questions();			
			

			$('input:radio[name=refugee]').val(['<?php echo($oe['refugee']); ?>']);
			$('input:radio[name=asylum_seeker]').val(['<?php echo($oe['asylum_seeker']); ?>']);

			var humanitarian_protection = "<?php echo $oe['humanitarian_protection']; ?>";
			if (humanitarian_protection == "on") {$('#humanitarian_protection').prop("checked","true");}

			var discretionary_leave = "<?php echo $oe['discretionary_leave']; ?>";
			if (discretionary_leave == "on") {$('#discretionary_leave').prop("checked","true");}			
			
			var exceptional_leave = "<?php echo $oe['exceptional_leave']; ?>";
			if (exceptional_leave == "on") {$('#exceptional_leave').prop("checked","true");}				

			var no_limit_on_stay = "<?php echo $oe['no_limit_on_stay']; ?>";
			if (no_limit_on_stay == "on") {$('#no_limit_on_stay').prop("checked","true");}	

			var other_asylum_reason = "<?php echo $oe['other_asylum_reason']; ?>";
			if (other_asylum_reason == "on") {$('#other_asylum_reason').prop("checked","true");}				
			
			toggle_asylum_questions();
			
			// personal statement done below directly into text area

			// quals currently studying
			$('input:radio[name=is_currently_studying]').val(['<?php echo($oe['is_currently_studying']); ?>']);

			toggle_quals_currently_studying_questions();
			
			
			document.getElementById('current_study_qual_1_examboard').value = "<?php echo($oe['current_study_qual_1_examboard']); ?>";
			document.getElementById('current_study_qual_1_subject').value = "<?php echo($oe['current_study_qual_1_subject']); ?>";
			document.getElementById('current_study_qual_1_level').value = "<?php echo($oe['current_study_qual_1_level']); ?>";			
			document.getElementById('current_study_qual_1_predicted_grade').value = "<?php echo($oe['current_study_qual_1_predicted_grade']); ?>";
			document.getElementById('current_study_qual_1_date_taken').value = "<?php echo($oe['current_study_qual_1_date_taken']); ?>";
			document.getElementById('current_study_qual_1_length').value = "<?php echo($oe['current_study_qual_1_length']); ?>";
			
			
			document.getElementById('current_study_qual_2_examboard').value = "<?php echo($oe['current_study_qual_2_examboard']); ?>";
			document.getElementById('current_study_qual_2_subject').value = "<?php echo($oe['current_study_qual_2_subject']); ?>";
			document.getElementById('current_study_qual_2_predicted_grade').value = "<?php echo($oe['current_study_qual_2_predicted_grade']); ?>";
			document.getElementById('current_study_qual_2_date_taken').value = "<?php echo($oe['current_study_qual_2_date_taken']); ?>";
			document.getElementById('current_study_qual_2_level').value = "<?php echo($oe['current_study_qual_2_level']); ?>";			
			document.getElementById('current_study_qual_2_length').value = "<?php echo($oe['current_study_qual_2_length']); ?>";

			document.getElementById('current_study_qual_3_examboard').value = "<?php echo($oe['current_study_qual_3_examboard']); ?>";
			document.getElementById('current_study_qual_3_subject').value = "<?php echo($oe['current_study_qual_3_subject']); ?>";
			document.getElementById('current_study_qual_3_predicted_grade').value = "<?php echo($oe['current_study_qual_3_predicted_grade']); ?>";
			document.getElementById('current_study_qual_3_date_taken').value = "<?php echo($oe['current_study_qual_3_date_taken']); ?>";
			document.getElementById('current_study_qual_3_level').value = "<?php echo($oe['current_study_qual_3_level']); ?>";			
			document.getElementById('current_study_qual_3_length').value = "<?php echo($oe['current_study_qual_3_length']); ?>";

			document.getElementById('current_study_qual_4_examboard').value = "<?php echo($oe['current_study_qual_4_examboard']); ?>";
			document.getElementById('current_study_qual_4_subject').value = "<?php echo($oe['current_study_qual_4_subject']); ?>";
			document.getElementById('current_study_qual_4_predicted_grade').value = "<?php echo($oe['current_study_qual_4_predicted_grade']); ?>";
			document.getElementById('current_study_qual_4_date_taken').value = "<?php echo($oe['current_study_qual_4_date_taken']); ?>";
			document.getElementById('current_study_qual_4_level').value = "<?php echo($oe['current_study_qual_4_level']); ?>";			
			document.getElementById('current_study_qual_4_length').value = "<?php echo($oe['current_study_qual_4_length']); ?>";
			
			document.getElementById('current_study_qual_5_examboard').value = "<?php echo($oe['current_study_qual_5_examboard']); ?>";
			document.getElementById('current_study_qual_5_subject').value = "<?php echo($oe['current_study_qual_5_subject']); ?>";
			document.getElementById('current_study_qual_5_predicted_grade').value = "<?php echo($oe['current_study_qual_5_predicted_grade']); ?>";
			document.getElementById('current_study_qual_5_date_taken').value = "<?php echo($oe['current_study_qual_5_date_taken']); ?>";
			document.getElementById('current_study_qual_5_level').value = "<?php echo($oe['current_study_qual_5_level']); ?>";			
			document.getElementById('current_study_qual_5_length').value = "<?php echo($oe['current_study_qual_5_length']); ?>";
			
			document.getElementById('current_study_qual_6_examboard').value = "<?php echo($oe['current_study_qual_6_examboard']); ?>";
			document.getElementById('current_study_qual_6_subject').value = "<?php echo($oe['current_study_qual_6_subject']); ?>";
			document.getElementById('current_study_qual_6_predicted_grade').value = "<?php echo($oe['current_study_qual_6_predicted_grade']); ?>";
			document.getElementById('current_study_qual_6_date_taken').value = "<?php echo($oe['current_study_qual_6_date_taken']); ?>";
			document.getElementById('current_study_qual_6_level').value = "<?php echo($oe['current_study_qual_6_level']); ?>";			
			document.getElementById('current_study_qual_6_length').value = "<?php echo($oe['current_study_qual_6_length']); ?>";
			
			document.getElementById('current_study_qual_7_examboard').value = "<?php echo($oe['current_study_qual_7_examboard']); ?>";
			document.getElementById('current_study_qual_7_subject').value = "<?php echo($oe['current_study_qual_7_subject']); ?>";
			document.getElementById('current_study_qual_7_predicted_grade').value = "<?php echo($oe['current_study_qual_7_predicted_grade']); ?>";
			document.getElementById('current_study_qual_7_date_taken').value = "<?php echo($oe['current_study_qual_7_date_taken']); ?>";
			document.getElementById('current_study_qual_7_level').value = "<?php echo($oe['current_study_qual_7_level']); ?>";			
			document.getElementById('current_study_qual_7_length').value = "<?php echo($oe['current_study_qual_7_length']); ?>";

			document.getElementById('current_study_qual_8_examboard').value = "<?php echo($oe['current_study_qual_8_examboard']); ?>";
			document.getElementById('current_study_qual_8_subject').value = "<?php echo($oe['current_study_qual_8_subject']); ?>";
			document.getElementById('current_study_qual_8_predicted_grade').value = "<?php echo($oe['current_study_qual_8_predicted_grade']); ?>";
			document.getElementById('current_study_qual_8_date_taken').value = "<?php echo($oe['current_study_qual_8_date_taken']); ?>";
			document.getElementById('current_study_qual_8_level').value = "<?php echo($oe['current_study_qual_8_level']); ?>";			
			document.getElementById('current_study_qual_8_length').value = "<?php echo($oe['current_study_qual_8_length']); ?>";
			
			document.getElementById('current_study_qual_9_examboard').value = "<?php echo($oe['current_study_qual_9_examboard']); ?>";
			document.getElementById('current_study_qual_9_subject').value = "<?php echo($oe['current_study_qual_9_subject']); ?>";
			document.getElementById('current_study_qual_9_predicted_grade').value = "<?php echo($oe['current_study_qual_9_predicted_grade']); ?>";
			document.getElementById('current_study_qual_9_date_taken').value = "<?php echo($oe['current_study_qual_9_date_taken']); ?>";
			document.getElementById('current_study_qual_9_level').value = "<?php echo($oe['current_study_qual_9_level']); ?>";			
			document.getElementById('current_study_qual_9_length').value = "<?php echo($oe['current_study_qual_9_length']); ?>";
			
			document.getElementById('current_study_qual_10_examboard').value = "<?php echo($oe['current_study_qual_10_examboard']); ?>";
			document.getElementById('current_study_qual_10_subject').value = "<?php echo($oe['current_study_qual_10_subject']); ?>";
			document.getElementById('current_study_qual_10_predicted_grade').value = "<?php echo($oe['current_study_qual_10_predicted_grade']); ?>";
			document.getElementById('current_study_qual_10_date_taken').value = "<?php echo($oe['current_study_qual_10_date_taken']); ?>";
			document.getElementById('current_study_qual_10_level').value = "<?php echo($oe['current_study_qual_10_level']); ?>";			
			document.getElementById('current_study_qual_10_length').value = "<?php echo($oe['current_study_qual_10_length']); ?>";
			
			document.getElementById('current_study_qual_11_examboard').value = "<?php echo($oe['current_study_qual_11_examboard']); ?>";
			document.getElementById('current_study_qual_11_subject').value = "<?php echo($oe['current_study_qual_11_subject']); ?>";
			document.getElementById('current_study_qual_11_predicted_grade').value = "<?php echo($oe['current_study_qual_11_predicted_grade']); ?>";
			document.getElementById('current_study_qual_11_date_taken').value = "<?php echo($oe['current_study_qual_11_date_taken']); ?>";
			document.getElementById('current_study_qual_11_level').value = "<?php echo($oe['current_study_qual_11_level']); ?>";			
			document.getElementById('current_study_qual_11_length').value = "<?php echo($oe['current_study_qual_11_length']); ?>";
			
			document.getElementById('current_study_qual_12_examboard').value = "<?php echo($oe['current_study_qual_12_examboard']); ?>";
			document.getElementById('current_study_qual_12_subject').value = "<?php echo($oe['current_study_qual_12_subject']); ?>";
			document.getElementById('current_study_qual_12_predicted_grade').value = "<?php echo($oe['current_study_qual_12_predicted_grade']); ?>";
			document.getElementById('current_study_qual_12_date_taken').value = "<?php echo($oe['current_study_qual_12_date_taken']); ?>";
			document.getElementById('current_study_qual_12_level').value = "<?php echo($oe['current_study_qual_12_level']); ?>";			
			document.getElementById('current_study_qual_12_length').value = "<?php echo($oe['current_study_qual_12_length']); ?>";

			// previous study
			$('input:radio[name=any_previous_quals]').val(['<?php echo($oe['any_previous_quals']); ?>']);
			$('input:radio[name=highest_qual]').val(['<?php echo($oe['highest_qual']); ?>']);
			
			toggle_quals_achieved_questions();
			
			document.getElementById('previous_study_qual_1_examboard').value = "<?php echo($oe['previous_study_qual_1_examboard']); ?>";
			document.getElementById('previous_study_qual_1_subject').value = "<?php echo($oe['previous_study_qual_1_subject']); ?>";
			document.getElementById('previous_study_qual_1_level').value = "<?php echo($oe['previous_study_qual_1_level']); ?>";			
			document.getElementById('previous_study_qual_1_predicted_grade').value = "<?php echo($oe['previous_study_qual_1_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_1_date_taken').value = "<?php echo($oe['previous_study_qual_1_date_taken']); ?>";
			document.getElementById('previous_study_qual_1_length').value = "<?php echo($oe['previous_study_qual_1_length']); ?>";
			
			
			document.getElementById('previous_study_qual_2_examboard').value = "<?php echo($oe['previous_study_qual_2_examboard']); ?>";
			document.getElementById('previous_study_qual_2_subject').value = "<?php echo($oe['previous_study_qual_2_subject']); ?>";
			document.getElementById('previous_study_qual_2_predicted_grade').value = "<?php echo($oe['previous_study_qual_2_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_2_date_taken').value = "<?php echo($oe['previous_study_qual_2_date_taken']); ?>";
			document.getElementById('previous_study_qual_2_level').value = "<?php echo($oe['previous_study_qual_2_level']); ?>";			
			document.getElementById('previous_study_qual_2_length').value = "<?php echo($oe['previous_study_qual_2_length']); ?>";

			document.getElementById('previous_study_qual_3_examboard').value = "<?php echo($oe['previous_study_qual_3_examboard']); ?>";
			document.getElementById('previous_study_qual_3_subject').value = "<?php echo($oe['previous_study_qual_3_subject']); ?>";
			document.getElementById('previous_study_qual_3_predicted_grade').value = "<?php echo($oe['previous_study_qual_3_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_3_date_taken').value = "<?php echo($oe['previous_study_qual_3_date_taken']); ?>";
			document.getElementById('previous_study_qual_3_level').value = "<?php echo($oe['previous_study_qual_3_level']); ?>";			
			document.getElementById('previous_study_qual_3_length').value = "<?php echo($oe['previous_study_qual_3_length']); ?>";

			document.getElementById('previous_study_qual_4_examboard').value = "<?php echo($oe['previous_study_qual_4_examboard']); ?>";
			document.getElementById('previous_study_qual_4_subject').value = "<?php echo($oe['previous_study_qual_4_subject']); ?>";
			document.getElementById('previous_study_qual_4_predicted_grade').value = "<?php echo($oe['previous_study_qual_4_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_4_date_taken').value = "<?php echo($oe['previous_study_qual_4_date_taken']); ?>";
			document.getElementById('previous_study_qual_4_level').value = "<?php echo($oe['previous_study_qual_4_level']); ?>";			
			document.getElementById('previous_study_qual_4_length').value = "<?php echo($oe['previous_study_qual_4_length']); ?>";
			
			document.getElementById('previous_study_qual_5_examboard').value = "<?php echo($oe['previous_study_qual_5_examboard']); ?>";
			document.getElementById('previous_study_qual_5_subject').value = "<?php echo($oe['previous_study_qual_5_subject']); ?>";
			document.getElementById('previous_study_qual_5_predicted_grade').value = "<?php echo($oe['previous_study_qual_5_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_5_date_taken').value = "<?php echo($oe['previous_study_qual_5_date_taken']); ?>";
			document.getElementById('previous_study_qual_5_level').value = "<?php echo($oe['previous_study_qual_5_level']); ?>";			
			document.getElementById('previous_study_qual_5_length').value = "<?php echo($oe['previous_study_qual_5_length']); ?>";
			
			document.getElementById('previous_study_qual_6_examboard').value = "<?php echo($oe['previous_study_qual_6_examboard']); ?>";
			document.getElementById('previous_study_qual_6_subject').value = "<?php echo($oe['previous_study_qual_6_subject']); ?>";
			document.getElementById('previous_study_qual_6_predicted_grade').value = "<?php echo($oe['previous_study_qual_6_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_6_date_taken').value = "<?php echo($oe['previous_study_qual_6_date_taken']); ?>";
			document.getElementById('previous_study_qual_6_level').value = "<?php echo($oe['previous_study_qual_6_level']); ?>";			
			document.getElementById('previous_study_qual_6_length').value = "<?php echo($oe['previous_study_qual_6_length']); ?>";
			
			document.getElementById('previous_study_qual_7_examboard').value = "<?php echo($oe['previous_study_qual_7_examboard']); ?>";
			document.getElementById('previous_study_qual_7_subject').value = "<?php echo($oe['previous_study_qual_7_subject']); ?>";
			document.getElementById('previous_study_qual_7_predicted_grade').value = "<?php echo($oe['previous_study_qual_7_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_7_date_taken').value = "<?php echo($oe['previous_study_qual_7_date_taken']); ?>";
			document.getElementById('previous_study_qual_7_level').value = "<?php echo($oe['previous_study_qual_7_level']); ?>";			
			document.getElementById('previous_study_qual_7_length').value = "<?php echo($oe['previous_study_qual_7_length']); ?>";

			document.getElementById('previous_study_qual_8_examboard').value = "<?php echo($oe['previous_study_qual_8_examboard']); ?>";
			document.getElementById('previous_study_qual_8_subject').value = "<?php echo($oe['previous_study_qual_8_subject']); ?>";
			document.getElementById('previous_study_qual_8_predicted_grade').value = "<?php echo($oe['previous_study_qual_8_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_8_date_taken').value = "<?php echo($oe['previous_study_qual_8_date_taken']); ?>";
			document.getElementById('previous_study_qual_8_level').value = "<?php echo($oe['previous_study_qual_8_level']); ?>";			
			document.getElementById('previous_study_qual_8_length').value = "<?php echo($oe['previous_study_qual_8_length']); ?>";
			
			document.getElementById('previous_study_qual_9_examboard').value = "<?php echo($oe['previous_study_qual_9_examboard']); ?>";
			document.getElementById('previous_study_qual_9_subject').value = "<?php echo($oe['previous_study_qual_9_subject']); ?>";
			document.getElementById('previous_study_qual_9_predicted_grade').value = "<?php echo($oe['previous_study_qual_9_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_9_date_taken').value = "<?php echo($oe['previous_study_qual_9_date_taken']); ?>";
			document.getElementById('previous_study_qual_9_level').value = "<?php echo($oe['previous_study_qual_9_level']); ?>";			
			document.getElementById('previous_study_qual_9_length').value = "<?php echo($oe['previous_study_qual_9_length']); ?>";
			
			document.getElementById('previous_study_qual_10_examboard').value = "<?php echo($oe['previous_study_qual_10_examboard']); ?>";
			document.getElementById('previous_study_qual_10_subject').value = "<?php echo($oe['previous_study_qual_10_subject']); ?>";
			document.getElementById('previous_study_qual_10_predicted_grade').value = "<?php echo($oe['previous_study_qual_10_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_10_date_taken').value = "<?php echo($oe['previous_study_qual_10_date_taken']); ?>";
			document.getElementById('previous_study_qual_10_level').value = "<?php echo($oe['previous_study_qual_10_level']); ?>";			
			document.getElementById('previous_study_qual_10_length').value = "<?php echo($oe['previous_study_qual_10_length']); ?>";
			
			document.getElementById('previous_study_qual_11_examboard').value = "<?php echo($oe['previous_study_qual_11_examboard']); ?>";
			document.getElementById('previous_study_qual_11_subject').value = "<?php echo($oe['previous_study_qual_11_subject']); ?>";
			document.getElementById('previous_study_qual_11_predicted_grade').value = "<?php echo($oe['previous_study_qual_11_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_11_date_taken').value = "<?php echo($oe['previous_study_qual_11_date_taken']); ?>";
			document.getElementById('previous_study_qual_11_level').value = "<?php echo($oe['previous_study_qual_11_level']); ?>";			
			document.getElementById('previous_study_qual_11_length').value = "<?php echo($oe['previous_study_qual_11_length']); ?>";
			
			document.getElementById('previous_study_qual_12_examboard').value = "<?php echo($oe['previous_study_qual_12_examboard']); ?>";
			document.getElementById('previous_study_qual_12_subject').value = "<?php echo($oe['previous_study_qual_12_subject']); ?>";
			document.getElementById('previous_study_qual_12_predicted_grade').value = "<?php echo($oe['previous_study_qual_12_predicted_grade']); ?>";
			document.getElementById('previous_study_qual_12_date_taken').value = "<?php echo($oe['previous_study_qual_12_date_taken']); ?>";
			document.getElementById('previous_study_qual_12_level').value = "<?php echo($oe['previous_study_qual_12_level']); ?>";			
			document.getElementById('previous_study_qual_12_length').value = "<?php echo($oe['previous_study_qual_12_length']); ?>";
			
			//disability
			$('input:radio[name=consider_disability_difficulty]').val(['<?php echo($oe['consider_disability_difficulty']); ?>']);
			toggle_disability_questions();
			
			document.getElementById('disability').value = "<?php echo($oe['disability']); ?>";
			document.getElementById('learning_difficulty').value = "<?php echo($oe['learning_difficulty']); ?>";
			$('input:radio[name=special_arrangement_exams]').val(['<?php echo($oe['special_arrangement_exams']); ?>']);
			$('input:radio[name=support_at_interview]').val(['<?php echo($oe['support_at_interview']); ?>']);			

			var extra_support = "<?php echo($oe['extra_support']); ?>";
			
			if (extra_support == "1") {
				$('input:radio[name=extra_support_reading_writing]').val(['Yes']);					
				$('input:radio[name=extra_support_numeracy]').val(['No']);		
			} else if (extra_support == "2") {
				$('input:radio[name=extra_support_reading_writing]').val(['Yes']);					
				$('input:radio[name=extra_support_numeracy]').val(['Yes']);		
			} else if (extra_support == "3") {
				$('input:radio[name=extra_support_reading_writing]').val(['No']);					
				$('input:radio[name=extra_support_numeracy]').val(['Yes']);		
			} else if (extra_support == "4") {
				$('input:radio[name=extra_support_reading_writing]').val(['No']);					
				$('input:radio[name=extra_support_numeracy]').val(['No']);		
			}			
			
			$('input:radio[name=statement_of_needs]').val(['<?php echo($oe['statement_of_needs']); ?>']);						

			// finance
			var financial_hardship = "<?php echo $oe['financial_hardship']; ?>";
			if (financial_hardship == "on") {$('#financial_hardship').prop("checked","true");}

			//lsf apply questions

			var fsm_last_year = "<?php echo $oe['fsm_1415']; ?>";
			$('input:radio[name=fsm_last_year]').val([fsm_last_year]);					
			
			var loan_24_applied = "<?php echo $oe['loan_24_applied']; ?>";
			$('input:radio[name=loan_24_applied]').val([loan_24_applied]);					

			document.getElementById('marital_status').value = "<?php echo($oe['marital_status']); ?>";

			// specific hardship in textarea
			
			// household
			$('#household_info_name_1').val('<?php echo  str_replace("'", "", $oe['household_info_name_1']); ?>');
			$('#household_info_name_2').val('<?php echo  str_replace("'", "", $oe['household_info_name_2']); ?>');
			$('#household_info_name_3').val('<?php echo  str_replace("'", "", $oe['household_info_name_3']); ?>');
			$('#household_info_name_4').val('<?php echo  str_replace("'", "", $oe['household_info_name_4']); ?>');
			$('#household_info_name_5').val('<?php echo  str_replace("'", "", $oe['household_info_name_5']); ?>');

			$('#household_info_relationship_1').val('<?php echo  str_replace("'", "", $oe['household_info_relationship_1']); ?>');
			$('#household_info_relationship_2').val('<?php echo  str_replace("'", "", $oe['household_info_relationship_2']); ?>');
			$('#household_info_relationship_3').val('<?php echo  str_replace("'", "", $oe['household_info_relationship_3']); ?>');
			$('#household_info_relationship_4').val('<?php echo  str_replace("'", "", $oe['household_info_relationship_4']); ?>');
			$('#household_info_relationship_5').val('<?php echo  str_replace("'", "", $oe['household_info_relationship_5']); ?>');
			
			$('#household_age_1').val('<?php echo  str_replace("'", "", $oe['household_age_1']); ?>');
			$('#household_age_2').val('<?php echo  str_replace("'", "", $oe['household_age_2']); ?>');
			$('#household_age_3').val('<?php echo  str_replace("'", "", $oe['household_age_3']); ?>');
			$('#household_age_4').val('<?php echo  str_replace("'", "", $oe['household_age_4']); ?>');
			$('#household_age_5').val('<?php echo  str_replace("'", "", $oe['household_age_5']); ?>');

		
			// lsf require help with checkboxes

			var childcare = "<?php echo $oe['childcare']; ?>";
			if (childcare == "on") {$('#childcare').prop("checked","true");}			
			
			var essential_kit = "<?php echo $oe['essential_kit']; ?>";
			if (essential_kit == "on") {$('#essential_kit').prop("checked","true");}			
			
			var material_fees = "<?php echo $oe['material_fees']; ?>";
			if (material_fees == "on") {$('#material_fees').prop("checked","true");}			
			
			$('#travel_college').val('<?php echo($oe['travel_college']); ?>');
			$('#travel_college_other').val('<?php echo($oe['travel_college_other']); ?>');				

			toggle_travel_college();
			
			var buying_own_kit = "<?php echo $oe['buying_own_kit']; ?>";
			$('input:radio[name=buying_own_kit]').val([buying_own_kit]);	

		
			toggle_lsf_apply();
			
			var paying_own_course_fees = "<?php echo $oe['paying_own_course_fees']; ?>";
			if (paying_own_course_fees == "on") {$('#paying_own_course_fees').prop("checked","true");}			
			
			var employer_course_fees = "<?php echo $oe['employer_course_fees']; ?>";
			if (employer_course_fees == "on") {$('#employer_course_fees').prop("checked","true");}			

			var loan_24_course_fees = "<?php echo $oe['loan_24_course_fees']; ?>";
			if (loan_24_course_fees == "on") {$('#loan_24_course_fees').prop("checked","true");}			
			
			// marketing
			document.getElementById('encouraged_apply').value = "<?php echo($oe['encouraged_apply']); ?>";

			var consent_contact_marketing = "<?php echo $oe['consent_contact_marketing']; ?>";
			if (consent_contact_marketing == "on") {$('#consent_contact_marketing').prop("checked","true");}				

			var consent_contact_courses = "<?php echo $oe['consent_contact_courses']; ?>";
			if (consent_contact_courses == "on") {$('#consent_contact_courses').prop("checked","true");}	
			
			var consent_contact_surveys = "<?php echo $oe['consent_contact_surveys']; ?>";
			if (consent_contact_surveys == "on") {$('#consent_contact_surveys').prop("checked","true");}	

			var contact_phone = "<?php echo $oe['contact_phone']; ?>";
			if (contact_phone == "on") {$('#contact_phone').prop("checked","true");}				

			var contact_post = "<?php echo $oe['contact_post']; ?>";
			if (contact_post == "on") {$('#contact_post').prop("checked","true");}	

			var contact_email = "<?php echo $oe['contact_email']; ?>";
			if (contact_email == "on") {$('#contact_email').prop("checked","true");}	
			
			toggle_marketing_questions();
			
			$('input:radio[name=criminal_convictions]').val(['<?php echo($oe['criminal_convictions']); ?>']);
			
			var accept_terms_conditions = "<?php echo $oe['accept_terms_conditions']; ?>";
			if (accept_terms_conditions == "on") {$('#accept_terms_conditions').prop("checked","true");}	


			
			var datapair = $.trim("<?php echo $oe['signature']; ?>");
			if ( ( datapair !== "") && ( datapair !== "image/jsignature;base30,") ) {
				$('#signature').jSignature("setData", "data:" + datapair.split(","));
			}
			
			// set our heading colours back if this is NOT a resume
			var is_resume = "<?php echo $is_resume;?>";

		
			if (is_resume == "Yes") {
		
				check_previous_study();
				check_course();
				check_personal_details();
				check_address_contact_details();
				check_employment_status();
				check_residency_nationality();
				check_personal_statement();
				check_quals_currently_studying();
				check_quals_achieved();
				check_disability();
				check_finance();
				check_marketing();
				check_declaration();								
			}
				
			// hide tabs
			close_all();
		}

		function send_link() {
		
			if (!confirm("We can send you an email and text to allow you to login later and continue this application.  Would you like to continue later?")) {
				return;
			}
		
			// first make sure mobile number and email has been entered and a save has occurred

			// email address, check valid
			var email_address = $.trim(document.getElementById('email_address').value);			
			var mobile = $.trim(document.getElementById('mobile_telephone').value);			
			
			
			if (save_occurred && email_address != "" && validateEmail(email_address) && validatePhone(mobile) && check_email_identical() ) {
			
			}	else {

				alert("Please provide a valid email address and mobile number above, and Save before trying again.  We will text you a 5-digit code together with an email so you may continue later.");
				return;
			}
				
			// if validation fine, ajax the page that sends an email
			// validation all done.
			// lets build a post string to use in our xmlhttp object
			var post_string = "email_address=" + email_address + "&mobile=" + mobile;
				
			// tell browser not to cache
			$.ajaxSetup ({cache: false});			
			
			$.ajax({
				url : "send_link_to_user.php",
				type: "POST",
				data : post_string,
				beforeSend: function(){
									   $("#loaderDiv").show();
										$("#send_email_link_button").hide();
									   },
				success: function(data, textStatus, jqXHR) {
				
					$("#loaderDiv").hide();
					$("#send_email_link_button").show();
										
					if ($.trim(data) == "Success") {
						alert("A link has been sent to " + email_address);
					} else {
						alert(data);					
					}
					

				}, error: function (jqXHR, textStatus, errorThrown){
					// error, then do something
				}
			});			


		}

		
		function check_email_identical() {
		
			// get the value of the original email address
			var email_address = $.trim(document.getElementById('email_address').value);		
			var confirm_email_address = $.trim(document.getElementById('confirm_email_address').value);		

			
			// hide error
			$("#confirm_email_address_error").hide();			
			
			if (email_address != confirm_email_address) {

				$("#confirm_email_address_error").css('color', 'red');
				$("#confirm_email_address_error").text('email addresses do not match');
				$("#confirm_email_address_error").show();
				
				return false;
				
			} else {

				if (confirm_email_address == "") {
					return false;
				}			
			
				$("#confirm_email_address_error").css('color', '#5cb85c');
				$("#confirm_email_address_error").text('email addresses match');
				$("#confirm_email_address_error").show();
				
			}

			
			return true;
			
		}
		
	</script>	

</head>

<body onload="set_existing_values();">

	<div class="container">

		<div style="height:20px;">
		</div>

		<img style="float:right;margin-top:10px;margin-right:20px;" src="images/lc_logo_header.jpg">		
	
		<h1><font color="#003c78">Full and Part Time Application Form</font></h1>

		<p class="curved_grey_div">
			Thank you for considering applying to Leicester College.  Please complete all sections below.  The form
			can only be submitted once all sections have been completed.
			<br />
			<br />
			<strong>Do not close or refresh your browser or your information may be lost</strong>.
		</p>

		<p>
			If you would like to continue your application later, make sure you Save, and then use the button at the bottom of the form to send yourself a link.	
		</p>
		
		<div style="height:15px;">
		</div>		
		
		<div>
			<input type="button" class="btn btn-primary" onClick="open_all();" value="open all"> 
			<input type="button" class="btn btn-primary" onClick="close_all();" value="close all"> 
			<input style="float:right;" type="button" class="btn btn-primary" onClick="do_logout();" value="Logout"> 
		</div>
		
		<div style="height:25px;">
		</div>		
			  
		<div class="panel-group" id="accordion">

			<!-- -------------------------------------------------------------- -->
			<!-- Previous study start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
						<h4 class="panel-title" id="previous_study_heading">
							Previous Study Details
						</h4>						
					</a>
				</div>
				<div id="collapseOne" class="panel-collapse collapse in">
					<div class="panel-body">

						<form name="form_previous_study" id="form_previous_study" role="form" class="form-horizontal">

							<div class="form-group" >
								<label class="control-label col-sm-5" for="uln" name="uln_label" id="uln_label">Unique Learner Number <font style="font-weight:normal;">(leave this blank if you do not know your ULN) </font></label>
								
								<div class="col-sm-7" style="margin-top:5px;">
									<input type="text" name="uln" id="uln" maxlength="10" value="" onFocus="reset_error_class('uln_label');">
								</div>
							</div>


							<div class="form-group" >
								<label class="control-label col-sm-5" for="studied_before" name="studied_before_label" id="studied_before_label">Have you studied at Leicester College before?</label>
								<div class="col-sm-7" style="margin-top:5px;">
									Yes <input name="studied_before" id="studied_before" type="radio" value="Yes" onClick="reset_error_class('studied_before_label');">
									No <input name="studied_before" id="studied_before" type="radio" value="No" onClick="reset_error_class('studied_before_label');">
								</div>
							</div>

							<div class="form-group" >
								<label class="control-label col-sm-5" for="choice_college" name="choice_college_label" id="choice_college_label">Is Leicester College your First, Second or Third choice College?</label>
								<div class="col-sm-7" style="margin-top:5px;">
									First <input name="choice_college" id="choice_college" type="radio" value="First" onclick="reset_error_class('choice_college_label');">
									Second <input name="choice_college" id="choice_college" type="radio" value="Second" onclick="reset_error_class('choice_college_label');">								
									Third <input name="choice_college" id="choice_college" type="radio" value="Third" onclick="reset_error_class('choice_college_label');">								
								</div>
							</div>
							
							<div class="form-group">
								<label class="control-label col-sm-5" name="enrolled_other_establishment_label" id="enrolled_other_establishment_label" for="enrolled_other_establishment">Have you enrolled at another educational establishment in this academic year?</label>
								<div class="col-sm-7" style="margin-top:5px;">
									Yes <input name="enrolled_other_establishment" id="enrolled_other_establishment" type="radio" value="Yes" onClick="reset_error_class('enrolled_other_establishment_label');">
									No <input name="enrolled_other_establishment" id="enrolled_other_establishment" type="radio" value="No" onClick="reset_error_class('enrolled_other_establishment_label');">
								</div>					
							</div>

							<div class="form-group">
								<label class="control-label col-sm-5" name="last_school_college_label" id="last_school_college_label" for="last_school_college">Please give the name of the last school or college you attended or are still attending</label>
								<div class="col-sm-7" style="margin-top:5px;">
									<select name="last_school_college" id="last_school_college" onChange="reset_error_class('last_school_college_label');">
										<option value=""></option>
										<option value="00999 - Other">Other (my school isn't listed)</option>
										<option value="00193 - Aabenraa Business College">Aabenraa Business College</option>
										<option value="00380 - Abbey V.S">Abbey V.S</option>
										<option value="00439 - Abingdon High School">Abingdon High School</option>
										<option value="00219 - Accrington & Rossendale College">Accrington & Rossendale College</option>
										<option value="00352 - Acle High School">Acle High School</option>
										<option value="00259 - Adult Education Centre">Adult Education Centre</option>
										<option value="00001 - Alderman Newton's School">Alderman Newton's School</option>
										<option value="00214 - Amersham & Wycombe College">Amersham & Wycombe College</option>
										<option value="00383 - Andrew Marvel High School">Andrew Marvel High School</option>
										<option value="00109 - Anstey Martin High School">Anstey Martin High School</option>
										<option value="00143 - Arya Boys Secondary School">Arya Boys Secondary School</option>
										<option value="00098 - Ash Field School">Ash Field School</option>
										<option value="00145 - Ashby School">Ashby School</option>
										<option value="00355 - Ashlawn School">Ashlawn School</option>
										<option value="01009 - Ashmount School">Ashmount School</option>
										<option value="00482 - Aston Comprehensive School">Aston Comprehensive School</option>
										<option value="00487 - Aston Secondary School">Aston Secondary School</option>
										<option value="00125 - Avalon Training Centre">Avalon Training Centre</option>
										<option value="00003 - Babington Community College">Babington Community College</option>
										<option value="00199 - Barnfield College">Barnfield College</option>
										<option value="00262 - Baroda High School">Baroda High School</option>
										<option value="00347 - Barrs Hill School & CC">Barrs Hill School & CC</option>
										<option value="00057 - Bath Technical College">Bath Technical College</option>
										<option value="00004 - Beauchamp Community College">Beauchamp Community College</option>
										<option value="00021 - Beaumont Leys School">Beaumont Leys School</option>
										<option value="00494 - Beechwood School">Beechwood School</option>
										<option value="00455 - Bexhill College">Bexhill College</option>
										<option value="00525 - Bilborough College">Bilborough College</option>
										<option value="00353 - Bilton High School">Bilton High School</option>
										<option value="00178 - Bingham Toothill Comprehensive">Bingham Toothill Comprehensive</option>
										<option value="00559 - Birkbeck">Birkbeck</option>
										<option value="01010 - Birkett House">Birkett House</option>
										<option value="00221 - Bishop Stodford School">Bishop Stodford School</option>
										<option value="00551 - Bishop Walsh R C">Bishop Walsh R C</option>
										<option value="01002 - Blackburn College">Blackburn College</option>
										<option value="00292 - Boston Grammar School">Boston Grammar School</option>
										<option value="00006 - Bosworth College">Bosworth College</option>
										<option value="00531 - Bournside Sixth Form Centre">Bournside Sixth Form Centre</option>
										<option value="00075 - Bradford & Ilkley College">Bradford & Ilkley College</option>
										<option value="00481 - Brentwood High School">Brentwood High School</option>
										<option value="00115 - Broadway School">Broadway School</option>
										<option value="00776 - Brooke House College ">Brooke House College </option>
										<option value="00364 - Brooke Western City Technical College">Brooke Western City Technical College</option>
										<option value="00532 - Brookhouse Senior School">Brookhouse Senior School</option>
										<option value="00101 - Brooksby Agricultural College">Brooksby Agricultural College</option>
										<option value="00436 - Brookvale High School">Brookvale High School</option>
										<option value="00511 - Brymore School">Brymore School</option>
										<option value="00070 - Burton upon Trent Technical College">Burton upon Trent Technical College</option>
										<option value="00278 - Cambridgeshire Regional College">Cambridgeshire Regional College</option>
										<option value="00279 - Campion School">Campion School</option>
										<option value="00430 - Cardinal Griffin R C">Cardinal Griffin R C</option>
										<option value="00064 - Casterton Community College">Casterton Community College</option>
										<option value="00300 - Castle Vale Comprehensive">Castle Vale Comprehensive</option>
										<option value="00497 - Cedars Upper School">Cedars Upper School</option>
										<option value="00358 - Charles Edward Brooke School">Charles Edward Brooke School</option>
										<option value="00085 - Charles Frears College of Nursing & Midw">Charles Frears College of Nursing & Midw</option>
										<option value="00100 - Charles Keene College of Further Ed">Charles Keene College of Further Ed</option>
										<option value="00043 - Charnwood Academy">Charnwood Academy</option>
										<option value="00775 - Charnwood College">Charnwood College</option>
										<option value="00540 - Chasetown High School">Chasetown High School</option>
										<option value="00256 - Cheadle High School">Cheadle High School</option>
										<option value="00441 - Chellaston School">Chellaston School</option>
										<option value="00508 - Chellenham Bournside School">Chellenham Bournside School</option>
										<option value="00287 - Chesterfield College">Chesterfield College</option>
										<option value="00014 - Chichester College of Arts/Science/Techn">Chichester College of Arts/Science/Techn</option>
										<option value="00561 - Children's Hospital School">Children's Hospital School</option>
										<option value="00510 - CHRIST HOSPITAL">CHRIST HOSPITAL</option>
										<option value="00495 - Christ The King 6th Form College">Christ The King 6th Form College</option>
										<option value="00237 - Church Farm Grammer">Church Farm Grammer</option>
										<option value="00227 - City and Islington College Of F.E">City and Islington College Of F.E</option>
										<option value="00090 - City College, Manchester">City College, Manchester</option>
										<option value="00010 - City of Leicester College (was School)">City of Leicester College (was School)</option>
										<option value="00521 - Clacton County High School">Clacton County High School</option>
										<option value="00230 - Clarendon College">Clarendon College</option>
										<option value="00220 - Cleveland College of Art & Design">Cleveland College of Art & Design</option>
										<option value="00472 - Cleveland College of Art & Design">Cleveland College of Art & Design</option>
										<option value="00025 - Coalville Community College">Coalville Community College</option>
										<option value="00104 - Coalville Technical College">Coalville Technical College</option>
										<option value="00356 - Coke Thorpe">Coke Thorpe</option>
										<option value="00131 - Colchester Institute">Colchester Institute</option>
										<option value="00541 - Colchester Royal Grammar School">Colchester Royal Grammar School</option>
										<option value="00546 - College of West Anglia">College of West Anglia</option>
										<option value="00777 - Connexions ">Connexions </option>
										<option value="00515 - Corpus Christie">Corpus Christie</option>
										<option value="00253 - Coudon Court">Coudon Court</option>
										<option value="00062 - Countesthorpe Community College">Countesthorpe Community College</option>
										<option value="00110 - Cressex Secondary School">Cressex Secondary School</option>
										<option value="00376 - Crispin School">Crispin School</option>
										<option value="00013 - Crown Hills School & Community College">Crown Hills School & Community College</option>
										<option value="00773 - Croydon Awards Section">Croydon Awards Section</option>
										<option value="00212 - Cumbria College of Art & Design">Cumbria College of Art & Design</option>
										<option value="00213 - Darlington College of Technology">Darlington College of Technology</option>
										<option value="00564 - Darul Ulodh Leicester">Darul Ulodh Leicester</option>
										<option value="00522 - Daventry William Parker School">Daventry William Parker School</option>
										<option value="00050 - De Lisle Catholic Science College">De Lisle Catholic Science College</option>
										<option value="00229 - De Montfort University">De Montfort University</option>
										<option value="00234 - De Montfort University (Lincoln)">De Montfort University (Lincoln)</option>
										<option value="00528 - Deincourt Secondary School">Deincourt Secondary School</option>
										<option value="00303 - Derby Moon Community School">Derby Moon Community School</option>
										<option value="00554 - Derby Moor Community School">Derby Moor Community School</option>
										<option value="00185 - Derby School">Derby School</option>
										<option value="00421 - Derby Tertiary College">Derby Tertiary College</option>
										<option value="00076 - Derby University">Derby University</option>
										<option value="00336 - Desford College">Desford College</option>
										<option value="00341 - Dinnington Comprehensive">Dinnington Comprehensive</option>
										<option value="00182 - Dixie Grammar School">Dixie Grammar School</option>
										<option value="00393 - Djanogoly City Technical College">Djanogoly City Technical College</option>
										<option value="00169 - Donogh O'Malley Regional Tech College">Donogh O'Malley Regional Tech College</option>
										<option value="00012 - Dorothy Goodman School">Dorothy Goodman School</option>
										<option value="00550 - Downlands School">Downlands School</option>
										<option value="00128 - Duston Upper School">Duston Upper School</option>
										<option value="00063 - Earl Shilton Community College">Earl Shilton Community College</option>
										<option value="00289 - Elfed High School">Elfed High School</option>
										<option value="00123 - Ellesmere Community College">Ellesmere Community College</option>
										<option value="00362 - Ellington High School">Ellington High School</option>
										<option value="00159 - Elliott Durham">Elliott Durham</option>
										<option value="00073 - Emily Fortey School">Emily Fortey School</option>
										<option value="00037 - English Martyrs Catholic School">English Martyrs Catholic School</option>
										<option value="00069 - Epping Forest College">Epping Forest College</option>
										<option value="00326 - Exeter College">Exeter College</option>
										<option value="00176 - Fairfax School">Fairfax School</option>
										<option value="00162 - Fareham Tertiary College">Fareham Tertiary College</option>
										<option value="00243 - Farnborough College Of Technology">Farnborough College Of Technology</option>
										<option value="01011 - Forestway Special School">Forestway Special School</option>
										<option value="00500 - Fort Pitt Grammer School">Fort Pitt Grammer School</option>
										<option value="00117 - Fosse Secondary Modern">Fosse Secondary Modern</option>
										<option value="00152 - Fullhurst Community College">Fullhurst Community College</option>
										<option value="00440 - Garendon High School">Garendon High School</option>
										<option value="00334 - Gartree GM School">Gartree GM School</option>
										<option value="00239 - Gateshead College">Gateshead College</option>
										<option value="00054 - Gateway College">Gateway College</option>
										<option value="00409 - Gosford Hill School">Gosford Hill School</option>
										<option value="00068 - Grantham College">Grantham College</option>
										<option value="00307 - Great Yarmouth High School">Great Yarmouth High School</option>
										<option value="00002 - Greenacres School">Greenacres School</option>
										<option value="00269 - Grenville College">Grenville College</option>
										<option value="00051 - Groby Community College">Groby Community College</option>
										<option value="00297 - Guilsborough School">Guilsborough School</option>
										<option value="00038 - Guthlaxton College">Guthlaxton College</option>
										<option value="00005 - Hamilton Community College">Hamilton Community College</option>
										<option value="00040 - Harlow College">Harlow College</option>
										<option value="00490 - Harris CTC">Harris CTC</option>
										<option value="00119 - Harrogate College of Arts & Technology">Harrogate College of Arts & Technology</option>
										<option value="00337 - Harry Carlton Comprehensive">Harry Carlton Comprehensive</option>
										<option value="00257 - Harwich Sixth Form">Harwich Sixth Form</option>
										<option value="00320 - Haywood Secondary School">Haywood Secondary School</option>
										<option value="00316 - Hazelmead Academy">Hazelmead Academy</option>
										<option value="00526 - Headington School">Headington School</option>
										<option value="00565 - Heart of England Training Centre ">Heart of England Training Centre </option>
										<option value="00324 - Heathland School">Heathland School</option>
										<option value="00553 - Henley College">Henley College</option>
										<option value="00386 - Henry Gotch School">Henry Gotch School</option>
										<option value="00271 - Henry Gotch School">Henry Gotch School</option>
										<option value="00138 - Hereford College">Hereford College</option>
										<option value="00319 - Hereford College Of Art & Design">Hereford College Of Art & Design</option>
										<option value="00198 - Hertford Regional College">Hertford Regional College</option>
										<option value="00149 - High Leas School">High Leas School</option>
										<option value="00398 - Highbury Grove School">Highbury Grove School</option>
										<option value="00369 - Highfields School">Highfields School</option>
										<option value="00099 - Highham School">Highham School</option>
										<option value="00106 - Hinckley College of Further Education">Hinckley College of Further Education</option>
										<option value="00048 - Hind Leys Conmmunity College">Hind Leys Conmmunity College</option>
										<option value="00365 - Hitchin Girls School">Hitchin Girls School</option>
										<option value="00520 - Holland Park Secondary School">Holland Park Secondary School</option>
										<option value="00489 - Holy Cross Convent School">Holy Cross Convent School</option>
										<option value="99998 - Home Educated">Home Educated</option>
										<option value="00255 - Hope Valley College">Hope Valley College</option>
										<option value="00092 - Huddersfield Technical College">Huddersfield Technical College</option>
										<option value="00501 - Humphrey Perkins">Humphrey Perkins</option>
										<option value="00277 - Huntingdonshire College">Huntingdonshire College</option>
										<option value="00517 - Hyde Clarendon College">Hyde Clarendon College</option>
										<option value="00273 - Ibstock Community College">Ibstock Community College</option>
										<option value="00296 - Irwin College">Irwin College</option>
										<option value="00909 - Jamea Girls Academy">Jamea Girls Academy</option>
										<option value="00007 - John Cleveland College">John Cleveland College</option>
										<option value="00016 - John Ellis Community College">John Ellis Community College</option>
										<option value="01003 - John Ferneley College">John Ferneley College</option>
										<option value="00381 - John Leggott 6th Form College">John Leggott 6th Form College</option>
										<option value="00330 - John O'Gaunt School">John O'Gaunt School</option>
										<option value="00539 - John Port School">John Port School</option>
										<option value="00538 - John Spendluffe School">John Spendluffe School</option>
										<option value="00524 - Joseph Leckie School">Joseph Leckie School</option>
										<option value="00031 - Judgemeadow Community College">Judgemeadow Community College</option>
										<option value="00134 - Kettering Boys' School">Kettering Boys' School</option>
										<option value="00313 - Kettering High School">Kettering High School</option>
										<option value="00560 - Keyham Lodge School">Keyham Lodge School</option>
										<option value="00458 - King Edward School Witley">King Edward School Witley</option>
										<option value="00173 - King Edward VI School">King Edward VI School</option>
										<option value="00053 - King Edward VII School (inc Melton Vale)">King Edward VII School (inc Melton Vale)</option>
										<option value="00046 - King Edward VII Science & Sports College">King Edward VII Science & Sports College</option>
										<option value="00023 - King Richard III School">King Richard III School</option>
										<option value="00132 - Kingsthorpe Upper School">Kingsthorpe Upper School</option>
										<option value="00155 - Kingswood School">Kingswood School</option>
										<option value="00518 - Knossington Grange School">Knossington Grange School</option>
										<option value="00310 - Lady Manners School">Lady Manners School</option>
										<option value="00202 - Lancaster & Morecambe College">Lancaster & Morecambe College</option>
										<option value="00024 - Lancaster School">Lancaster School</option>
										<option value="00089 - Langley College of Further Education">Langley College of Further Education</option>
										<option value="00350 - Lawrence Sheriff School">Lawrence Sheriff School</option>
										<option value="00093 - Leeds College of Art & Design">Leeds College of Art & Design</option>
										<option value="00080 - Leeds College of Art & Design">Leeds College of Art & Design</option>
										<option value="00368 - Lees Brock Community School">Lees Brock Community School</option>
										<option value="00558 - Leicester College">Leicester College</option>
										<option value="01004 - Leicester Community Islamic School">Leicester Community Islamic School</option>
										<option value="00156 - Leicester Grammar School">Leicester Grammar School</option>
										<option value="00065 - Leicester High School For Girls">Leicester High School For Girls</option>
										<option value="00254 - Leicester Islamic Academy">Leicester Islamic Academy</option>
										<option value="00233 - Leicester Islamic Academy">Leicester Islamic Academy</option>
										<option value="00563 - Leicester Montisorri 6th Form College">Leicester Montisorri 6th Form College</option>
										<option value="00097 - Leics Education Training">Leics Education Training</option>
										<option value="00467 - Leysland High School">Leysland High School</option>
										<option value="00499 - Leyton Sixth Form College">Leyton Sixth Form College</option>
										<option value="00479 - Licensed Victuallers Boarding School">Licensed Victuallers Boarding School</option>
										<option value="00428 - Limehurst High School">Limehurst High School</option>
										<option value="00416 - Linwood Boys School">Linwood Boys School</option>
										<option value="00096 - Linwood Centre">Linwood Centre</option>
										<option value="00231 - Lodge Park School">Lodge Park School</option>
										<option value="00536 - Lomagundi College">Lomagundi College</option>
										<option value="00150 - Long Close School">Long Close School</option>
										<option value="00507 - Long Field School">Long Field School</option>
										<option value="00177 - Longlands College">Longlands College</option>
										<option value="00045 - Longslade Community College">Longslade Community College</option>
										<option value="00107 - Loughborough College">Loughborough College</option>
										<option value="00067 - Loughborough Grammar School">Loughborough Grammar School</option>
										<option value="00463 - Loughborough High School for Girls">Loughborough High School for Girls</option>
										<option value="00103 - Loughborough University of Technology">Loughborough University of Technology</option>
										<option value="00389 - Lowfields Secondary School">Lowfields Secondary School</option>
										<option value="00357 - LRI Hospital School">LRI Hospital School</option>
										<option value="00077 - Lutterworth College">Lutterworth College</option>
										<option value="00452 - Lycee Van Gogh">Lycee Van Gogh</option>
										<option value="00406 - Lynm High School">Lynm High School</option>
										<option value="00081 - Mackworth College, Derby">Mackworth College, Derby</option>
										<option value="00562 - Madani High School">Madani High School</option>
										<option value="00411 - Magdelene College School">Magdelene College School</option>
										<option value="00474 - Manning Comprehensive School">Manning Comprehensive School</option>
										<option value="00009 - Manorbrook School">Manorbrook School</option>
										<option value="00055 - Maple Hayes Hall School">Maple Hayes Hall School</option>
										<option value="00079 - Maplewell Hall School">Maplewell Hall School</option>
										<option value="00165 - Marlborough School">Marlborough School</option>
										<option value="00030 - Mary Linwood School">Mary Linwood School</option>
										<option value="00298 - Matthew Boulton College">Matthew Boulton College</option>
										<option value="00537 - Matthew Murnery School">Matthew Murnery School</option>
										<option value="00102 - Melton Mowbray College of Further Ed">Melton Mowbray College of Further Ed</option>
										<option value="00519 - Mereway Upper School">Mereway Upper School</option>
										<option value="00268 - Merton College">Merton College</option>
										<option value="00142 - Mid Kent College">Mid Kent College</option>
										<option value="00340 - Middlesex University">Middlesex University</option>
										<option value="00147 - Millgate Centre">Millgate Centre</option>
										<option value="00027 - Moat Community College">Moat Community College</option>
										<option value="00413 - Moat Girls Secondary School">Moat Girls Secondary School</option>
										<option value="00129 - Montsaye School">Montsaye School</option>
										<option value="00412 - Mount Grays">Mount Grays</option>
										<option value="00015 - Mount School">Mount School</option>
										<option value="00032 - Mundella Community College">Mundella Community College</option>
										<option value="00163 - Nairn Academy">Nairn Academy</option>
										<option value="00056 - Nene College">Nene College</option>
										<option value="00148 - Nether Hall School">Nether Hall School</option>
										<option value="00544 - New College Leicester">New College Leicester</option>
										<option value="01013 - New College Nottingham">New College Nottingham</option>
										<option value="00041 - New Parks Community College">New Parks Community College</option>
										<option value="00146 - Newarke King Richard III Community Coll">Newarke King Richard III Community Coll</option>
										<option value="00026 - Newarke School">Newarke School</option>
										<option value="00241 - Newbold Community School">Newbold Community School</option>
										<option value="00205 - Newcastle College">Newcastle College</option>
										<option value="00314 - Newcastle-Under-Lyme College">Newcastle-Under-Lyme College</option>
										<option value="00118 - North Devon College">North Devon College</option>
										<option value="00459 - North Leamington School">North Leamington School</option>
										<option value="00086 - North Warwickshire and Hinckley College ">North Warwickshire and Hinckley College </option>
										<option value="00209 - Northampton College">Northampton College</option>
										<option value="00401 - Northwood School">Northwood School</option>
										<option value="00175 - Nottingham Trent University">Nottingham Trent University</option>
										<option value="00071 - Oakham Grammar School">Oakham Grammar School</option>
										<option value="00542 - Oakwood High School">Oakwood High School</option>
										<option value="00284 - Oldbury Wells School">Oldbury Wells School</option>
										<option value="XXXXX - Other (Specified on application form)"> Other (my school isn't listed)</option>
										<option value="00445 - Our Lady's Convent">Our Lady's Convent</option>
										<option value="99999 - Out of County">Out of County</option>
										<option value="00552 - Overstone Park School">Overstone Park School</option>
										<option value="00122 - Oxford College of Further Education">Oxford College of Further Education</option>
										<option value="00523 - Palatine High School">Palatine High School</option>
										<option value="00450 - Parklands Arya Girls High">Parklands Arya Girls High</option>
										<option value="00465 - Pembroke Bush School">Pembroke Bush School</option>
										<option value="00420 - Peterborough High School">Peterborough High School</option>
										<option value="00322 - Peterborough Regional College">Peterborough Regional College</option>
										<option value="00425 - Pingle School G/M">Pingle School G/M</option>
										<option value="00512 - Prince Henry's High School">Prince Henry's High School</option>
										<option value="00435 - Prince William School">Prince William School</option>
										<option value="00493 - Princethorpe College">Princethorpe College</option>
										<option value="00180 - Punjab University">Punjab University</option>
										<option value="00359 - Queen Margarets School">Queen Margarets School</option>
										<option value="00302 - Queens School">Queens School</option>
										<option value="00444 - Ratcliffe College">Ratcliffe College</option>
										<option value="00126 - Rathbone Training Centre">Rathbone Training Centre</option>
										<option value="00047 - Rawlins Community College">Rawlins Community College</option>
										<option value="00509 - Rawlins Grammar School for Girls">Rawlins Grammar School for Girls</option>
										<option value="00339 - Regent College">Regent College</option>
										<option value="00333 - Reigate 6th Form College">Reigate 6th Form College</option>
										<option value="00543 - Rhodesway Upper School">Rhodesway Upper School</option>
										<option value="00201 - Richmond Upon Thames College">Richmond Upon Thames College</option>
										<option value="00545 - Riverside Business & Enterprise College">Riverside Business & Enterprise College</option>
										<option value="00294 - Robert Pattison School">Robert Pattison School</option>
										<option value="00044 - Robert Smyth School">Robert Smyth School</option>
										<option value="00154 - Robert Sutton Catholic High School">Robert Sutton Catholic High School</option>
										<option value="00466 - Roundwood Park School">Roundwood Park School</option>
										<option value="00035 - Rowley Fields School & Community College">Rowley Fields School & Community College</option>
										<option value="00555 - Royal School for the Deaf">Royal School for the Deaf</option>
										<option value="00312 - Rugby College Of Further Education">Rugby College Of Further Education</option>
										<option value="00485 - Rushcliffe Comprehensive">Rushcliffe Comprehensive</option>
										<option value="00121 - Rushden School">Rushden School</option>
										<option value="00036 - Rushey Mead School">Rushey Mead School</option>
										<option value="00029 - Rutland Sixth Form College">Rutland Sixth Form College</option>
										<option value="00470 - Saffron Walden County High School">Saffron Walden County High School</option>
										<option value="00049 - Saint Pauls Catholic School">Saint Pauls Catholic School</option>
										<option value="01012 - Samworth Enterprise Academy">Samworth Enterprise Academy</option>
										<option value="00556 - Sedgemoor College">Sedgemoor College</option>
										<option value="00468 - Shapwick School">Shapwick School</option>
										<option value="00194 - Sheffield College">Sheffield College</option>
										<option value="00168 - Shepshed C.C.">Shepshed C.C.</option>
										<option value="00196 - Shrewsbury College of Art & Technology">Shrewsbury College of Art & Technology</option>
										<option value="00408 - Sir Christopher Hatton">Sir Christopher Hatton</option>
										<option value="00288 - Sir John Leman High School">Sir John Leman High School</option>
										<option value="00042 - Sir Jonathan North Community College">Sir Jonathan North Community College</option>
										<option value="00446 - Sir William Ramsey School">Sir William Ramsey School</option>
										<option value="00388 - Sir William Robertson High School">Sir William Robertson High School</option>
										<option value="00095 - Skegness Grammar School">Skegness Grammar School</option>
										<option value="00028 - Soar Valley College">Soar Valley College</option>
										<option value="00088 - South Bristol Technical College">South Bristol Technical College</option>
										<option value="00385 - South Craven School">South Craven School</option>
										<option value="00203 - South Devon College of Art Design & Tech">South Devon College of Art Design & Tech</option>
										<option value="00258 - South East Essex College">South East Essex College</option>
										<option value="00140 - South Fields College of F E">South Fields College of F E</option>
										<option value="00379 - South Kent College">South Kent College</option>
										<option value="01006 - South Leicestershire College">South Leicestershire College</option>
										<option value="00105 - South Leics College (was Wigston Coll)">South Leics College (was Wigston Coll)</option>
										<option value="00039 - South Notts College of FE">South Notts College of FE</option>
										<option value="00226 - South Thames College">South Thames College</option>
										<option value="00431 - South Wigston High School">South Wigston High School</option>
										<option value="00174 - Southampton Institute of HE">Southampton Institute of HE</option>
										<option value="00423 - Southfield School">Southfield School</option>
										<option value="00216 - Southgate School">Southgate School</option>
										<option value="00161 - Spencefield School">Spencefield School</option>
										<option value="00232 - Spondon House School">Spondon House School</option>
										<option value="00343 - St Augustines Roman Catholic School">St Augustines Roman Catholic School</option>
										<option value="00498 - St Bede The Dicker">St Bede The Dicker</option>
										<option value="00280 - St Benedicts R C Upper School">St Benedicts R C Upper School</option>
										<option value="00184 - St Bernards High School">St Bernards High School</option>
										<option value="00535 - St Crispins School">St Crispins School</option>
										<option value="00223 - St Edmunds Church of England Girls' Sch">St Edmunds Church of England Girls' Sch</option>
										<option value="00774 - St Helens Awards Section">St Helens Awards Section</option>
										<option value="00238 - St Ivo School">St Ivo School</option>
										<option value="00261 - St Josephs">St Josephs</option>
										<option value="00136 - St Leonards College">St Leonards College</option>
										<option value="00276 - St Nicholas">St Nicholas</option>
										<option value="00549 - St Pauls R.C. Comprehensive">St Pauls R.C. Comprehensive</option>
										<option value="00372 - St Peters School">St Peters School</option>
										<option value="00295 - St Peters Secondary School">St Peters Secondary School</option>
										<option value="00120 - St Phillips 6th Form College">St Phillips 6th Form College</option>
										<option value="00218 - St Simon Stock School">St Simon Stock School</option>
										<option value="00158 - St Thomas More School">St Thomas More School</option>
										<option value="00245 - St Wulfrans High School">St Wulfrans High School</option>
										<option value="00477 - St.Georges Church of England School">St.Georges Church of England School</option>
										<option value="00530 - St.Ignatius">St.Ignatius</option>
										<option value="00400 - Staffordshire University">Staffordshire University</option>
										<option value="00451 - Stanbrige Earls">Stanbrige Earls</option>
										<option value="01008 - Stephenson College">Stephenson College</option>
										<option value="00443 - Stonehill High">Stonehill High</option>
										<option value="00778 - Studio School">Studio School</option>
										<option value="00323 - Sutton Coldfield College">Sutton Coldfield College</option>
										<option value="00516 - Swansea College">Swansea College</option>
										<option value="00486 - Swanson School">Swanson School</option>
										<option value="00019 - Tamworth College of Further Education">Tamworth College of Further Education</option>
										<option value="00248 - Telford College Of Art & Technology">Telford College Of Art & Technology</option>
										<option value="00527 - The Brunts Upper School">The Brunts Upper School</option>
										<option value="00399 - The Kings School">The Kings School</option>
										<option value="4246 - The Lancaster School">The Lancaster School</option>
										<option value="00240 - The Rutland College">The Rutland College</option>
										<option value="00215 - Thomas Danby College">Thomas Danby College</option>
										<option value="00533 - Thornby Hall">Thornby Hall</option>
										<option value="00344 - Trent College">Trent College</option>
										<option value="00018 - Tresham College">Tresham College</option>
										<option value="00317 - Trinity Comprehensive School">Trinity Comprehensive School</option>
										<option value="00153 - University of Birmingham">University of Birmingham</option>
										<option value="00066 - University of Central England">University of Central England</option>
										<option value="00108 - University of Leicester">University of Leicester</option>
										<option value="00166 - University of London">University of London</option>
										<option value="00141 - University Of Luton">University Of Luton</option>
										<option value="00082 - Uppingham Community College">Uppingham Community College</option>
										<option value="00438 - Uxbridge College">Uxbridge College</option>
										<option value="00058 - Vale of Catmose College">Vale of Catmose College</option>
										<option value="00151 - Vaughan College">Vaughan College</option>
										<option value="00084 - Walsall College of Technology">Walsall College of Technology</option>
										<option value="00377 - Ward Freeman School">Ward Freeman School</option>
										<option value="00457 - Warlingham School">Warlingham School</option>
										<option value="01007 - Welland Park Community College">Welland Park Community College</option>
										<option value="00471 - Wellfield High School">Wellfield High School</option>
										<option value="00480 - Weobley Comprehensive School">Weobley Comprehensive School</option>
										<option value="00349 - West Bridgford Comprehensive School">West Bridgford Comprehensive School</option>
										<option value="01005 - West Gate School">West Gate School</option>
										<option value="00059 - West Herts College">West Herts College</option>
										<option value="00197 - West Notts College">West Notts College</option>
										<option value="00270 - West Oxfordshire College">West Oxfordshire College</option>
										<option value="00473 - West Somerset Sixth Form College">West Somerset Sixth Form College</option>
										<option value="00246 - West Suffolk College">West Suffolk College</option>
										<option value="00491 - West Thames College">West Thames College</option>
										<option value="00514 - Westcotes School">Westcotes School</option>
										<option value="00061 - Western Park Special School">Western Park Special School</option>
										<option value="00529 - Westhill High School">Westhill High School</option>
										<option value="00127 - Weston Favell Upper School">Weston Favell Upper School</option>
										<option value="00427 - Whitstone School">Whitstone School</option>
										<option value="00513 - Wilberforce College">Wilberforce College</option>
										<option value="00290 - William Beaumont High School">William Beaumont High School</option>
										<option value="00566 - William Bradford Community College">William Bradford Community College</option>
										<option value="00011 - Willowbank School">Willowbank School</option>
										<option value="00204 - Wilmorton Tertiary College">Wilmorton Tertiary College</option>
										<option value="00363 - Windsor School">Windsor School</option>
										<option value="00008 - Winstanley Community College">Winstanley Community College</option>
										<option value="00267 - Wintringham School">Wintringham School</option>
										<option value="00329 - Wirral Metropolitan College">Wirral Metropolitan College</option>
										<option value="00429 - Witton Park High School">Witton Park High School</option>
										<option value="00434 - Wood Green High School">Wood Green High School</option>
										<option value="00060 - Woodbank Grammar School">Woodbank Grammar School</option>
										<option value="00301 - Woodlands Community School">Woodlands Community School</option>
										<option value="00382 - Worcester 6th Form College">Worcester 6th Form College</option>
										<option value="00033 - Wreake Valley Community College">Wreake Valley Community College</option>
										<option value="00020 - Wycliffe Community College">Wycliffe Community College</option>
										<option value="00052 - Wyggeston & Queen Elizabeth I College">Wyggeston & Queen Elizabeth I College</option>
										<option value="00113 - York College of Art & Technology">York College of Art & Technology</option>
										<option value="00351 - Ysgol Gyfun Garth Olwg">Ysgol Gyfun Garth Olwg</option>
									</select>
								</div>					
							</div>					
							
						</form>
				
					</div>
				</div>
			</div>
			<!-- Previous study END -->

			
			<!-- -------------------------------------------------------------- -->
			<!-- Course selection start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseFifteen">
						<h4 class="panel-title" id="course_heading">
					Course
						</h4>						
					</a>
				</div>
				<div id="collapseFifteen" class="panel-collapse collapse in">
					<div class="panel-body">

						<p>
							Begin typing the course code or title you are applying for in the boxes below, to select your course(s)
						</p>					
					
						<form name="form_course" id="form_course" role="form" class="form-horizontal">

							<div class="form-group" >
								<label class="control-label col-sm-2" for="course_1_select" name="course_1_select_label" id="course_1_select_label">First Choice</label>
								 <div class="col-sm-7" style="margin-top:5px;">
									<div class="ui-widget">
										<input id="course_1_select" name="course_1_select" style="width:90%;" onFocus="reset_error_class('course_1_select_label');" value="">
										<input type="hidden" value="" name="course_1_chosen" id="course_1_chosen" >
									</div>								
								 </div>
							</div>

							<div class="form-group" >
								<label class="control-label col-sm-2" for="course_2_select" name="course_2_select_label" id="course_2_select_label">Second Choice</label>
								 <div class="col-sm-7" style="margin-top:5px;">
									<div class="ui-widget">
										<input id="course_2_select" name="course_2_select" style="width:90%;" onFocus="reset_error_class('course_2_select_label');" value="">
										<input type="hidden" value="" name="course_2_chosen" id="course_2_chosen" >
									</div>								
								 </div>
							</div>
							
							<div class="form-group" >

								<label class="control-label col-sm-2" for="course_3_select" name="course_3_select_label" id="course_2_select_label">Third Choice</label>
							
								 <div class="col-sm-7" style="margin-top:5px;">
									<div class="ui-widget">
										<input id="course_3_select" name="course_3_select" style="width:90%;" onFocus="reset_error_class('course_3_select_label');" value="">
										<input type="hidden" value="" name="course_3_chosen" id="course_3_chosen" >
									</div>								
								 </div>								 
								 
							</div>

							
							<div class="form-group" >
								<label class="control-label col-sm-2" for="applying_apprenticeship" name="applying_apprenticeship_label" id="applying_apprenticeship_label">Are you applying for an Apprenticeship?</label>
							
								 <div class="col-sm-7" style="margin-top:5px;">
									<div class="ui-widget">
										Yes <input name="applying_apprenticeship" id="applying_apprenticeship" type="radio" value="Yes" onClick="reset_error_class('applying_apprenticeship_label');">
										No <input name="applying_apprenticeship" id="applying_apprenticeship" type="radio" value="No" onClick="reset_error_class('applying_apprenticeship_label');">
									</div>								
								 </div>								 
							</div>

							<div class="form-group" >
								<label class="control-label col-sm-2" for="have_employer" name="have_employer_label" id="have_employer_label">Do you have an employer?</label>
							
								 <div class="col-sm-7" style="margin-top:5px;">
									<div class="ui-widget">
										Yes <input name="have_employer" id="have_employer" type="radio" value="Yes" onClick="reset_error_class('have_employer_label');">
										No <input name="have_employer" id="have_employer" type="radio" value="No" onClick="reset_error_class('have_employer_label');">
									</div>								
								 </div>								 
							</div>


						</form>

					</div>
				</div>
			</div>
			<!-- course selection end -->			

			<!-- -------------------------------------------------------------- -->			
			<!-- Personal Details start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
						<h4 class="panel-title" id="personal_details_heading">
							Personal Details
						</h4>
					</a>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse in">
					<div class="panel-body">

						<form name="form_personal_details" id="form_personal_details" role="form" class="form-horizontal">

							<div class="form-group">
								<label class="control-label col-sm-3" name="learner_title_label" id="learner_title_label" for="learner_title">Title</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="learner_title" id="learner_title" onChange="reset_error_class('learner_title_label');">
										<option value=""></option>
										<option value="Mr">Mr</option>
										<option value="Mrs">Mrs</option>
										<option value="Miss">Miss</option>
										<option value="Ms">Ms</option>
										<option value="Dr">Dr</option>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3" name="first_name_label" id="first_name_label" for="first_name">First name</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="text" name="first_name" id="first_name" value="" onFocus="reset_error_class('first_name_label');">
								</div>					
							</div>						

							<div class="form-group">
								<label class="control-label col-sm-3" name="middle_name_label" id="middle_name_label" for="middle_name">Middle name(s)</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="text" name="middle_name" id="middle_name" value="" onFocus="reset_error_class('middle_name_label');">
								</div>					
							</div>				
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="last_name_label" id="last_name_label" for="first_name">Surname</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="text" name="last_name" id="last_name" value="" onFocus="reset_error_class('last_name_label');">
								</div>					
							</div>				

							<div class="form-group">
								<label class="control-label col-sm-3" name="previous_surname_label" id="previous_surname_label" for="first_name">Previous Surname(s)</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="text" name="previous_surname" id="previous_surname" value="" onFocus="reset_error_class('previous_surname_label');">
								</div>					
							</div>							

							<div class="form-group">
								<label class="control-label col-sm-3" name="learner_gender_label" id="learner_gender_label" for="learner_gender">Sex</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Male <input name="learner_gender" id="learner_gender" type="radio" value="Male" onClick="reset_error_class('learner_gender_label');">
									Female <input name="learner_gender" id="learner_gender" type="radio" value="Female" onClick="reset_error_class('learner_gender_label');">
								</div>					
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3" name="gender_identity_label" id="gender_identity_label" for="gender_identity">Sexual Identity</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="gender_identity" id="gender_identity" onchange="reset_error_class('gender_identity_label');">
										<option value="">Please select</option>
										<option value="1 Bisexual">Bisexual</option>
										<option value="2 Gay Man">Gay Man</option>												
										<option value="3 Heterosexual">Heterosexual</option>
										<option value="4 Lesbian/Gay">Lesbian/Gay</option>
										<option value="6 Prefer not to say">Prefer not to say</option>												
									</select>
								</div>					
							</div>
							
							
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="ethnicity_label" id="ethnicity_label" for="ethnicity">Ethnicity</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="ethnicity" id="ethnicity" onChange="reset_error_class('ethnicity_label');">
										<option value=''></option>
										<option value='31 - English/Welsh/Scottish/Northern Irish/British'>English/Welsh/Scottish/Northern Irish/British</option>
										<option value='32 - Irish' >Irish</option>
										<option value='33 - Gypsy or Irish Traveller' >Gypsy or Irish Traveller</option>
										<option value='34 - Any other White background' >Any other White background</option>

										<option value='35 White and Black Caribbean' >White and Black Caribbean</option>
										<option value='36 White and Black African' >White and Black African</option>
										<option value='37 White and Asian' >White and Asian</option>
										<option value='38 Any other mixed background' >Any other mixed background</option>

										<option value='39 Indian' >Indian</option>
										<option value='40 Pakistani' >Pakistani</option>
										<option value='41 Bangladeshi' >Bangladeshi</option>
										<option value='42 Chinese' >Chinese</option>
										<option value='43 Any other Asian background' >Any other Asian background</option>

										<option value='44 African' >African</option>
										<option value='45 Caribbean' >Caribbean</option>
										<option value='46 Any other Black/African/Caribbean background' >Any other Black/African/Caribbean background</option>

										<option value='47 Arab' >Arab</option>

										<option value='98 Any Other ethnic group' >Any Other ethnic group</option>

										<option value='99 Not known/not provided' >Not known/not provided</option>
									</select>
								</div>					
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3" name="religion_belief_label" id="religion_belief_label" for="religion_belief">Religion / Belief</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="religion_belief" id="religion_belief" onchange="reset_error_class('religion_belief_label');">
										<option value=""></option>
										<option value="12 Agnostic">Agnostic</option>
										<option value="09 Atheist">Atheist</option>
										<option value="03 Buddhist">Buddhist</option>
										<option value="01 Christian">Christian (including Catholic)</option>
										<option value="05 Hindu">Hindu</option>
										<option value="08 Humanist">Humanist</option>
										<option value="04 Jain">Jain</option>
										<option value="07 Jewish">Jewish</option>
										<option value="02 Muslim">Muslim</option>
										<option value="06 Sikh">Sikh</option>
										<option value="11 No Religion">No Religion</option>
										<option value="13 Prefer not to say">Prefer not to say</option>
										<option value="14 Dont know">Dont know</option>
										<option value="10 Other">Other</option>
									</select>
								</div>
							</div>
							
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="dob_label" id="dob_label" for="date_of_birth_day">Date of Birth</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="date_of_birth_day" id="date_of_birth_day" onChange="reset_error_class('dob_label');">
										<option value=""></option>
										<option value="01">01</option>
										<option value="02">02</option>
										<option value="03">03</option>
										<option value="04">04</option>
										<option value="05">05</option>
										<option value="06">06</option>
										<option value="07">07</option>
										<option value="08">08</option>
										<option value="09">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
										<option value="24">24</option>
										<option value="25">25</option>
										<option value="26">26</option>
										<option value="27">27</option>
										<option value="28">28</option>
										<option value="29">29</option>
										<option value="30">30</option>
										<option value="31">31</option>
									</select>

									<select name="date_of_birth_month" id="date_of_birth_month" onChange="reset_error_class('dob_label');">
										<option value=""></option>
										<option value="01">January</option>
										<option value="02">February</option>
										<option value="03">March</option>
										<option value="04">April</option>
										<option value="05">May</option>
										<option value="06">June</option>
										<option value="07">July</option>
										<option value="08">August</option>
										<option value="09">September</option>
										<option value="10">October</option>
										<option value="11">November</option>
										<option value="12">December</option>
									</select>							
											
									<select id="date_of_birth_year" name="date_of_birth_year" value="" onChange="reset_error_class('dob_label');">
										<option value=""></option>
										<option value="1945">1945</option>
										<option value="1946">1946</option>
										<option value="1947">1947</option>
										<option value="1948">1948</option>
										<option value="1949">1949</option>
										<option value="1950">1950</option>
										<option value="1951">1951</option>
										<option value="1952">1952</option>
										<option value="1953">1953</option>
										<option value="1954">1954</option>
										<option value="1955">1955</option>
										<option value="1956">1956</option>
										<option value="1957">1957</option>
										<option value="1958">1958</option>
										<option value="1959">1959</option>
										<option value="1960">1960</option>
										<option value="1961">1961</option>
										<option value="1962">1962</option>
										<option value="1963">1963</option>
										<option value="1964">1964</option>
										<option value="1965">1965</option>
										<option value="1966">1966</option>
										<option value="1967">1967</option>
										<option value="1968">1968</option>
										<option value="1969">1969</option>
										<option value="1970">1970</option>
										<option value="1971">1971</option>
										<option value="1972">1972</option>
										<option value="1973">1973</option>
										<option value="1974">1974</option>
										<option value="1975">1975</option>
										<option value="1976">1976</option>
										<option value="1977">1977</option>
										<option value="1978">1978</option>
										<option value="1979">1979</option>
										<option value="1980">1980</option>
										<option value="1981">1981</option>
										<option value="1982">1982</option>
										<option value="1983">1983</option>
										<option value="1984">1984</option>
										<option value="1985">1985</option>
										<option value="1986">1986</option>
										<option value="1987">1987</option>
										<option value="1988">1988</option>
										<option value="1989">1989</option>
										<option value="1990">1990</option>
										<option value="1991">1991</option>
										<option value="1992">1992</option>
										<option value="1993">1993</option>
										<option value="1994">1994</option>
										<option value="1995">1995</option>
										<option value="1996">1996</option>
										<option value="1997">1997</option>
										<option value="1998">1998</option>
										<option value="1999">1999</option>
										<option value="2000">2000</option>
										<option value="2001">2001</option>
									</select>

								</div>					
							</div><!-- end date selects -->								

							<div class="form-group">
								<label class="control-label col-sm-3" for="national_insurance_number">National Insurance Number</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="text" name="national_insurance_number" id="national_insurance_number" value="">
								</div>					
							</div>

						</form>

					</div>
				</div>
			</div>
			<!-- Personal Details END -->			
			
			
			<!-- Address and Contact Details start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
						<h4  class="panel-title" id="address_contact_details_heading">
							Address and Contact Details
						</h4>
					</a>
				</div>
				<div id="collapseThree" class="panel-collapse collapse in">
					<div class="panel-body">
			
					
						<form name="form_address_contact_details" id="form_address_contact_details" role="form" class="form-horizontal">

							<div class="form-group">
								<label class="control-label col-sm-3" name="international_learner_label" id="international_learner_label" for="international_learner">Are you an International learner? i.e you are NOT a European Union National</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="international_learner" id="international_learner" type="radio" value="Yes" onClick="reset_error_class('international_learner_label');">
									No <input name="international_learner" id="international_learner" type="radio" value="No" onClick="reset_error_class('international_learner_label');">
								</div>					
							</div>	
						
							<div class="form-group">
								<label class="control-label col-sm-3" id="home_telephone_label" name="home_telephone_label" for="home_telephone">Home Telephone</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:200px;" type="tel" name="home_telephone" id="home_telephone" value="" onFocus="reset_error_class('home_telephone_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-3" id="mobile_telephone_label" name="mobile_telephone_label"  for="mobile_telephone">Mobile Telephone</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:200px;" maxlength="22" type="tel" name="mobile_telephone" id="mobile_telephone" value="" onFocus="reset_error_class('mobile_telephone_label');">
								</div>					
							</div>	

							
							<div class="form-group" style="margin-top:20px;margin-bottom:20px;">
								<label class="control-label col-sm-3" id="email_address_label" name="email_address_label" for="email_address">e-mail</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:300px;" name="email_address" id="email_address" value="" onFocus="reset_error_class('email_address_label');" type="email" >
								</div>					

								<label class="control-label col-sm-3" id="confirm_email_address_label" name="confirm_email_address_label" for="confirm_email_address">confirm e-mail</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:300px;"  name="confirm_email_address" id="confirm_email_address" onkeyup="check_email_identical();" value="" onFocus="reset_error_class('confirm_email_address_label');" type="email" >
									<div style="display:none;color:red;" id="confirm_email_address_error" name="confirm_email_address_error" >email address does not match</div>
								</div>					

							</div>	

							
							<div class="form-group">
								<label class="control-label col-sm-3" name="emergency_contact_name_label" id="emergency_contact_name_label" for="emergency_contact_name">Name of Emergency Contact</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:200px;" maxlength='29' type="text" name="emergency_contact_name" id="emergency_contact_name" value="" onFocus="reset_error_class('emergency_contact_name_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-3" name="emergency_contact_telephone_label" id="emergency_contact_telephone_label" for="emergency_contact_telephone">Emergency Contact Telephone</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:200px;" maxlength="22" type="text" name="emergency_contact_telephone" id="emergency_contact_telephone" value="" onFocus="reset_error_class('emergency_contact_telephone_label');$('#emergency_contact_telephone_label_2').hide();">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-3"></label>
								<div id="emergency_contact_telephone_label_2" name="emergency_contact_telephone_label_2" class="col-sm-9" style="margin-top:5px;display:none;color:#de5555;font-family:Helvetica Neue,Helvetica,Arial,sans-serif;font-weight:bold;">
									The emergency contact telephone number cannot be the same as your mobile number
								</div>					
							</div>	



							<div style="height:20px">
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3" name="living_independently_label" id="living_independently_label" for="living_independently">Are you aged 16 to 18 and living independently?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="living_independently" id="living_independently" type="radio" value="Yes" onClick="reset_error_class('living_independently_label');">
									No <input name="living_independently" id="living_independently" type="radio" value="No" onClick="reset_error_class('living_independently_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-3" name="in_care_label" id="in_care_label" for="in_care">Are you currently in care, or a recent care leaver, or receiving leaving care services (e.g. foster care, residential care, unaccompanied asylum seeker)?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="in_care" id="in_care" type="radio" value="Yes" onClick="reset_error_class('in_care_label');">
									No <input name="in_care" id="in_care" type="radio" value="No" onClick="reset_error_class('in_care_label');">
								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="permanent_address_line_1_label" id="permanent_address_line_1_label"  for="permanent_address_line_1">Address Line 1</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="permanent_address_line_1" id="permanent_address_line_1" value="" onFocus="reset_error_class('permanent_address_line_1_label');">
								</div>					
							</div>					
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="permanent_address_line_2_label" id="permanent_address_line_2_label"  for="permanent_address_line_2">Address Line 2</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="permanent_address_line_2" id="permanent_address_line_2" value="" onFocus="reset_error_class('permanent_address_line_2_label');">
								</div>					
							</div>								
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="permanent_town_city_label" id="permanent_town_city_label" for="permanent_town_city">Town / City</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="permanent_town_city" id="permanent_town_city" value="" onFocus="reset_error_class('permanent_town_city_label');">
								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="permanent_county_label" id="permanent_county_label" for="permanent_county">County</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="permanent_county" id="permanent_county" value="" onFocus="reset_error_class('permanent_county_label');">
								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="permanent_postcode_label" id="permanent_postcode_label" for="permanent_postcode">Postcode</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input size="4" maxlength="4" type="text" name="permanent_postcode_part_1" id="permanent_postcode_part_1" value="" onFocus="reset_error_class('permanent_postcode_label');">
									<input size="4" maxlength="4" type="text" name="permanent_postcode_part_2" id="permanent_postcode_part_2" value="" onFocus="reset_error_class('permanent_postcode_label');">
								</div>					
							</div>				

							<h5>Termtime Address (only complete if this is different to your permanent address)</h5>
							
							<div class="form-group">
								<label class="control-label col-sm-3" id="termtime_address_line_1_label" name="termtime_address_line_1_label" for="termtime_address_line_1">Address Line 1</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="termtime_address_line_1" id="termtime_address_line_1" value="" onFocus="reset_error_class('termtime_address_line_1_label');">
								</div>					
							</div>					

							<div class="form-group">
								<label class="control-label col-sm-3" id="termtime_address_line_2_label" name="termtime_address_line_2_label" for="termtime_address_line_2">Address Line 2</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="termtime_address_line_2" id="termtime_address_line_2" value="" onFocus="reset_error_class('termtime_address_line_2_label');">
								</div>					
							</div>								
							
							<div class="form-group">
								<label class="control-label col-sm-3" name="termtime_town_city_label" id="termtime_town_city_label" for="termtime_town_city">Town / City</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="termtime_town_city" id="termtime_town_city" value="" onFocus="reset_error_class('termtime_town_city_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-3" name="termtime_county_label" id="termtime_county_label" for="termtime_county">County</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:50%;" type="text" name="termtime_county" id="termtime_county" value="" onFocus="reset_error_class('termtime_county_label');">
								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-3" id="termtime_postcode_label" name="termtime_postcode_label" for="termtime_postcode">Postcode</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input maxlength="4" size="4" type="text" name="termtime_postcode_part_1" id="termtime_postcode_part_1" value="" onFocus="reset_error_class('termtime_postcode_label');">
									<input maxlength="4" size="4" type="text" name="termtime_postcode_part_2" id="termtime_postcode_part_2" value="" onFocus="reset_error_class('termtime_postcode_label');">

								</div>					
							</div>	
						</form>
					</div>
				</div>
			</div>			
			<!-- Address and Contact Details end -->
			
			<!-- employment status start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseSix">
						<h4 class="panel-title" id="employment_status_heading">
							Employment Status	
						</h4>
					</a>
				</div>
				<div id="collapseSix" class="panel-collapse collapse in">
					<div class="panel-body">
						<form name="form_employment_status" id="form_employment_status" role="form" class="form-horizontal">

							<div class="form-group">
								<label class="control-label col-sm-3" name="employment_status_label" id="employment_status_label" for="employment_status">Please select</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="employment_status" id="employment_status" onChange="toggle_employment_status_questions();reset_error_class('employment_status_label');">
										<option value=''></option>
										<option value='Paid employment'>In paid employment</option>
										<option value='Unemployed and available to start work'>Unemployed and available to start work</option>
										<option value='Unemployed and NOT available to start work'>Unemployed and NOT available to start work</option>
										<option value='In Full time Education or Training'>In Full time Education or Training</option>							
										<option value='Retired'>Retired</option>	
									</select>
								</div>					
							</div>							

							<div id="paid_employment_questions" name="paid_employment_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">

								<div class="form-group">
									<label class="control-label col-sm-3" name="date_employment_started_label" id="date_employment_started_label" for="date_employment_started">Date employment started</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="date_employment_started_day" id="date_employment_started_day" onChange="reset_error_class('date_employment_started_label');">
											<option value=""></option>
											<option value="01">01</option>
											<option value="02">02</option>
											<option value="03">03</option>
											<option value="04">04</option>
											<option value="05">05</option>
											<option value="06">06</option>
											<option value="07">07</option>
											<option value="08">08</option>
											<option value="09">09</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">15</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20">20</option>
											<option value="21">21</option>
											<option value="22">22</option>
											<option value="23">23</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
											<option value="29">29</option>
											<option value="30">30</option>
											<option value="31">31</option>
										</select>

										<select name="date_employment_started_month" id="date_employment_started_month" onChange="reset_error_class('date_entry_uk_label');">
											<option value=""></option>
											<option value="01">January</option>
											<option value="02">February</option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>								
										
										<select id="date_employment_started_year" name="date_employment_started_year" value="" onChange="reset_error_class('date_entry_uk_label');">
											<option value=""></option>
											<option value="1945">1945</option>
											<option value="1946">1946</option>
											<option value="1947">1947</option>
											<option value="1948">1948</option>
											<option value="1949">1949</option>
											<option value="1950">1950</option>
											<option value="1951">1951</option>
											<option value="1952">1952</option>
											<option value="1953">1953</option>
											<option value="1954">1954</option>
											<option value="1955">1955</option>
											<option value="1956">1956</option>
											<option value="1957">1957</option>
											<option value="1958">1958</option>
											<option value="1959">1959</option>
											<option value="1960">1960</option>
											<option value="1961">1961</option>
											<option value="1962">1962</option>
											<option value="1963">1963</option>
											<option value="1964">1964</option>
											<option value="1965">1965</option>
											<option value="1966">1966</option>
											<option value="1967">1967</option>
											<option value="1968">1968</option>
											<option value="1969">1969</option>
											<option value="1970">1970</option>
											<option value="1971">1971</option>
											<option value="1972">1972</option>
											<option value="1973">1973</option>
											<option value="1974">1974</option>
											<option value="1975">1975</option>
											<option value="1976">1976</option>
											<option value="1977">1977</option>
											<option value="1978">1978</option>
											<option value="1979">1979</option>
											<option value="1980">1980</option>
											<option value="1981">1981</option>
											<option value="1982">1982</option>
											<option value="1983">1983</option>
											<option value="1984">1984</option>
											<option value="1985">1985</option>
											<option value="1986">1986</option>
											<option value="1987">1987</option>
											<option value="1988">1988</option>
											<option value="1989">1989</option>
											<option value="1990">1990</option>
											<option value="1991">1991</option>
											<option value="1992">1992</option>
											<option value="1993">1993</option>
											<option value="1994">1994</option>
											<option value="1995">1995</option>
											<option value="1996">1996</option>
											<option value="1997">1997</option>
											<option value="1998">1998</option>
											<option value="1999">1999</option>
											<option value="2000">2000</option>
											<option value="2001">2001</option>
											<option value="2002">2002</option>											
											<option value="2003">2003</option>
											<option value="2004">2004</option>											
											<option value="2005">2005</option>											
											<option value="2006">2006</option>											
											<option value="2007">2007</option>											
											<option value="2008">2008</option>		
											<option value="2009">2009</option>													
											<option value="2010">2010</option>													
											<option value="2011">2011</option>																								
											<option value="2012">2012</option>																																			
											<option value="2013">2013</option>	
											<option value="2014">2014</option>												
											<option value="2015">2015</option>
											<option value="2016">2016</option>
										</select>

									</div>					
								</div>					

								<div class="form-group">
									<label class="control-label col-sm-3" name="hours_per_week_employed_label" id="hours_per_week_employed_label" for="hours_per_week_employed">How many hours per week are you employed?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="hours_per_week_employed" id="hours_per_week_employed" onchange="reset_error_class('hours_per_week_employed_label');">
											<option value=''></option>	
											<option value='Less than 16 Hours'>Less than 16 Hours</option>	
											<option value='16-19 Hours'>16-19 Hours</option>	
											<option value='20 Hours or more'>20 Hours or more</option>	
										</select>
									</div>
								</div>

								<div class="form-group">
									<label class="control-label col-sm-3" name="self_employed_label" id="self_employed_label" for="self_employed">Are you self employed?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										Yes <input name="self_employed" id="self_employed" type="radio" value="Yes" onClick="reset_error_class('self_employed_label');">
										No <input name="self_employed" id="self_employed" type="radio" value="No" onClick="reset_error_class('self_employed_label');">
									</div>
								</div>					
					
							</div>
							
							<div id="retirement_questions" name="retirement_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">

								<div class="form-group">
									<label class="control-label col-sm-3" name="retirement_started_label" id="retirement_started_label" for="retirement_started">Date Retirement started</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="retirement_started_day" id="retirement_started_day" onChange="reset_error_class('retirement_started_label');">
											<option value=""></option>
											<option value="01">01</option>
											<option value="02">02</option>
											<option value="03">03</option>
											<option value="04">04</option>
											<option value="05">05</option>
											<option value="06">06</option>
											<option value="07">07</option>
											<option value="08">08</option>
											<option value="09">09</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">15</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20">20</option>
											<option value="21">21</option>
											<option value="22">22</option>
											<option value="23">23</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
											<option value="29">29</option>
											<option value="30">30</option>
											<option value="31">31</option>
										</select>

										<select name="retirement_started_month" id="retirement_started_month" onChange="reset_error_class('retirement_started_label');">
											<option value=""></option>
											<option value="01">January</option>
											<option value="02">February</option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>								
										
										<select id="retirement_started_year" name="retirement_started_year" value="" onChange="reset_error_class('retirement_started_label');">
											<option value=""></option>
											<option value="1945">1945</option>
											<option value="1946">1946</option>
											<option value="1947">1947</option>
											<option value="1948">1948</option>
											<option value="1949">1949</option>
											<option value="1950">1950</option>
											<option value="1951">1951</option>
											<option value="1952">1952</option>
											<option value="1953">1953</option>
											<option value="1954">1954</option>
											<option value="1955">1955</option>
											<option value="1956">1956</option>
											<option value="1957">1957</option>
											<option value="1958">1958</option>
											<option value="1959">1959</option>
											<option value="1960">1960</option>
											<option value="1961">1961</option>
											<option value="1962">1962</option>
											<option value="1963">1963</option>
											<option value="1964">1964</option>
											<option value="1965">1965</option>
											<option value="1966">1966</option>
											<option value="1967">1967</option>
											<option value="1968">1968</option>
											<option value="1969">1969</option>
											<option value="1970">1970</option>
											<option value="1971">1971</option>
											<option value="1972">1972</option>
											<option value="1973">1973</option>
											<option value="1974">1974</option>
											<option value="1975">1975</option>
											<option value="1976">1976</option>
											<option value="1977">1977</option>
											<option value="1978">1978</option>
											<option value="1979">1979</option>
											<option value="1980">1980</option>
											<option value="1981">1981</option>
											<option value="1982">1982</option>
											<option value="1983">1983</option>
											<option value="1984">1984</option>
											<option value="1985">1985</option>
											<option value="1986">1986</option>
											<option value="1987">1987</option>
											<option value="1988">1988</option>
											<option value="1989">1989</option>
											<option value="1990">1990</option>
											<option value="1991">1991</option>
											<option value="1992">1992</option>
											<option value="1993">1993</option>
											<option value="1994">1994</option>
											<option value="1995">1995</option>
											<option value="1996">1996</option>
											<option value="1997">1997</option>
											<option value="1998">1998</option>
											<option value="1999">1999</option>
											<option value="2000">2000</option>
											<option value="2001">2001</option>
											<option value="2002">2002</option>											
											<option value="2003">2003</option>
											<option value="2004">2004</option>											
											<option value="2005">2005</option>											
											<option value="2006">2006</option>											
											<option value="2007">2007</option>											
											<option value="2008">2008</option>		
											<option value="2009">2009</option>													
											<option value="2010">2010</option>													
											<option value="2011">2011</option>																								
											<option value="2012">2012</option>																																			
											<option value="2013">2013</option>	
											<option value="2014">2014</option>												
											<option value="2015">2015</option>
											<option value="2016">2016</option>											
										</select>

									</div>					
								</div>	<!-- end formgroup -->				


								<div class="form-group">
									<label class="control-label col-sm-3" name="retirement_length_label" id="retirement_length_label" for="retirement_length">For how long have you been Retired?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="retirement_length" id="retirement_length" onChange="reset_error_class('retirement_length_label');">
											<option value=''></option>	
											<option value='Less than 6 months'>Less than 6 months</option>	
											<option value='6-11 months'>6-11 months</option>	
											<option value='12-23 months'>12-23 months</option>	
											<option value='24-35 months'>24-35 months</option>	
											<option value='36 months +'>36 months +</option>
										</select>
									</div>					
								</div>	<!-- end formgroup -->		

							</div>
							
							<div id="unemployment_questions" name="unemployment_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">
							
								<div class="form-group">
									<label class="control-label col-sm-3" name="unemployment_started_label" id="unemployment_started_label" for="unemployment_started_day">Date Unemployment started</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="unemployment_started_day" id="unemployment_started_day" onChange="reset_error_class('unemployment_started_label');">
											<option value=""></option>
											<option value="01">01</option>
											<option value="02">02</option>
											<option value="03">03</option>
											<option value="04">04</option>
											<option value="05">05</option>
											<option value="06">06</option>
											<option value="07">07</option>
											<option value="08">08</option>
											<option value="09">09</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">15</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20">20</option>
											<option value="21">21</option>
											<option value="22">22</option>
											<option value="23">23</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
											<option value="29">29</option>
											<option value="30">30</option>
											<option value="31">31</option>
										</select>

										<select name="unemployment_started_month" id="unemployment_started_month" onChange="reset_error_class('unemployment_started_label');">
											<option value=""></option>
											<option value="01">January</option>
											<option value="02">February</option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>								
										
										<select id="unemployment_started_year" name="unemployment_started_year" value="" onChange="reset_error_class('unemployment_started_label');">
											<option value=""></option>
											<option value="1945">1945</option>
											<option value="1946">1946</option>
											<option value="1947">1947</option>
											<option value="1948">1948</option>
											<option value="1949">1949</option>
											<option value="1950">1950</option>
											<option value="1951">1951</option>
											<option value="1952">1952</option>
											<option value="1953">1953</option>
											<option value="1954">1954</option>
											<option value="1955">1955</option>
											<option value="1956">1956</option>
											<option value="1957">1957</option>
											<option value="1958">1958</option>
											<option value="1959">1959</option>
											<option value="1960">1960</option>
											<option value="1961">1961</option>
											<option value="1962">1962</option>
											<option value="1963">1963</option>
											<option value="1964">1964</option>
											<option value="1965">1965</option>
											<option value="1966">1966</option>
											<option value="1967">1967</option>
											<option value="1968">1968</option>
											<option value="1969">1969</option>
											<option value="1970">1970</option>
											<option value="1971">1971</option>
											<option value="1972">1972</option>
											<option value="1973">1973</option>
											<option value="1974">1974</option>
											<option value="1975">1975</option>
											<option value="1976">1976</option>
											<option value="1977">1977</option>
											<option value="1978">1978</option>
											<option value="1979">1979</option>
											<option value="1980">1980</option>
											<option value="1981">1981</option>
											<option value="1982">1982</option>
											<option value="1983">1983</option>
											<option value="1984">1984</option>
											<option value="1985">1985</option>
											<option value="1986">1986</option>
											<option value="1987">1987</option>
											<option value="1988">1988</option>
											<option value="1989">1989</option>
											<option value="1990">1990</option>
											<option value="1991">1991</option>
											<option value="1992">1992</option>
											<option value="1993">1993</option>
											<option value="1994">1994</option>
											<option value="1995">1995</option>
											<option value="1996">1996</option>
											<option value="1997">1997</option>
											<option value="1998">1998</option>
											<option value="1999">1999</option>
											<option value="2000">2000</option>
											<option value="2001">2001</option>
											<option value="2002">2002</option>											
											<option value="2003">2003</option>
											<option value="2004">2004</option>											
											<option value="2005">2005</option>											
											<option value="2006">2006</option>											
											<option value="2007">2007</option>											
											<option value="2008">2008</option>		
											<option value="2009">2009</option>													
											<option value="2010">2010</option>													
											<option value="2011">2011</option>																								
											<option value="2012">2012</option>																																			
											<option value="2013">2013</option>	
											<option value="2014">2014</option>												
											<option value="2015">2015</option>
											<option value="2016">2016</option>
										</select>
									</div>					
								</div>	<!-- end formgroup -->						

								<div class="form-group">
									<label class="control-label col-sm-3" name="unemployment_length_label" id="unemployment_length_label" for="unemployment_length">For how long have you been Unemployed?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="unemployment_length" id="unemployment_length" onChange="reset_error_class('unemployment_length_label');">
											<option value=''></option>	
											<option value='Less than 6 months'>Less than 6 months</option>	
											<option value='6-11 months'>6-11 months</option>	
											<option value='12-23 months'>12-23 months</option>	
											<option value='24-35 months'>24-35 months</option>	
											<option value='36 months +'>36 months +</option>
										</select>
									</div>					
								</div>	<!-- end formgroup -->

								<div class="form-group">
								<label class="control-label col-sm-3" id="receipt_jsa_label" name="receipt_jsa_label" for="receipt_jsa">Are you in receipt of JSA?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="receipt_jsa" name="receipt_jsa">
									</div>					
								</div>					

								<div class="form-group">
								<label class="control-label col-sm-3" id="receipt_esa_label" name="receipt_esa_label" for="receipt_esa">Are you in receipt of ESA,WRAG?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="receipt_esa" name="receipt_esa">
									</div>					
								</div>						

								<div class="form-group">
								<label class="control-label col-sm-3" id="receipt_universal_credit_label" name="receipt_universal_credit_label" for="receipt_universal_credit">Are you in receipt of Universal Credit?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="receipt_universal_credit" name="receipt_universal_credit">
									</div>					
								</div>	

								<div class="form-group">
								<label class="control-label col-sm-3" id="other_benefit_label" name="other_benefit_label" for="other_benefit_label">Any other</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="text" id="receipt_other_benefit" name="receipt_other_benefit">
									</div>					
								</div>
								
							</div>
							

						</form>
					</div>
				</div>
			</div>			
			<!-- employment status end -->			
			
			<!-- Residency and Nationality start -->			
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseFour">
						<h4 class="panel-title" id="residency_nationality_heading">
							Residency and Nationality
						</h4>
					</a>
				</div>
				<div id="collapseFour" class="panel-collapse collapse in">
					<div class="panel-body">


					<form name="form_residency_details" id="form_residency_details" role="form" class="form-horizontal">

						<div class="form-group">
							<label class="control-label col-sm-3" name="language_label" id="language_label" for="language">Is English your first language?</label>
							<div class="col-sm-9" style="margin-top:5px;">
								Yes <input name="language" id="language" type="radio" value="Yes" onClick="toggle_language_questions();reset_error_class('language_label');">
								No <input name="language" id="language" type="radio" value="No" onClick="toggle_language_questions();reset_error_class('language_label');">
							</div>					
						</div>	

						
						<div id="language_questions" name="language_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">
							<div class="form-group">
								<label class="control-label col-sm-3" name="other_language_label" id="other_language_label" for="other_language">Please state your first language</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:200px;" type="text" name="other_language" id="other_language" value="" onFocus="reset_error_class('other_language_label');">
								</div>					
							</div>	
						</div>

						<div class="form-group">
							<label class="control-label col-sm-3" id="british_citizen_label" name="british_citizen_label" for="british_citizen">Are you a British Citizen?</label>
							<div class="col-sm-9" style="margin-top:5px;">
								Yes <input name="british_citizen" id="british_citizen" type="radio" value="Yes" onClick="reset_error_class('british_citizen_label');">
								No <input name="british_citizen" id="british_citizen" type="radio" value="No" onClick="reset_error_class('british_citizen_label');">
							</div>					
						</div>				
						
						<div class="form-group">
							<label class="control-label col-sm-3" id="passport_number_label" name="passport_number_label" for="passport_number">Passport number (if owned)</label>
							<div class="col-sm-9" style="margin-top:5px;">
								<input maxlength="11" size="11" type="text" name="passport_number" id="passport_number" value="" onFocus="reset_error_class('passport_number_label');">
							</div>					
						</div>				
						
						<div class="form-group">
							<label class="control-label col-sm-3" id="nationality_label" name="nationality_label"  for="nationality">What is your Nationality?</label>
							<div class="col-sm-9" style="margin-top:5px;">
								<select name="nationality" id="nationality" onChange="reset_error_class('nationality_label');" >
									<option value="">-- select one --</option>
									<option value="GB">UNITED KINGDOM (GREAT BRITAIN)</option>
									<option value="AF">AFGHANISTAN</option>
									<option value="XQ">AFRICA NOT OTHERWISE SPECIFIED</option>
									<option value="AX">ALAND ISLANDS</option>
									<option value="AL">ALBANIA</option>
									<option value="DZ">ALGERIA</option>
									<option value="AS">AMERICAN SAMOA</option>
									<option value="AD">ANDORRA</option>
									<option value="AO">ANGOLA</option>
									<option value="AI">ANGUILLA</option>
									<option value="AQ">ANTARCTICA</option>
									<option value="XX">ANTARCTICA AND OCEANIA NOT OTHERWISE SPE</option>
									<option value="AG">ANTIGUA AND BARBUDA</option>
									<option value="AR">ARGENTINA</option>
									<option value="AM">ARMENIA</option>
									<option value="AW">ARUBA</option>
									<option value="XS">ASIA (EXCEPT MIDDLE EAST) NOT OTHERWISE </option>
									<option value="AU">AUSTRALIA</option>
									<option value="AT">AUSTRIA</option>
									<option value="AZ">AZERBAIJAN</option>
									<option value="BS">BAHAMAS, THE</option>
									<option value="BH">BAHRAIN</option>
									<option value="BD">BANGLADESH</option>
									<option value="BB">BARBADOS</option>
									<option value="BY">BELARUS</option>
									<option value="BE">BELGIUM</option>
									<option value="BZ">BELIZE</option>
									<option value="BJ">BENIN</option>
									<option value="BM">BERMUDA</option>
									<option value="BT">BHUTAN</option>
									<option value="BO">BOLIVIA</option>
									<option value="BQ">BONAIRE, SINT EUSTATIUS AND SABA (NOT FO</option>
									<option value="BA">BOSNIA AND HERZEGOVINA</option>
									<option value="BW">BOTSWANA</option>
									<option value="BV">BOUVET ISLAND</option>
									<option value="BR">BRAZIL</option>
									<option value="IO">BRITISH INDIAN OCEAN TERRITORY</option>
									<option value="VG">BRITISH VIRGIN ISLANDS</option>
									<option value="BN">BRUNEI</option>
									<option value="BG">BULGARIA</option>
									<option value="BF">BURKINA</option>
									<option value="MM">BURMA</option>
									<option value="BI">BURUNDI</option>
									<option value="KH">CAMBODIA</option>
									<option value="CM">CAMEROON</option>
									<option value="CA">CANADA</option>
									<option value="IC">CANARY ISLANDS</option>
									<option value="CV">CAPE VERDE</option>
									<option value="XW">CARIBBEAN NOT OTHERWISE SPECIFIED</option>
									<option value="KY">CAYMAN ISLANDS</option>
									<option value="CF">CENTRAL AFRICAN REPUBLIC</option>
									<option value="XU">CENTRAL AMERICA NOT OTHERWISE SPECIFIED</option>
									<option value="TD">CHAD</option>
									<option value="XK">CHANNEL ISLANDS</option>
									<option value="XL">CHANNEL ISLANDS NOT OTHERWISE SPECIFIED</option>
									<option value="CL">CHILE</option>
									<option value="CN">CHINA</option>
									<option value="TW">CHINA (TAIWAN)</option>
									<option value="CX">CHRISTMAS ISLAND</option>
									<option value="CC">COCOS (KEELING) ISLANDS</option>
									<option value="CO">COLOMBIA</option>
									<option value="KM">COMOROS</option>
									<option value="CG">CONGO</option>
									<option value="CD">CONGO (DEMOCRATIC REPUBLIC)</option>
									<option value="CK">COOK ISLANDS</option>
									<option value="CR">COSTA RICA</option>
									<option value="HR">CROATIA</option>
									<option value="CU">CUBA</option>
									<option value="CW">CURACAO (NOT FOR HE)</option>
									<option value="CY">CYPRUS</option>
									<option value="XA">CYPRUS (EUROPEAN UNION)</option>
									<option value="XB">CYPRUS (NON-EUROPEAN UNION)</option>
									<option value="XC">CYPRUS NOT OTHERWISE SPECIFIED</option>
									<option value="CZ">CZECH REPUBLIC</option>
									<option value="XM">CZECHOSLOVAKIA NOT OTHERWISE SPECIFIED</option>
									<option value="DK">DENMARK</option>
									<option value="DJ">DJIBOUTI</option>
									<option value="DM">DOMINICA</option>
									<option value="DO">DOMINICAN REPUBLIC</option>
									<option value="TL">EAST TIMOR</option>
									<option value="EC">ECUADOR</option>
									<option value="EG">EGYPT</option>
									<option value="SV">EL SALVADOR</option>
									<option value="XF">ENGLAND</option>
									<option value="GQ">EQUATORIAL GUINEA</option>
									<option value="ER">ERITREA</option>
									<option value="EE">ESTONIA</option>
									<option value="ET">ETHIOPIA</option>
									<option value="XP">EUROPE NOT OTHERWISE SPECIFIED</option>
									<option value="FK">FALKLAND ISLANDS</option>
									<option value="FO">FAROE ISLANDS</option>
									<option value="FJ">FIJI</option>
									<option value="FI">FINLAND</option>
									<option value="FR">FRANCE</option>
									<option value="GF">FRENCH GUIANA</option>
									<option value="PF">FRENCH POLYNESIA</option>
									<option value="TF">FRENCH SOUTHERN TERRITORIES</option>
									<option value="GA">GABON</option>
									<option value="GM">GAMBIA, THE</option>
									<option value="GE">GEORGIA</option>
									<option value="DE">GERMANY</option>
									<option value="GH">GHANA</option>
									<option value="GI">GIBRALTAR</option>
									<option value="GR">GREECE</option>
									<option value="GL">GREENLAND</option>
									<option value="GD">GRENADA</option>
									<option value="GP">GUADELOUPE</option>
									<option value="GU">GUAM</option>
									<option value="GT">GUATEMALA</option>
									<option value="GG">GUERNSEY</option>
									<option value="GN">GUINEA</option>
									<option value="GW">GUINEA-BISSAU</option>
									<option value="GY">GUYANA</option>
									<option value="HT">HAITI</option>
									<option value="HM">HEARD ISLAND AND MCDONALD ISLANDS</option>
									<option value="HN">HONDURAS</option>
									<option value="HK">HONG KONG (SPECIAL ADMINISTRATIVE REGION</option>
									<option value="HU">HUNGARY</option>
									<option value="IS">ICELAND</option>
									<option value="IN">INDIA</option>
									<option value="ID">INDONESIA</option>
									<option value="IR">IRAN</option>
									<option value="IQ">IRAQ</option>
									<option value="IE">IRELAND</option>
									<option value="IM">ISLE OF MAN</option>
									<option value="IL">ISRAEL</option>
									<option value="IT">ITALY</option>
									<option value="CI">IVORY COAST</option>
									<option value="JM">JAMAICA</option>
									<option value="JP">JAPAN</option>
									<option value="JE">JERSEY</option>
									<option value="JO">JORDAN</option>
									<option value="KZ">KAZAKHSTAN</option>
									<option value="KE">KENYA</option>
									<option value="KI">KIRIBATI</option>
									<option value="KP">KOREA (NORTH)</option>
									<option value="KR">KOREA (SOUTH)</option>
									<option value="QO">KOSOVO</option>
									<option value="KW">KUWAIT</option>
									<option value="KG">KYRGYZSTAN</option>
									<option value="LA">LAOS</option>
									<option value="LV">LATVIA</option>
									<option value="LB">LEBANON</option>
									<option value="LS">LESOTHO</option>
									<option value="LR">LIBERIA</option>
									<option value="LY">LIBYA</option>
									<option value="LI">LIECHTENSTEIN</option>
									<option value="LT">LITHUANIA</option>
									<option value="LU">LUXEMBOURG</option>
									<option value="MO">MACAO (SPECIAL ADMINISTRATIVE REGION OF </option>
									<option value="MK">MACEDONIA</option>
									<option value="MG">MADAGASCAR</option>
									<option value="MW">MALAWI</option>
									<option value="MY">MALAYSIA</option>
									<option value="MV">MALDIVES</option>
									<option value="ML">MALI</option>
									<option value="MT">MALTA</option>
									<option value="MH">MARSHALL ISLANDS</option>
									<option value="MQ">MARTINIQUE</option>
									<option value="MR">MAURITANIA</option>
									<option value="MU">MAURITIUS</option>
									<option value="YT">MAYOTTE</option>
									<option value="MX">MEXICO</option>
									<option value="FM">MICRONESIA</option>
									<option value="XR">MIDDLE EAST NOT OTHERWISE SPECIFIED</option>
									<option value="MD">MOLDOVA</option>
									<option value="MC">MONACO</option>
									<option value="MN">MONGOLIA</option>
									<option value="ME">MONTENEGRO</option>
									<option value="MS">MONTSERRAT</option>
									<option value="MA">MOROCCO</option>
									<option value="MZ">MOZAMBIQUE</option>
									<option value="NA">NAMIBIA</option>
									<option value="NR">NAURU</option>
									<option value="NP">NEPAL</option>
									<option value="NL">NETHERLANDS</option>
									<option value="AN">NETHERLANDS ANTILLES</option>
									<option value="NC">NEW CALEDONIA (NOT FOR HE)</option>
									<option value="NZ">NEW ZEALAND</option>
									<option value="NI">NICARAGUA</option>
									<option value="NE">NIGER</option>
									<option value="NG">NIGERIA</option>
									<option value="NU">NIUE</option>
									<option value="NF">NORFOLK ISLAND</option>
									<option value="XT">NORTH AMERICA NOT OTHERWISE SPECIFIED</option>
									<option value="XG">NORTHERN IRELAND</option>
									<option value="MP">NORTHERN MARIANA ISLANDS</option>
									<option value="NO">NORWAY</option>
									<option value="ZZ">NOT KNOWN</option>
									<option value="OM">OMAN</option>
									<option value="PK">PAKISTAN</option>
									<option value="PW">PALAU</option>
									<option value="PA">PANAMA</option>
									<option value="PG">PAPUA NEW GUINEA</option>
									<option value="PY">PARAGUAY</option>
									<option value="PE">PERU</option>
									<option value="PH">PHILIPPINES</option>
									<option value="PN">PITCAIRN, HENDERSON, DUCIE AND OENO ISLA</option>
									<option value="PL">POLAND</option>
									<option value="PT">PORTUGAL</option>
									<option value="PR">PUERTO RICO</option>
									<option value="QA">QATAR</option>
									<option value="RE">REUNION</option>
									<option value="RO">ROMANIA</option>
									<option value="RU">RUSSIA</option>
									<option value="RW">RWANDA</option>
									<option value="MF">SAINT MARTIN (FRENCH PART) (NOT FOR HE)</option>
									<option value="WS">SAMOA</option>
									<option value="SM">SAN MARINO</option>
									<option value="ST">SAO TOME AND PRINCIPE</option>
									<option value="SA">SAUDI ARABIA</option>
									<option value="XH">SCOTLAND</option>
									<option value="SN">SENEGAL</option>
									<option value="RS">SERBIA</option>
									<option value="CS">SERBIA AND MONTENEGRO</option>
									<option value="QN">SERBIA AND MONTENEGRO NOT OTHERWISE SPEC</option>
									<option value="SC">SEYCHELLES</option>
									<option value="SL">SIERRA LEONE</option>
									<option value="SG">SINGAPORE</option>
									<option value="SX">SINT MAARTEN (DUTCH PART) (NOT FOR HE)</option>
									<option value="SK">SLOVAKIA</option>
									<option value="SI">SLOVENIA</option>
									<option value="SB">SOLOMON ISLANDS</option>
									<option value="SO">SOMALIA</option>
									<option value="ZA">SOUTH AFRICA</option>
									<option value="XV">SOUTH AMERICA NOT OTHERWISE SPECIFIED</option>
									<option value="GS">SOUTH GEORGIA AND THE SOUTH SANDWICH ISL</option>
									<option value="SS">SOUTH SUDAN</option>
									<option value="ES">SPAIN</option>
									<option value="XD">SPAIN (EXCEPT CANARY ISLANDS)</option>
									<option value="XE">SPAIN NOT OTHERWISE SPECIFIED</option>
									<option value="LK">SRI LANKA</option>
									<option value="BL">ST BARTHELEMY (NOT FOR HE)</option>
									<option value="SH">ST HELENA</option>
									<option value="KN">ST KITTS AND NEVIS</option>
									<option value="LC">ST LUCIA</option>
									<option value="PM">ST PIERRE AND MIQUELON</option>
									<option value="VC">ST VINCENT AND THE GRENADINES</option>
									<option value="SD">SUDAN</option>
									<option value="SR">SURINAM</option>
									<option value="SJ">SVALBARD AND JAN MAYEN</option>
									<option value="SZ">SWAZILAND</option>
									<option value="SE">SWEDEN</option>
									<option value="CH">SWITZERLAND</option>
									<option value="SY">SYRIA</option>
									<option value="TJ">TAJIKISTAN</option>
									<option value="TZ">TANZANIA</option>
									<option value="TH">THAILAND</option>
									<option value="TG">TOGO</option>
									<option value="TK">TOKELAU</option>
									<option value="TO">TONGA</option>
									<option value="TT">TRINIDAD AND TOBAGO</option>
									<option value="TN">TUNISIA</option>
									<option value="TR">TURKEY</option>
									<option value="TM">TURKMENISTAN</option>
									<option value="TC">TURKS AND CAICOS ISLANDS</option>
									<option value="TV">TUVALU</option>
									<option value="UG">UGANDA</option>
									<option value="UA">UKRAINE</option>
									<option value="XN">UNION OF SOVIET SOCIALIST REPUBLICS NOT </option>
									<option value="AE">UNITED ARAB EMIRATES</option>
									<option value="GB">UNITED KINGDOM</option>
									<option value="XJ">UNITED KINGDOM NOT OTHERWISE SPECIFIED</option>
									<option value="US">UNITED STATES</option>
									<option value="UM">UNITED STATES MINOR OUTLYING ISLANDS</option>
									<option value="VI">UNITED STATES VIRGIN ISLANDS</option>
									<option value="UY">URUGUAY</option>
									<option value="UZ">UZBEKISTAN</option>
									<option value="VU">VANUATU</option>
									<option value="VA">VATICAN CITY</option>
									<option value="VE">VENEZUELA</option>
									<option value="VN">VIETNAM</option>
									<option value="XI">WALES</option>
									<option value="WF">WALLIS AND FUTUNA</option>
									<option value="PS">WEST BANK (INCLUDING EAST JERUSALEM) AND</option>
									<option value="EH">WESTERN SAHARA</option>
									<option value="YE">YEMEN</option>
									<option value="XO">YUGOSLAVIA NOT OTHERWISE SPECIFIED</option>
									<option value="ZM">Zambia</option>
									<option value="ZW">ZIMBABWE</option>
								</select>
							</div>					
						</div>

						<div class="form-group">
							<label class="control-label col-sm-3" id="uk_last_three_years_label" name="uk_last_three_years_label" for="uk_last_three_years">Have you lived in the UK/EU continuously for the last three years?</label>
							<div class="col-sm-9" style="margin-top:5px;">
								Yes <input name="uk_last_three_years" id="uk_last_three_years" type="radio" value="Yes" onClick="toggle_date_entry_questions();reset_error_class('uk_last_three_years_label');">
								No <input name="uk_last_three_years" id="uk_last_three_years" type="radio" value="No" onClick="toggle_date_entry_questions();reset_error_class('uk_last_three_years_label');">
							</div>					
						</div>				

						<div id="date_entry_uk_questions" name="date_entry_uk_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">
							<div class="form-group" >
								<label class="control-label col-sm-3" id="date_entry_uk_label" name="date_entry_uk_label" for="date_entry_uk_day">If 'No' date of entry to UK</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<select name="date_entry_uk_day" id="date_entry_uk_day" onChange="reset_error_class('date_entry_uk_label');">
										<option value=""></option>
										<option value="01">01</option>
										<option value="02">02</option>
										<option value="03">03</option>
										<option value="04">04</option>
										<option value="05">05</option>
										<option value="06">06</option>
										<option value="07">07</option>
										<option value="08">08</option>
										<option value="09">09</option>
										<option value="10">10</option>
										<option value="11">11</option>
										<option value="12">12</option>
										<option value="13">13</option>
										<option value="14">14</option>
										<option value="15">15</option>
										<option value="16">16</option>
										<option value="17">17</option>
										<option value="18">18</option>
										<option value="19">19</option>
										<option value="20">20</option>
										<option value="21">21</option>
										<option value="22">22</option>
										<option value="23">23</option>
										<option value="24">24</option>
										<option value="25">25</option>
										<option value="26">26</option>
										<option value="27">27</option>
										<option value="28">28</option>
										<option value="29">29</option>
										<option value="30">30</option>
										<option value="31">31</option>
									</select>

									<select name="date_entry_uk_month" id="date_entry_uk_month" onChange="reset_error_class('date_entry_uk_label');">
										<option value=""></option>
										<option value="01">January</option>
										<option value="02">February</option>
										<option value="03">March</option>
										<option value="04">April</option>
										<option value="05">May</option>
										<option value="06">June</option>
										<option value="07">July</option>
										<option value="08">August</option>
										<option value="09">September</option>
										<option value="10">October</option>
										<option value="11">November</option>
										<option value="12">December</option>
									</select>								
									
									<select id="date_entry_uk_year" name="date_entry_uk_year" value="" onChange="reset_error_class('date_entry_uk_label');">
										<option value=""></option>
										<option value="1945">1945</option>
										<option value="1946">1946</option>
										<option value="1947">1947</option>
										<option value="1948">1948</option>
										<option value="1949">1949</option>
										<option value="1950">1950</option>
										<option value="1951">1951</option>
										<option value="1952">1952</option>
										<option value="1953">1953</option>
										<option value="1954">1954</option>
										<option value="1955">1955</option>
										<option value="1956">1956</option>
										<option value="1957">1957</option>
										<option value="1958">1958</option>
										<option value="1959">1959</option>
										<option value="1960">1960</option>
										<option value="1961">1961</option>
										<option value="1962">1962</option>
										<option value="1963">1963</option>
										<option value="1964">1964</option>
										<option value="1965">1965</option>
										<option value="1966">1966</option>
										<option value="1967">1967</option>
										<option value="1968">1968</option>
										<option value="1969">1969</option>
										<option value="1970">1970</option>
										<option value="1971">1971</option>
										<option value="1972">1972</option>
										<option value="1973">1973</option>
										<option value="1974">1974</option>
										<option value="1975">1975</option>
										<option value="1976">1976</option>
										<option value="1977">1977</option>
										<option value="1978">1978</option>
										<option value="1979">1979</option>
										<option value="1980">1980</option>
										<option value="1981">1981</option>
										<option value="1982">1982</option>
										<option value="1983">1983</option>
										<option value="1984">1984</option>
										<option value="1985">1985</option>
										<option value="1986">1986</option>
										<option value="1987">1987</option>
										<option value="1988">1988</option>
										<option value="1989">1989</option>
										<option value="1990">1990</option>
										<option value="1991">1991</option>
										<option value="1992">1992</option>
										<option value="1993">1993</option>
										<option value="1994">1994</option>
										<option value="1995">1995</option>
										<option value="1996">1996</option>
										<option value="1997">1997</option>
										<option value="1998">1998</option>
										<option value="1999">1999</option>
										<option value="2000">2000</option>
										<option value="2001">2001</option>
										<option value="2002">2002</option>											
										<option value="2003">2003</option>
										<option value="2004">2004</option>											
										<option value="2005">2005</option>											
										<option value="2006">2006</option>											
										<option value="2007">2007</option>											
										<option value="2008">2008</option>		
										<option value="2009">2009</option>													
										<option value="2010">2010</option>													
										<option value="2011">2011</option>																								
										<option value="2012">2012</option>																																			
										<option value="2013">2013</option>	
										<option value="2014">2014</option>												
										<option value="2015">2015</option>
										<option value="2016">2016</option>
									</select>

								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-3" id="live_before_uk_label" name="live_before_uk_label" for="live_before_uk">Where did you live before coming to the UK?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input style="width:200px;" type="text" name="live_before_uk" id="live_before_uk" value="" onFocus="reset_error_class('live_before_uk_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label style="font-weight:normal;" class="control-label col-sm-3" >Which of the following do you have?</label>
								<div class="col-sm-9" style="margin-top:5px;">
								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-3" for="indefinite_leave">Indefinite Leave</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="checkbox" id="indefinite_leave" name="indefinite_leave">
								</div>					
							</div>						

							<div class="form-group">
								<label class="control-label col-sm-3" for="right_of_abode">Right of abode</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="checkbox" id="right_of_abode" name="right_of_abode">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-3" for="limited_leave">Limited Leave to Enter/Remain</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="checkbox" id="limited_leave" name="limited_leave">
								</div>					
							</div>						

							<div class="form-group">
								<label class="control-label col-sm-3" for="any_other_visa">Any other type of visa</label>
								<div class="col-sm-9" style="margin-top:5px;">
									<input type="checkbox" id="any_other_visa" name="any_other_visa">
								</div>					
							</div>						
							
							<div class="form-group">
								<label class="control-label col-sm-3" id="student_visa_label" name="student_visa_label" for="student_visa">Are you on a student visa?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="student_visa" id="student_visa" type="radio" value="Yes" onClick="toggle_student_visa_questions();reset_error_class('student_visa_label');">
									No <input name="student_visa" id="student_visa" type="radio" value="No" onClick="toggle_student_visa_questions();reset_error_class('student_visa_label');">
								</div>					
							</div>

							<div id="student_visa_questions" name="student_visa_questions" style="display:none;background-color:#E4FDFF;padding-top:5px;padding-bottom:5px;margin-bottom:10px;">					
								<div class="form-group" >
									<label class="control-label col-sm-3" id="when_start_studies_label" name="when_start_studies_label" for="when_start_studies">If 'Yes', when did you start your studies?</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="when_start_studies_day" id="when_start_studies_day" onChange="reset_error_class('when_start_studies_label');">
											<option value=""></option>
											<option value="01">01</option>
											<option value="02">02</option>
											<option value="03">03</option>
											<option value="04">04</option>
											<option value="05">05</option>
											<option value="06">06</option>
											<option value="07">07</option>
											<option value="08">08</option>
											<option value="09">09</option>
											<option value="10">10</option>
											<option value="11">11</option>
											<option value="12">12</option>
											<option value="13">13</option>
											<option value="14">14</option>
											<option value="15">15</option>
											<option value="16">16</option>
											<option value="17">17</option>
											<option value="18">18</option>
											<option value="19">19</option>
											<option value="20">20</option>
											<option value="21">21</option>
											<option value="22">22</option>
											<option value="23">23</option>
											<option value="24">24</option>
											<option value="25">25</option>
											<option value="26">26</option>
											<option value="27">27</option>
											<option value="28">28</option>
											<option value="29">29</option>
											<option value="30">30</option>
											<option value="31">31</option>
										</select>

										<select name="when_start_studies_month" id="when_start_studies_month" onChange="reset_error_class('when_start_studies_label');">
											<option value=""></option>
											<option value="01">January</option>
											<option value="02">February</option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>								
										
										<select id="when_start_studies_year" name="when_start_studies_year" value="" onChange="reset_error_class('when_start_studies_label');">
											<option value=""></option>
											<option value="1945">1945</option>
											<option value="1946">1946</option>
											<option value="1947">1947</option>
											<option value="1948">1948</option>
											<option value="1949">1949</option>
											<option value="1950">1950</option>
											<option value="1951">1951</option>
											<option value="1952">1952</option>
											<option value="1953">1953</option>
											<option value="1954">1954</option>
											<option value="1955">1955</option>
											<option value="1956">1956</option>
											<option value="1957">1957</option>
											<option value="1958">1958</option>
											<option value="1959">1959</option>
											<option value="1960">1960</option>
											<option value="1961">1961</option>
											<option value="1962">1962</option>
											<option value="1963">1963</option>
											<option value="1964">1964</option>
											<option value="1965">1965</option>
											<option value="1966">1966</option>
											<option value="1967">1967</option>
											<option value="1968">1968</option>
											<option value="1969">1969</option>
											<option value="1970">1970</option>
											<option value="1971">1971</option>
											<option value="1972">1972</option>
											<option value="1973">1973</option>
											<option value="1974">1974</option>
											<option value="1975">1975</option>
											<option value="1976">1976</option>
											<option value="1977">1977</option>
											<option value="1978">1978</option>
											<option value="1979">1979</option>
											<option value="1980">1980</option>
											<option value="1981">1981</option>
											<option value="1982">1982</option>
											<option value="1983">1983</option>
											<option value="1984">1984</option>
											<option value="1985">1985</option>
											<option value="1986">1986</option>
											<option value="1987">1987</option>
											<option value="1988">1988</option>
											<option value="1989">1989</option>
											<option value="1990">1990</option>
											<option value="1991">1991</option>
											<option value="1992">1992</option>
											<option value="1993">1993</option>
											<option value="1994">1994</option>
											<option value="1995">1995</option>
											<option value="1996">1996</option>
											<option value="1997">1997</option>
											<option value="1998">1998</option>
											<option value="1999">1999</option>
											<option value="2000">2000</option>
											<option value="2001">2001</option>
											<option value="2002">2002</option>											
											<option value="2003">2003</option>
											<option value="2004">2004</option>											
											<option value="2005">2005</option>											
											<option value="2006">2006</option>											
											<option value="2007">2007</option>											
											<option value="2008">2008</option>		
											<option value="2009">2009</option>													
											<option value="2010">2010</option>													
											<option value="2011">2011</option>																								
											<option value="2012">2012</option>																																			
											<option value="2013">2013</option>	
											<option value="2014">2014</option>												
											<option value="2015">2015</option>
											<option value="2016">2016</option>
										</select>
									</div>					
								</div>	
							</div>

							<div class="form-group">
								<label class="control-label col-sm-3" id="refugee_label" name="refugee_label" for="refugee">Are you a refugee?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="refugee" id="refugee" type="radio" value="Yes" onClick="reset_error_class('refugee_label');">
									No <input name="refugee" id="refugee" type="radio" value="No" onClick="reset_error_class('refugee_label');">
								</div>					
							</div>
							
							<div class="form-group">
								<label class="control-label col-sm-3" id="asylum_seeker_label" name="asylum_seeker_label" for="asylum_seeker">Are you an asylum seeker?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="asylum_seeker" id="asylum_seeker" type="radio" value="Yes" onClick="toggle_asylum_questions();reset_error_class('asylum_seeker_label');">
									No <input name="asylum_seeker" id="asylum_seeker" type="radio" value="No" onClick="toggle_asylum_questions();reset_error_class('asylum_seeker_label');">
								</div>					
							</div>			

							<div id="asylum_questions" name="asylum_questions" style="display:none;background-color:#E4FDFF;padding-top:5px;padding-bottom:5px;margin-bottom:10px;">					
								
								<div class="form-group">
									<label style="font-weight:normal;" class="control-label col-sm-3" >If 'Yes', do you have:</label>
									<div class="col-sm-9" style="margin-top:5px;">
									</div>					
								</div>	
								
								<div class="form-group">
									<label class="control-label col-sm-3" for="humanitarian_protection">Humanitarian Protection</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="humanitarian_protection" name="humanitarian_protection">
									</div>					
								</div>	

								<div class="form-group">
									<label class="control-label col-sm-3" for="discretionary_leave">Discretionary Leave</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="discretionary_leave" name="discretionary_leave">
									</div>					
								</div>						

								<div class="form-group">
									<label class="control-label col-sm-3" for="exceptional_leave">Exceptional leave to enter or remain</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="exceptional_leave" name="exceptional_leave">
									</div>					
								</div>

								<div class="form-group">
									<label class="control-label col-sm-3" for="no_limit_on_stay">No limit on your stay</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="no_limit_on_stay" name="no_limit_on_stay">
									</div>					
								</div>

								<div class="form-group">
									<label class="control-label col-sm-3" id="asylum_reason_other_label" name="asylum_reason_other_label" for="asylum_reason_other">Other</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<input type="checkbox" id="other_asylum_reason" name="other_asylum_reason">
									</div>					
								</div>
							</div><!-- end asylum questions -->
							
						</div>

					</form>

					</div>
				</div>
			</div>
			<!-- Residency and Nationality end -->

			<!-- Personal Statement start -->			
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapsePersonalStatement">
						<h4 class="panel-title" id="personal_statement_heading">
							Personal Statement
						</h4>
					</a>
				</div>
				<div id="collapsePersonalStatement" class="panel-collapse collapse in">

					<div class="panel-body">

						<form name="form_personal_statement" id="form_personal_statement" role="form" class="form-horizontal">

							<div class="form-group" style="padding:20px;">
								<label id="why_study_label" name="why_study_label" for="why_study">Personal Statement (max 500 characters)</label>
								<textarea onkeyup="update_character_count(event,'why_study');" onkeydown="update_character_count(event,'why_study');" onkeypress="update_character_count(event,'why_study');" onfocus="reset_error_class('why_study_label');" class="form-control" rows="5" id="why_study" name="why_study"><?php echo $oe['why_study']; ?></textarea>
								<div id="why_study_character_count_label">
									<span align="right" id="why_study_character_count">0</span> / 500 characters used
								</div>
							</div>


							<div class="form-group" style="padding:20px;">
								<label id="work_experience_label" name="work_experience_label" for="work_experience">Work History (max 500 characters)</label>
								<textarea onkeyup="update_character_count(event,'work_experience');" onkeydown="update_character_count(event,'work_experience');" onkeypress="update_character_count(event,'work_experience');" onfocus="reset_error_class('work_experience_label');" class="form-control" rows="5" name="work_experience" id="work_experience"><?php echo $oe['work_experience']; ?></textarea>
								<div id="work_experience_character_count_label">
									<span align="right" id="work_experience_character_count">0</span> / 500 characters used
								</div>
							</div>							

					
						</form>
				
					</div>
				</div>
			</div>
			<!-- end Personal Statement -->

			<!-- Quals being studied start -->			
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseQualsCurrentlyStudying">
						<h4 class="panel-title" id="quals_currently_studying_heading">
							Qualifications currently being studied
						</h4>
					</a>
				</div>
				<div id="collapseQualsCurrentlyStudying" class="panel-collapse collapse in">

					<div class="panel-body">

						<form name="form_quals_currently_studying" id="form_quals_currently_studying" role="form" class="form-horizontal">

							<div class="form-group">
								<label class="control-label col-sm-3" name="is_currently_studying_label" id="is_currently_studying_label" for="is_currently_studying">Are you currently studying any qualifications?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input id="is_currently_studying" name="is_currently_studying" type="radio" value="Yes" onClick="reset_error_class('is_currently_studying_label');toggle_quals_currently_studying_questions();">
									No <input id="is_currently_studying" name="is_currently_studying" type="radio" value="No" onClick="reset_error_class('is_currently_studying_label');toggle_quals_currently_studying_questions();">
								</div>					
							</div>	

												
							<div id="quals_currently_studying_questions" style="display:none;">	
								<p style="margin-top:30px;">Please enter the details of qualifications that you are <strong>currently</strong> studying.</p>

								<!-- start quals achieved table -->
								<div class="table-responsive"> 
									<table class="table">
										<tr>
											<th>
												Exam board
											</th>
											<th>
												Subject
											</th>
											<th>
												Level
											</th>
											<th>
												Predicted Grade
											</th>
											<th>
												Date to be taken
											</th>
											<th>
												Length of study
											</th>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_1_examboard" id="current_study_qual_1_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_1_subject" id="current_study_qual_1_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_1_level" id="current_study_qual_1_level" size="10">
											</td>
											<td>
												<input maxlength="15" type="text" size="5" name="current_study_qual_1_predicted_grade" id="current_study_qual_1_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_1_date_taken" id="current_study_qual_1_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_1_length" id="current_study_qual_1_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>	
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_2_examboard" id="current_study_qual_2_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="current_study_qual_2_subject" id="current_study_qual_2_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_2_level" id="current_study_qual_2_level" size="10">
					
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_2_predicted_grade" id="current_study_qual_2_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_2_date_taken" id="current_study_qual_2_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_2_length" id="current_study_qual_2_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>					
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_3_examboard" id="current_study_qual_3_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="current_study_qual_3_subject" id="current_study_qual_3_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_3_level" id="current_study_qual_3_level" size="10">

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_3_predicted_grade" id="current_study_qual_3_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_3_date_taken" id="current_study_qual_3_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_3_length" id="current_study_qual_3_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>					
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_4_examboard" id="current_study_qual_4_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="current_study_qual_4_subject" id="current_study_qual_4_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_4_level" id="current_study_qual_4_level" size="10">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_4_predicted_grade" id="current_study_qual_4_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_4_date_taken" id="current_study_qual_4_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_4_length" id="current_study_qual_4_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_5_examboard" id="current_study_qual_5_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_5_subject" id="current_study_qual_5_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_5_level" id="current_study_qual_5_level" size="10">

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_5_predicted_grade" id="current_study_qual_5_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_5_date_taken" id="current_study_qual_5_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_5_length" id="current_study_qual_5_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_6_examboard" id="current_study_qual_6_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_6_subject" id="current_study_qual_6_subject" value="">
											</td>
											<td>
												<input  maxlength="15" type="text" name="current_study_qual_6_level" id="current_study_qual_6_level" size="10">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_6_predicted_grade" id="current_study_qual_6_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_6_date_taken" id="current_study_qual_6_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_6_length" id="current_study_qual_6_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_7_examboard" id="current_study_qual_7_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="current_study_qual_7_subject" id="current_study_qual_7_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_7_level" id="current_study_qual_7_level" size="10">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_7_predicted_grade" id="current_study_qual_7_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_7_date_taken" id="current_study_qual_7_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_7_length" id="current_study_qual_7_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_8_examboard" id="current_study_qual_8_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_8_subject" id="current_study_qual_8_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_8_level" id="current_study_qual_8_level" size="10">
		

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_8_predicted_grade" id="current_study_qual_8_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_8_date_taken" id="current_study_qual_8_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_8_length" id="current_study_qual_8_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_9_examboard" id="current_study_qual_9_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_9_subject" id="current_study_qual_9_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_9_level" id="current_study_qual_9_level" size="10">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_9_predicted_grade" id="current_study_qual_9_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_9_date_taken" id="current_study_qual_9_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_9_length" id="current_study_qual_9_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_10_examboard" id="current_study_qual_10_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_10_subject" id="current_study_qual_10_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_10_level" id="current_study_qual_10_level" size="10">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_10_predicted_grade" id="current_study_qual_10_predicted_grade"  value="">
											</td>
											<td>
												<input  maxlength="15" size="10" type="text" name="current_study_qual_10_date_taken" id="current_study_qual_10_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_10_length" id="current_study_qual_10_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_11_examboard" id="current_study_qual_11_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_11_subject" id="current_study_qual_11_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_11_level" id="current_study_qual_11_level" size="10">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_11_predicted_grade" id="current_study_qual_11_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_11_date_taken" id="current_study_qual_11_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_11_length" id="current_study_qual_11_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="current_study_qual_12_examboard" id="current_study_qual_12_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="current_study_qual_12_subject" id="current_study_qual_12_subject" value="">
											</td>
											<td>
												<input maxlength="15" type="text" name="current_study_qual_12_level" id="current_study_qual_12_level" size="10">

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="current_study_qual_12_predicted_grade" id="current_study_qual_12_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="current_study_qual_12_date_taken" id="current_study_qual_12_date_taken" value="">
											</td>
											<td>
												<select name="current_study_qual_12_length" id="current_study_qual_12_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
								
									</table>		
								</div>
								<!-- end quals achieved table -->								

								<p style="margin-top:20px;"></p>
						
								<div class="form-group" style="padding:20px;">
									<label for="current_study_qual_further_detail">If you are studying further qualifications please give details below</label>
									<textarea class="form-control" rows="5" name="current_study_qual_further_detail" id="current_study_qual_further_detail"><?php echo $oe['current_study_qual_further_detail'];?></textarea>
								</div>
							</div>

						</form>
						
					</div>
				</div>
			</div>
			<!-- end Quals being studied -->			

			<!-- Quals finished studied start -->			
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseQualsAchieved">
						<h4 class="panel-title" id="quals_achieved_heading">
							Qualifications already achieved
						</h4>
					</a>
				</div>
				<div id="collapseQualsAchieved" class="panel-collapse collapse in">

					<div class="panel-body">

						<p style="font-weight:bold;color:#009aca;padding:5px;">
							It is important to tell us about your previous qualifications as many of our courses have entry requirements. This information can also help us to provide further advice and guidance on the suitability of your chosen course(s).
						</p>
					
						<form name="form_quals_achieved" id="form_quals_achieved" role="form" class="form-horizontal">

							<div class="form-group">
								<label class="control-label col-sm-3" name="any_previous_quals_label" id="any_previous_quals_label" for="any_previous_quals">Do you have any previous qualifications?</label>
								<div class="col-sm-9" style="margin-top:5px;">
									Yes <input name="any_previous_quals" id="any_previous_quals" type="radio" value="Yes" onClick="reset_error_class('any_previous_quals_label');toggle_quals_achieved_questions();">
									No <input name="any_previous_quals" id="any_previous_quals" type="radio" value="No" onClick="reset_error_class('any_previous_quals_label');toggle_quals_achieved_questions();">
								</div>					
							</div>	

						
							<div id="quals_achieved_div" name="quals_achieved_div" style="display:none;">
								<p name="highest_qual_label" id="highest_qual_label" ><strong>What is the Highest Level Qualification you have achieved?</strong></p>

								<div class="form-group">
									<label class="control-label col-sm-8" for="no_qual">No qualifications</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="99 No qualifications" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>
								
								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_1">Level 1 (e.g. fewer than five GCSEs at grades D to G, BTEC First Certificates, GNVQ	Foundation)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="01 Level 1" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>
									
								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_2">Full Level 2 (e.g. five or more GCSEs at grades A to C, BTEC First Diploma, GNVQ Intermediate)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="02 Full Level 2" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>	
									
								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_3">Full Level 3 (e.g. two or more A levels, four or more AS levels, BTEC National,	ONC/OND, GNVQ Advanced)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="03 Full Level 3" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>		

								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_4">Level 4 (e.g. First Degree, HNC/HND, teaching qualifications)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="10 Level 4" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>																																

								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_5">Level 5 and above (e.g. HNDS/Other higher diploma, Professional	Diplomas)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="11 Level 5" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>					

								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_6">Level 6 and above (e.g. Bachelor degree, Advanced Professional Diplomas)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="12 Level 6" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>					

								<div class="form-group">				
									<label class="control-label col-sm-8" for="level_7">Level 7 and above (e.g. Postgraduate certificate, Fellowships and fellowship diploma, Masters)</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="13 Level 7 and above" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>	

								<div class="form-group">				
									<label class="control-label col-sm-8" for="entry_level">Entry level</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="09 Entry level" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>

								<div class="form-group">				
									<label class="control-label col-sm-8" for="other_level">Other qualifications below level 1</label>
									<div class="col-sm-4" style="margin-top:5px;">
										<input name="highest_qual" id="highest_qual" type="radio" value="07 Other qualifications below level 1" onClick="reset_error_class('highest_qual_label');">
									</div>
								</div>
								
								<p style="margin-top:30px;">Please enter the details of qualifications that you have <strong>fully</strong> completed.</p>

								<!-- start quals achieved table -->
								<div class="table-responsive"> 
									<table class="table">
										<tr>
											<th>
												Exam board
											</th>
											<th>
												Subject
											</th>
											<th>
												Level
											</th>
											<th>
												Result
											</th>
											<th>
												Date taken
											</th>
											<th>
												Length of study
											</th>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_1_examboard" id="previous_study_qual_1_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_1_subject" id="previous_study_qual_1_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_1_level" id="previous_study_qual_1_level" value="">
					
											</td>
											<td>
												<input maxlength="15" type="text" size="5" name="previous_study_qual_1_predicted_grade" id="previous_study_qual_1_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_1_date_taken" id="previous_study_qual_1_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_1_length" id="previous_study_qual_1_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>	
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_2_examboard" id="previous_study_qual_2_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="previous_study_qual_2_subject" id="previous_study_qual_2_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_2_level" id="previous_study_qual_2_level" value="">
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_2_predicted_grade" id="previous_study_qual_2_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_2_date_taken" id="previous_study_qual_2_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_2_length" id="previous_study_qual_2_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>					
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_3_examboard" id="previous_study_qual_3_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="previous_study_qual_3_subject" id="previous_study_qual_3_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_3_level" id="previous_study_qual_3_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_3_predicted_grade" id="previous_study_qual_3_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_3_date_taken" id="previous_study_qual_3_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_3_length" id="previous_study_qual_3_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>					
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_4_examboard" id="previous_study_qual_4_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="previous_study_qual_4_subject" id="previous_study_qual_4_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_4_level" id="previous_study_qual_4_level" value="">

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_4_predicted_grade" id="previous_study_qual_4_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_4_date_taken" id="previous_study_qual_4_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_4_length" id="previous_study_qual_4_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_5_examboard" id="previous_study_qual_5_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_5_subject" id="previous_study_qual_5_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_5_level" id="previous_study_qual_5_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_5_predicted_grade" id="previous_study_qual_5_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_5_date_taken" id="previous_study_qual_5_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_5_length" id="previous_study_qual_5_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_6_examboard" id="previous_study_qual_6_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_6_subject" id="previous_study_qual_6_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_6_level" id="previous_study_qual_6_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_6_predicted_grade" id="previous_study_qual_6_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_6_date_taken" id="previous_study_qual_6_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_6_length" id="previous_study_qual_6_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_7_examboard" id="previous_study_qual_7_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>					
											</td>
											<td>
												<input type="text" name="previous_study_qual_7_subject" id="previous_study_qual_7_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_7_level" id="previous_study_qual_7_level" value="">

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_7_predicted_grade" id="previous_study_qual_7_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_7_date_taken" id="previous_study_qual_7_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_7_length" id="previous_study_qual_7_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_8_examboard" id="previous_study_qual_8_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_8_subject" id="previous_study_qual_8_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_8_level" id="previous_study_qual_8_level" value="">
		

											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_8_predicted_grade" id="previous_study_qual_8_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_8_date_taken" id="previous_study_qual_8_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_8_length" id="previous_study_qual_8_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_9_examboard" id="previous_study_qual_9_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_9_subject" id="previous_study_qual_9_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_9_level" id="previous_study_qual_9_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_9_predicted_grade" id="previous_study_qual_9_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_9_date_taken" id="previous_study_qual_9_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_9_length" id="previous_study_qual_9_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_10_examboard" id="previous_study_qual_10_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_10_subject" id="previous_study_qual_10_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_10_level" id="previous_study_qual_10_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_10_predicted_grade" id="previous_study_qual_10_predicted_grade"  value="">
											</td>
											<td>
												<input  maxlength="15" size="10" type="text" name="previous_study_qual_10_date_taken" id="previous_study_qual_10_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_10_length" id="previous_study_qual_10_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_11_examboard" id="previous_study_qual_11_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_11_subject" id="previous_study_qual_11_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_11_level" id="previous_study_qual_11_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_11_predicted_grade" id="previous_study_qual_11_predicted_grade"  value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_11_date_taken" id="previous_study_qual_11_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_11_length" id="previous_study_qual_11_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
										<tr>
											<td>
												<select name="previous_study_qual_12_examboard" id="previous_study_qual_12_examboard">
													<option value=""></option>						
													<option value="AQA">AQA</option>						
													<option value="CG">C&amp;G</option>						
													<option value="CIE">CIE</option>						
													<option value="ICAAR">CCEA</option>						
													<option value="OCR">OCR</option>						
													<option value="WJEC">WJEC</option>												
													<option value="Other">Other</option>												
												</select>
											</td>
											<td>
												<input type="text" name="previous_study_qual_12_subject" id="previous_study_qual_12_subject" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_12_level" id="previous_study_qual_12_level" value="">
		
											</td>
											<td>
												<input maxlength="15" size="5" type="text" name="previous_study_qual_12_predicted_grade" id="previous_study_qual_12_predicted_grade" value="">
											</td>
											<td>
												<input maxlength="15" size="10" type="text" name="previous_study_qual_12_date_taken" id="previous_study_qual_12_date_taken" value="">
											</td>
											<td>
												<select name="previous_study_qual_12_length" id="previous_study_qual_12_length">
													<option value=""></option>						
													<option value="less than 1 year">&lt; 1 year</option>						
													<option value="1 year">1 year</option>						
													<option value="2 years">2 years</option>						
													<option value="3 years">3 years</option>						
													<option value="more than 3 years">&gt; 3 years</option>						
												</select>
											</td>
										</tr>
								
									</table>		
								</div>
								<!-- end quals achieved table -->								

								<p style="margin-top:20px;"></p>
						
								<div class="form-group" style="padding:20px;">
									<label for="previous_study_qual_further_detail">If you have more information regarding previously studied qualifications, please give details below</label>
									<textarea class="form-control" rows="5" name="previous_study_qual_further_detail" id="previous_study_qual_further_detail"><?php echo $oe['previous_study_qual_further_detail'];?></textarea>
								</div>
							</div>
							
						</form>
					</div>
				</div>
			</div>
			<!-- end Quals finished studied -->

			<!-- disability start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseSeven">
						<h4 class="panel-title" id="disability_heading">
							Disability/Learning Difficulty or Health Problem
						</h4>
					</a>
				</div>
				<div id="collapseSeven" class="panel-collapse collapse in">
					<div class="panel-body">

						<form id="form_disability"name="form_disability" role="form" class="form-horizontal">					
					
							<div class="form-group">
								<label class="control-label col-sm-6" name="consider_disability_difficulty_label" id="consider_disability_difficulty_label" for="consider_disability_difficulty">Do you consider yourself to have a disability and/or learning difficulty?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="consider_disability_difficulty" id="consider_disability_difficulty" type="radio" value="Yes" onClick="toggle_disability_questions();reset_error_class('consider_disability_difficulty_label');">
									No <input name="consider_disability_difficulty" id="consider_disability_difficulty" type="radio" value="No" onClick="toggle_disability_questions();reset_error_class('consider_disability_difficulty_label');">
								</div>					
							</div>				

							<div id="disability_questions" name="disability_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">

								<div class="form-group">
									<label class="control-label col-sm-3" name="disability_label" id="disability_label" for="disability">Disability</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="disability" id="disability" onChange="reset_error_class('disability_label');reset_error_class('learning_difficulty_label');">
											<option value=""></option>
											<option value="90 Multiple disabilities" >Multiple Disabilities</option>
											<option value="01 Blind or visually impaired" >Blind or visually impaired (very little or no sight)</option>
											<option value="02 Deaf or hearing impaired" >Deaf or hearing impaired (use a hearing aid or sign language)</option>
											<option value="03 Disability affecting mobility" >Disability affecting mobility (e.g wheelchair user)</option>
											<option value="04 Other physical disbility" >Other physical disability</option>
											<option value="05 Other medical condition" >Other medical condition (e.g epilepsy, asthma, diabetes)</option>
											<option value="06 Emotional or behavioural difficulties" >Emotional or Behavioural Difficulties</option>
											<option value="07 Mental health difficulty" >Mental health difficulty</option>
											<option value="08 Temporary disability or illness" >Temporary disability or illness (e.g accident)</option>
											<option value="09 Profound complex difficulty" >Profound Complex Difficulty</option>
											<option value="10 Aspergers syndrome" >Asperger's syndrome</option>
											<option value="97 Other disability" >Other</option>
										</select>
									</div>					
								</div>					
							
								<div class="form-group">
									<label class="control-label col-sm-3" name="learning_difficulty_label" id="learning_difficulty_label" for="learning_difficulty">Learning Difficulty</label>
									<div class="col-sm-9" style="margin-top:5px;">
										<select name="learning_difficulty" id="learning_difficulty" onChange="reset_error_class('disability_label');reset_error_class('learning_difficulty_label');">
											<option value=""></option>
											<option value="90 Multiple learning difficulties" >Multiple learning difficulties</option>
											<option value="01 Moderate learning difficulty" >Moderate learning difficulty</option>
											<option value="02 Severe learning difficulty" >Severe learning difficulty</option>
											<option value="10 Dyslexia" >Dyslexia (difficulty with words)</option>
											<option value="11 Dyscalculia" >Dyscalculia (difficulty with numbers)</option>
											<option value="20 Autism" >Autism Spectrum Disorder</option>
											<option value="19 other specific learning difficulty" >other specific learning difficulty</option>
											<option value="97 other" >Other</option>										
										</select>
									</div>					
								</div>	
								
								
							</div>

							<div class="form-group">
								<label class="control-label col-sm-6" name="special_arrangement_exams_label" id="special_arrangement_exams_label" for="special_arrangement_exams">Have you ever received special arrangements in exams?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="special_arrangement_exams" id="special_arrangement_exams" type="radio" value="Yes" onClick="toggle_disability_questions();reset_error_class('special_arrangement_exams_label');">
									No <input name="special_arrangement_exams" id="special_arrangement_exams" type="radio" value="No" onClick="toggle_disability_questions();reset_error_class('special_arrangement_exams_label');">
								</div>					
							</div>			

							<div class="form-group">
								<label class="control-label col-sm-6" name="support_at_interview_label" id="support_at_interview_label" for="support_at_interview">Do you need any support at interview stage?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="support_at_interview" id="support_at_interview" type="radio" value="Yes" onClick="reset_error_class('support_at_interview_label');">
									No <input name="support_at_interview" id="support_at_interview" type="radio" value="No" onClick="reset_error_class('support_at_interview_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-6" name="extra_support_reading_writing_label" id="extra_support_reading_writing_label" for="extra_support_reading_writing">Do you need any extra support for reading and writing?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="extra_support_reading_writing" id="extra_support_reading_writing" type="radio" value="Yes" onClick="reset_error_class('extra_support_reading_writing_label');">
									No <input name="extra_support_reading_writing" id="extra_support_reading_writing" type="radio" value="No" onClick="reset_error_class('extra_support_reading_writing_label');">
								</div>					
							</div>	
							
							<div class="form-group">
								<label class="control-label col-sm-6" name="extra_support_numeracy_label" id="extra_support_numeracy_label" for="extra_support_numeracy">Do you need any extra support with maths?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="extra_support_numeracy" id="extra_support_numeracy" type="radio" value="Yes" onClick="reset_error_class('extra_support_numeracy_label');">
									No <input name="extra_support_numeracy" id="extra_support_numeracy" type="radio" value="No" onClick="reset_error_class('extra_support_numeracy_label');">
								</div>					
							</div>	

							<div class="form-group">
								<label class="control-label col-sm-6" name="statement_of_needs_label" id="statement_of_needs_label" for="statement_of_needs">Do you have a Statement of Special Education Needs or an Education and Health Care Plan?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="statement_of_needs" id="statement_of_needs" type="radio" value="Yes" onClick="reset_error_class('statement_of_needs_label');">
									No <input name="statement_of_needs" id="statement_of_needs" type="radio" value="No" onClick="reset_error_class('statement_of_needs_label');">
								</div>					
							</div>								
							
							
						</form>

					</div>
				</div>
			</div>				
			<!-- disability end -->

			<!-- Finance start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseFinance">
						<h4 class="panel-title" id="finance_heading">
							Finance
						</h4>
					</a>
				</div>
				<div id="collapseFinance" class="panel-collapse collapse in">
					<div class="panel-body">

						<form id="form_finance"name="form_finance" role="form" class="form-horizontal">					
					
							<p><strong>How will you be paying your course fees?</strong></p>

							<div class="form-group">
								<label class="control-label col-sm-4" name="paying_own_course_fees_label" id="paying_own_course_fees_label" for="paying_own_course_fees">Paying your own course fees</label>

								<div class="col-sm-8" style="margin-top:5px;">
									<input type="checkbox" id="paying_own_course_fees" name="paying_own_course_fees">
								</div>					
							</div>								

							<div class="form-group">
								<label class="control-label col-sm-4" name="employer_course_fees_label" id="employer_course_fees_label" for="employer_course_fees">Employer/sponsor paying your course fees</label>

								<div class="col-sm-8" style="margin-top:5px;">
									<input type="checkbox" id="employer_course_fees" name="employer_course_fees">
								</div>					
							</div>								

							<div class="form-group">
								<label class="control-label col-sm-4" name="loan_24_course_fees_label" id="loan_24_course_fees_label" for="loan_24_course_fees">Applying for an Advanced Learner Loan</label>

								<div class="col-sm-8" style="margin-top:5px;">
									<input type="checkbox" id="loan_24_course_fees" name="loan_24_course_fees">
								</div>					
							</div>								

							<p style="margin-top:35px;">
								We can provide Financial Support and advice to eligible students e.g. if you are on income support or housing benefit etc.
							</p>
							<p style="margin-top:25px;">
								<strong>To find out if you may be eligible, please <a href="lsf_eligibility.php" target="_blank">click here</strong></a>.
							</p>
							
							<div class="form-group">
								<label class="control-label col-sm-5" name="financial_hardship_label" id="financial_hardship_label" for="financial_hardship">
								Would you like to apply to the Learner Support Fund?
								</label>
								<div class="col-sm-7" style="margin-top:5px;">
									<input type="checkbox" id="financial_hardship" name="financial_hardship" onclick="toggle_lsf_apply();">
								</div>					
							</div>				

							<div id="lsf_apply_div" name="lsf_apply_div" style="padding:20px;display:none;background-color:#F9D4C1;padding-top:5px;padding-bottom:5px;margin-bottom:10px;">		

								<h2>Learner Support Fund Application</h2>														

							
								<p style="margin-top:20px;margin-bottom:20px;"><strong>Funding is limited and cannot be guaranteed.  Please answer these additional questions</strong></p>							

								<div class="form-group">
									<label class="control-label col-sm-6" name="fsm_last_year_label" id="fsm_last_year_label" for="fsm_last_year">If you are aged 16 to 18, did you receive Free School Meals last year?</label>
									<div class="col-sm-6" style="margin-top:5px;">
										Yes <input name="fsm_last_year" id="fsm_last_year" type="radio" value="Yes" onClick="reset_error_class('fsm_last_year_label');">
										No <input name="fsm_last_year" id="fsm_last_year" type="radio" value="No" onClick="reset_error_class('fsm_last_year_label');">
										I am not aged 16 to 18 <input name="fsm_last_year" id="fsm_last_year" type="radio" value="Not aged 16 to 18" onClick="reset_error_class('fsm_last_year_label');">
									</div>					
								</div>									
								
								<div class="form-group">
									<label class="control-label col-sm-6" name="loan_24_applied_label" id="loan_24_applied_label" for="loan_24_applied">Have you applied for an Advanced Learner Loan?</label>
									<div class="col-sm-6" style="margin-top:5px;">
										Yes <input name="loan_24_applied" id="loan_24_applied" type="radio" value="Yes" onClick="reset_error_class('loan_24_applied_label');">
										No <input name="loan_24_applied" id="loan_24_applied" type="radio" value="No" onClick="reset_error_class('loan_24_applied_label');">
										I am not aged 24 or over <input name="loan_24_applied" id="loan_24_applied" type="radio" value="Not aged 24 or over" onClick="reset_error_class('loan_24_applied_label');">
									</div>					
								</div>	

								<div class="form-group">
									<label class="control-label col-sm-6" name="marital_status_label" id="marital_status_label" for="loan_24_applied">What is your marital status?</label>
									<div class="col-sm-6" style="margin-top:5px;">
										<select name="marital_status" id="marital_status" onchange="reset_error_class('marital_status_label');">
											<option value=""></option>
											<option value="Single">Single</option>															
											<option value="Lone Parent">Lone Parent</option>
											<option value="Married or in a Civil Partnership">Married or in a Civil Partnership</option>
										</select>
									</div>					
								</div>	

								<div class="form-group" style="padding:20px;">
									<label id="specific_hardship_label" name="specific_hardship_label" for="specific_hardship">IT IS ESSENTIAL YOU COMPLETE THIS SECTION.  Please identify specific hardship you may have in attending your course.</label>
									<textarea onfocus="reset_error_class('specific_hardship_label');" class="form-control" rows="5" id="specific_hardship" name="specific_hardship"><?php echo $oe['specific_hardship']; ?></textarea>
								</div>								
								
								<p><strong>Household Information</strong><br />(please list all members of your household and their relationship to you)</p>		

								<table class="table" style="width:50%;">
									<tr>
										<th>Name</th>
										<th>Relationship to you</th>
										<th>Age (if under 18)</th>
									</tr>
									<tr>
										<td>
											<input maxlength="50" type="text" id="household_info_name_1" name="household_info_name_1">
										</td>
										<td>
											<input maxlength="50" type="text" id="household_info_relationship_1" name="household_info_relationship_1">
										</td>
										<td>
											<input maxlength="50" size="2" type="text" id="household_age_1" name="household_age_1">
										</td>
									</tr>
									<tr>
										<td>
											<input maxlength="55" type="text" id="household_info_name_2" name="household_info_name_2">
										</td>
										<td>
											<input maxlength="50" type="text" id="household_info_relationship_2" name="household_info_relationship_2">
										</td>
										<td>
											<input maxlength="50" size="2" type="text" id="household_age_2" name="household_age_2">
										</td>
									</tr>
									<tr>
										<td>
											<input maxlength="55" type="text" id="household_info_name_3" name="household_info_name_3">
										</td>
										<td>
											<input maxlength="50" type="text" id="household_info_relationship_3" name="household_info_relationship_3">
										</td>
										<td>
											<input  maxlength="50" size="2" type="text" id="household_age_3" name="household_age_3">
										</td>
									</tr>
									<tr>
										<td>
											<input maxlength="55" type="text" id="household_info_name_4" name="household_info_name_4">
										</td>
										<td>
											<input maxlength="50" type="text" id="household_info_relationship_4" name="household_info_relationship_4">
										</td>
										<td>
											<input maxlength="50" size="2" type="text" id="household_age_4" name="household_age_4">
										</td>
									</tr>
									<tr>
										<td>
											<input maxlength="55" type="text" id="household_info_name_5" name="household_info_name_5">
										</td>
										<td>
											<input maxlength="50" type="text" id="household_info_relationship_5" name="household_info_relationship_5">
										</td>
										<td>
											<input maxlength="50" size="2" type="text" id="household_age_5" name="household_age_5">
										</td>
									</tr>
								</table>

								<p style="margin-top:10px;margin-bottom:10px;"><strong>What do you require help with?  (tick all that apply)</strong></p>		
								
								<div class="form-group">
									<label class="control-label col-sm-5" name="childcare_label" id="childcare_label" for="childcare">Childcare (Care to Learn).  For more information visit <a href="https://www.gov.uk/care-to-learn/overview" target="blank">https://www.gov.uk/care-to-learn/overview</a>)</label>

									<div class="col-sm-7" style="margin-top:5px;">
										<input type="checkbox" id="childcare" name="childcare">
									</div>					
								</div>	

								<div class="form-group">
									<label class="control-label col-sm-5" name="essential_kit_label" id="essential_kit_label" for="essential_kit">Essential kit/equipment</label>

									<div class="col-sm-7" style="margin-top:5px;">
										<input type="checkbox" id="essential_kit" name="essential_kit">
									</div>					
								</div>	

								<div class="form-group">
									<label class="control-label col-sm-5" name="material_fees_label" id="material_fees_label" for="material_fees">Material Fees</label>

									<div class="col-sm-7" style="margin-top:5px;">
										<input type="checkbox" id="material_fees" name="material_fees">
									</div>					
								</div>	

								<div class="form-group">
									<label class="control-label col-sm-5" name="travel_college_label" id="travel_college_label" for="travel_college">Travel to and from college (please provide the name of the bus company you will use)</label>

									<div class="col-sm-7" style="margin-top:5px;">
										<select name="travel_college" id="travel_college" onchange="toggle_travel_college();" >
											<option value=""></option>
											<option value="First">First</option>											
											<option value="Arriva">Arriva</option>																						
											<option value="Fuel">Fuel (if aged over 19)</option>																						
											<option value="Other">Other</option>																						
										</select>
									</div>					
								</div>								

								<div id="travel_college_other_div" name="travel_college_other_div" style="display:none;">
									<div class="form-group">
										<label class="control-label col-sm-5" name="travel_college_other_label" id="travel_college_other_label" for="travel_college">Please state</label>
										<div class="col-sm-7" style="margin-top:5px;">
											<input type="text" maxlength="25" id="travel_college_other" name="travel_college_other">
										</div>					
									</div>									
								</div>
									
								<div class="form-group">
									<label class="control-label col-sm-5" name="buying_own_kit_label" id="buying_own_kit_label" for="buying_own_kit">Will you buy your own kit before enrolment? (If the answer is 'Yes' please keep all receipts for possible reimbursement) </label>
									<div class="col-sm-7" style="margin-top:5px;">
										Yes <input name="buying_own_kit" id="buying_own_kit" type="radio" value="Yes" onClick="reset_error_class('buying_own_kit_label');">
										No <input name="buying_own_kit" id="buying_own_kit" type="radio" value="No" onClick="reset_error_class('buying_own_kit_label');">
									</div>					
								</div>

								<p style="margin-top:40px;margin-bottom:20px;"><strong>Declaration</strong></p>
								<p>
									I understand that funding is limited and cannot be guaranteed.
								</p>
								<p>
									I declare that the information I have given is correct with nothing being omitted that would affect this application.
									Any false applications will be subject to disciplinary action.
								</p>
								<p>
									I understand that any assistance provided is subject to enrolling on a course of study and maintaining
									satisfactory levels of attendance and progression, and agree that consultation may take place with my Tutor.
								</p>
								<p>
									I agree that this support is only available if I have no outstanding debts with Leicester College.
								</p>
								<p>
									I understand all or part of any Financial Assistance provided may be repayable if I withdraw from my course for any
									reason during the Academic Year.  The amount repayable will be determined by Leicester College.
								</p>
								<p style="margin-top:20px;margin-bottom:20px;"><strong>Appeals Procedure</strong></p>
								<p>
									If you do not agree with any decisions made, you can appeal in writing, to the IAG Coordinator
									within 14 days of your allocation letter.  You can ask for an Appeal Form, from Student Services at any campus.
								</p>

							</div>
							
							
						</form>

					</div>
				</div>
			</div>				
			<!-- disability end -->

			<!-- Marketing start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseMarketing">
						<h4 class="panel-title" id="marketing_heading">
							Marketing
						</h4>
					</a>
				</div>
				<div id="collapseMarketing" class="panel-collapse collapse in">
					<div class="panel-body">

						<form id="form_marketing"name="form_marketing" role="form" class="form-horizontal">					

							<div class="form-group">
							<label class="control-label col-sm-7" name="encouraged_apply_label" id="encouraged_apply_label" for="encouraged_apply">What encouraged you to apply to Leicester College?</label>
								<div class="col-sm-5" style="margin-top:5px;">
									<select name="encouraged_apply" id="encouraged_apply" onchange="reset_error_class('encouraged_apply_label');">
										<option value="">Please Select</option>
										<option value="03 Teachers Recommendation">Teachers Recommendation</option>
										<option value="09 Careers Advice">Careers Advice</option>
										<option value="04 Employer Advice">Employer Advice</option>
										<option value="05 Website">Website</option>
										<option value="19 Advertising Publicity">Advertising / Publicity</option>
										<option value="25 Event">Event / Exhibition</option>
										<option value="10 Course Guide">Course Guide</option>
										<option value="11 Previous Student">Previous Student</option>
										<option value="08 Other">Other</option>
									</select>
								</div>					
							</div>							
						
							<div class="form-group">
							<label class="control-label col-sm-7" for="consent_contact_courses">I consent to Leicester College contacting me about Courses or Learning Opportunities</label>
								<div class="col-sm-5" style="margin-top:5px;">
									<input type="checkbox" id="consent_contact_courses" name="consent_contact_courses" onClick="toggle_marketing_questions();">
								</div>					
							</div>					

							<div class="form-group">
							<label class="control-label col-sm-7" for="consent_contact_surveys">I consent to Leicester College contacting me for Surveys and Research</label>
								<div class="col-sm-5" style="margin-top:5px;">
									<input type="checkbox" id="consent_contact_surveys" name="consent_contact_surveys" onClick="toggle_marketing_questions();">
								</div>					
							</div>	

							<div class="form-group">
							<label class="control-label col-sm-7" for="consent_contact_marketing">I consent to Leicester College contacting me for Marketing purposes</label>
								<div class="col-sm-5" style="margin-top:5px;">
									<input type="checkbox" id="consent_contact_marketing" name="consent_contact_marketing" onClick="toggle_marketing_questions();">
								</div>					
							</div>							
							
							
							<div id="marketing_questions" name="marketing_questions" style="display:none;background-color:#B3E9ED;padding-top:5px;padding-bottom:5px;">
								
								<p id="marketing_questions_label"></p>
								
								<div class="form-group">
								<label class="control-label col-sm-5" >I can be contacted...</label>
									<div class="col-sm-7" style="margin-top:5px;">
									</div>					
								</div>								
								
								
								<div class="form-group">
								<label class="control-label col-sm-5" for="contact_post">by Post</label>
									<div class="col-sm-7" style="margin-top:5px;">
										<input type="checkbox"  id="contact_post" name="contact_post" onClick="reset_error_class('marketing_questions_label');">
									</div>					
								</div>

								<div class="form-group">
								<label class="control-label col-sm-5" for="contact_phone">by Phone (including SMS)</label>
									<div class="col-sm-7" style="margin-top:5px;">
										<input type="checkbox"  id="contact_phone" name="contact_phone" onClick="reset_error_class('marketing_questions_label');">
									</div>					
								</div>

								<div class="form-group">
								<label class="control-label col-sm-5" for="contact_email">by e-mail</label>
									<div class="col-sm-7" style="margin-top:5px;">
										<input type="checkbox"  id="contact_email" name="contact_email" onClick="reset_error_class('marketing_questions_label');">
									</div>					
								</div>

							</div>
			
						</form>
						
					</div>
				</div>
			</div>	
			<!-- Marketing end -->

			<!-- Declaration start -->
			<div class="panel panel-default">
				<div class="panel-heading">
					<a style="color:white;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseDeclaration">
						<h4 class="panel-title" id="declaration_heading">
							Declaration and Terms &amp; Conditions
						</h4>
					</a>
				</div>
				<div id="collapseDeclaration" class="panel-collapse collapse in">
					<div class="panel-body">

						<form id="form_declaration" name="form_declaration" role="form" class="form-horizontal">					

							<div style="background-color:#f4f4f4;padding:5px;">
								<p><strong>How we use your personal information</strong></p>

								<p>
									The personal information you provide is passed to the Chief Executive of Skills Funding ("the Agency") and, when needed, the Young People's Learning Agency for England ("the YPLA") to meet legal duties under the Apprenticeship, Skills, Children and Learning Act 2009, and for the Agency's Learning Records Service (LRS) to create and maintain a unique learner number (ULN). The information you provide may be shared with other partner organisations for purposes relating to education or training. 
									Further information about use of and access to your personal data, and details of partner organisations are available at https://www.gov.uk/help/privacy-policy and https://www.gov.uk/government/publications/lrs-privacy-notices
								</p>
							
								<p><strong>LRS Standard Fair Processing Notice</strong></p>
								
								<p>
									The information you provide will be used by the Chief Executive of "the Agency", to issue you with a Unique Learner Number (ULN), and to create your Personal Learning Record. Further details of how your information is processed and shared can be found at 
									<a target="_blank" href="https://www.gov.uk/government/publications/lrs-privacy-notices ">https://www.gov.uk/government/publications/lrs-privacy-notices</a>
									To access your ULN log onto Leicester College Moodle (for existing learners only) or contact one of our information centres.							
								</p>
							</div>						
						
							<p style="margin-top:10px;margin-bottom:10px;"><strong>If your course involves a work placement with vulnerable groups, you must declare both 'spent' and 'unspent' cautions and convictions that aren't 'filtered' in line with current guidance</strong></p>
						
							<div class="form-group">
								<label class="control-label col-sm-6" name="criminal_convictions_label" id="criminal_convictions_label" for="criminal_convictions">Do you have any unspent criminal convictions, pending matters or, are on bail?</label>
								<div class="col-sm-6" style="margin-top:5px;">
									Yes <input name="criminal_convictions" id="criminal_convictions" type="radio" value="Yes" onClick="reset_error_class('criminal_convictions_label');">
									No <input name="criminal_convictions" id="criminal_convictions" type="radio" value="No" onClick="reset_error_class('criminal_convictions_label');">
								</div>					
							</div>					
					
						
							<div class="form-group">
								<label class="control-label col-sm-6" id="accept_terms_conditions_label" name="accept_terms_conditions_label" for="accept_terms_conditions">
									I believe that all the information contained in this application form is correct to the best of my knowledge and I accept the <a id="accept_terms_conditions_link" name="accept_terms_conditions_link" style="color:black;text-decoration:underline;" href="/oe/terms_and_conditions.php" target="_blank" title="Read our Terms and Conditions (Opens in a new window)">Terms and Conditions</a>
								</label>
								<div class="col-sm-6" style="margin-top:5px;">
									<input type="checkbox" id="accept_terms_conditions" name="accept_terms_conditions" onClick="reset_error_class('accept_terms_conditions_link');reset_error_class('accept_terms_conditions_label');">
								</div>					
							</div>			

							<h3>
								<strong>Please sign below</strong>
							</h3>

							<p>
								You can draw your signature if you are using a mouse or supported tablet/mobile device.  Use the 'Reset Signature' button below for more attempts.  This field is not mandatory.
							</p>

							
							<div style="border:1px solid black;" id="signature"></div>		
							<div style="text-align:right;margin-top:20px;">
								<input class="btn btn-primary" type="button" id="reset_signature_button" value="Reset Signature" onClick="$('#signature').jSignature('reset');">
							</div>
					
							<input type="hidden" id="pk" name="pk" value="<?php echo $pk;?>">

							<input type="hidden" id="date_of_birth" name="date_of_birth" value="">
							<input type="hidden" id="date_entry_uk" name="date_entry_uk" value="">
							<input type="hidden" id="when_start_studies" name="when_start_studies" value="">							
							
							<input type="hidden" id="date_employment_status_began" name="date_employment_status_began" value="">							
							<input type="hidden" id="date_unemployment_status_began" name="date_unemployment_status_began" value="">							
							<input type="hidden" id="date_retirement_status_began" name="date_retirement_status_began" value="">														

							<input type="hidden" id="extra_support" name="extra_support" value="">						
							
							<input type="hidden" id="lng" name="lng" value="">						
							<input type="hidden" id="lat" name="lat" value="">													
							
							<input type="hidden" id="permanent_postcode" name="permanent_postcode" value="">						
							<input type="hidden" id="termtime_postcode"  name="termtime_postcode" value="">						
							
						</form>

					</div>
				</div>
			</div>	
			<!-- Declaration end -->						

		</div>

		<div>
			<div style="float:left;text-align:left;margin-bottom:50px;">
				<image src="images/big-roller.gif" id="loaderDiv" style="display:none;">
				<input class="btn btn-primary" id="send_email_link_button" name="send_email_link_button" type="button" value="Continue with this later?" onClick="send_link();">
			</div>		
			<div style="float:right;text-align:right;margin-bottom:50px;">
				<image src="images/big-roller.gif" id="saveLoaderDiv" style="display:none;">
				<input class="btn btn-primary" id="save_button" type="button" value="Save" onclick="do_save();">
				<input class="btn btn-primary" type="button" value="Submit Application" name="submit_application_button" id="submit_application_button" onClick="do_application_submit();">
			</div>		
		</div>
			
		<div style="float:right;width:100%;text-align:right;">
			<img style="margin-top:10px;margin-bottom:10px;margin-right:20px;opacity:0.5;" src="images/esf_new.jpg">		
		</div>

		<div style='float:right;font-size:10px;opacity:0.5;'>
		&copy; Leicester College 2016
		</div>
			
	</div>
	<!-- end main container -->



</body>

</html>
