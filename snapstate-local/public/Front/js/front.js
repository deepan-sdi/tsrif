/*	Front End Validation Scripts	*/

/*	Global Vars
 	Var: Email Regex
*/
	var emailRegex	= /^[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,4})$/;
	var alphanumeric_alteast	= /^.*[a-zA-Z\u00C0-\u017F0-9]+/;
	var alphanumeric_mustbe		= /^[a-zA-Z0-9]+$/;
	var alphanumeric_mustbe_ws	= /^[-_ a-zA-Z0-9\u00C0-\u017F]+$/;
	var alpha_mustbe			= /^[a-zA-Z\u00C0-\u017F]+$/;

/*	Function: validateRegistrationForm
 	Form: Registration
*/
	function validateRegistrationForm() {
		var error_flag						= 0,
			msg								= '',
			form							= $('#registration_form'),
			first_name						= $('#first_name'),
			last_name						= $('#last_name'),
			email_address					= $('#email_address'),
			registration_password			= $('#registration_password'),
			registration_confirm_password	= $('#registration_confirm_password');
		
		$('.error_msg').remove();
		$('#signup').addClass('ind-pop-h2');
		$('#signup').removeClass('ind-pop-h2_new');
		
		first_name.removeClass('error_field');
		last_name.removeClass('error_field');
		email_address.removeClass('error_field');
		registration_password.removeClass('error_field');
		registration_confirm_password.removeClass('error_field');
		
		if(first_name.val() == '') {	// First Name
			msg	= 'Enter your First Name';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(first_name.val().length < 3) {
			msg	= 'First Name must be atleast 3 characters';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alpha_mustbe.test(first_name.val())) {
			msg	= 'First Name contains invalid character';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(last_name.val() == '') {	// Last Name
			msg	= 'Enter your Last Name';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(last_name.val().length < 3) {
			msg	= 'Last Name must be atleast 3 characters';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alpha_mustbe.test(last_name.val())) {
			msg	= 'Last Name contains invalid character';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(registration_password.val() == '' || registration_password.val().length < 6) {	// Password
			msg	= 'Password must be atleast 6 characters';
			registration_password.addClass('error_field');
			registration_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alphanumeric_alteast.test(registration_password.val())) {
			msg	= 'Password must contain atleast 1 alphanumeric character';
			registration_password.addClass('error_field');
			registration_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(registration_confirm_password.val() == '') {	// Confirm Password
			msg	= 'Password must be confirmed';
			registration_confirm_password.addClass('error_field');
			registration_confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(registration_confirm_password.val() != registration_password.val()) {
			msg	= 'Passwords do not match';
			registration_confirm_password.addClass('error_field');
			registration_confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			$('#signup').addClass('ind-pop-h2_new');
			$('#signup').removeClass('ind-pop-h2');
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-registration', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Email is already exist
					msg	= 'Email Address is already registered';
					email_address.addClass('error_field');
					email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					$('#signup').addClass('ind-pop-h2_new');
					$('#signup').removeClass('ind-pop-h2');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#registration_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					$('#signup').addClass('ind-pop-h2_new');
					$('#signup').removeClass('ind-pop-h2');
				} else if(data != '') {
					resetRegistrationForm();
					$('#signup').modal('hide');
					$('#message_content').html('Thank you for registering!  A confirmation email has been sent to your email address. Please click on the link in that email in order to activate your account.');
					$('#confirmation_modal').click();
				}
			});
			return false;
		}
	}
	
/*	Function: resetRegistrationForm
	Form: Registration
*/
	function resetRegistrationForm() {
		var first_name						= $('#first_name'),
			last_name						= $('#last_name'),
			email_address					= $('#email_address'),
			registration_password			= $('#registration_password'),
			registration_confirm_password	= $('#registration_confirm_password');
		
		$('.error_msg').remove();
		$('#signup').addClass('ind-pop-h2');
		$('#signup').removeClass('ind-pop-h2_new');
			
		first_name.removeClass('error_field');
		last_name.removeClass('error_field');
		email_address.removeClass('error_field');
		registration_password.removeClass('error_field');
		registration_confirm_password.removeClass('error_field');
		first_name.val('');
		last_name.val('');
		email_address.val('');
		registration_password.val('');
		registration_confirm_password.val('');
	}

/*	Function: showOverlay
	Page: Common
*/
	function showOverlay() {
		$('.progress-indicator').css('display', 'block');
		//$('body').css('overflow', 'hidden');
	}

/*	Function: hideOverlay
	Page: Common
*/
	function hideOverlay() {
		$('.progress-indicator').css('display', 'none');
		//$('body').css('overflow', 'auto');
	}

/*	Function: scrolltotop
	Page: Common
*/
	function scrolltotop() {
		window.scroll(0, 0);
	}
	
/*	Function: validateLoginForm
	Form: Login
*/
	function validateLoginForm() {
		var error_flag			= 0,
			msg					= '',
			form				= $('#login_form'),
			email_address		= $('#login_email'),
			password			= $('#login_password');
		
		$('.error_msg').remove();
		$('#login').addClass('ind-pop-h');
		$('#login').removeClass('ind-pop-h_new');
		
		email_address.removeClass('error_field');
		password.removeClass('error_field');
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(password.val() == '' || password.val().length < 6) {	// Password
			msg	= 'Password must be atleast 6 characters';
			password.addClass('error_field');
			password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			$('#login').addClass('ind-pop-h_new');
			$('#login').removeClass('ind-pop-h');
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-login', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Email is already exist
					msg	= 'Incorrect Email Address or Password';
					email_address.addClass('error_field');
					email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					password.addClass('error_field');
					$('#login').addClass('ind-pop-h_new');
					$('#login').removeClass('ind-pop-h');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#login_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					$('#login').addClass('ind-pop-h_new');
					$('#login').removeClass('ind-pop-h');
				} else if(data == '1') {
					resetLoginForm();
					$('#login').modal('hide');
					showOverlay();
					window.location.href	= '/';
				}
			});
			return false;
		}
	}

/*	Function: resetRegistrationForm
	Form: Registration
*/
	function resetLoginForm() {
		var email_address	= $('#login_email'),
			password		= $('#login_password');
		
		$('.error_msg').remove();
		$('#login').addClass('ind-pop-h');
		$('#login').removeClass('ind-pop-h_new');
		
		email_address.removeClass('error_field');
		password.removeClass('error_field');
		email_address.val('');
		password.val('');
	}
	
/*	Function: resetForgetPasswordForm
	Form: ForgetPassword
*/
	function resetForgetPasswordForm() {
		var email_address	= $('#forget_password_email');
		
		$('.error_msg').remove();
		$('#forget-password').addClass('ind-pop-h4');
		$('#forget-password').removeClass('ind-pop-h4_new');
		
		email_address.removeClass('error_field');
		email_address.val('');
	}

/*	Function: validateForgerPasswordForm
	Form: ForgetPassword
*/
	function validateForgerPasswordForm() {
		var error_flag			= 0,
			msg					= '',
			form				= $('#forget_password_form'),
			email_address		= $('#forget_password_email');
		
		$('.error_msg').remove();
		$('#forget-password').addClass('ind-pop-h4');
		$('#forget-password').removeClass('ind-pop-h4_new');
		
		email_address.removeClass('error_field');
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			$('#forget-password').addClass('ind-pop-h4_new');
			$('#forget-password').removeClass('ind-pop-h4');
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-forget-password', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Email does not exist.
					msg	= 'Email Address does not exist';
					email_address.addClass('error_field');
					email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					$('#forget-password').addClass('ind-pop-h4_new');
					$('#forget-password').removeClass('ind-pop-h4');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#forget-password_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					$('#forget-password').addClass('ind-pop-h4_new');
					$('#forget-password').removeClass('ind-pop-h4');
				} else if(data != '') {
					resetForgetPasswordForm();
					$('#forget-password').modal('hide');
					$('#message_content').html('We have sent an email with instructions to login. Your existing password has been changed.');
					$('#confirmation_modal').click();
				}
			});
			return false;
		}
	}

/*	Function: fetchFBUserInfo
	Form: Login with Facebook
*/
	function fetchFBUserInfo() {
		FB.api('/me', function(response) {
			if(response.email != undefined && response.first_name != undefined && response.last_name != undefined) {
				var user_email		= response.email,
					user_firstname	= response.first_name,
					user_lastname	= response.last_name,
					user_fbuid		= response.id;
					
					$.post('/front/index/validate-fblogin', {user_fbuid: user_fbuid, user_email: user_email, user_firstname: user_firstname, user_lastname: user_lastname}, function(data){
						if($.trim(data) == 1) {
							window.location.reload();
						} else {
							alert('Authentication Failed. Please try again.');
						}
					});
			} else {
				console.log("Error!");
			}
		});
	}

/*	Function: fetchFriendsInfo
	Form: Login with Facebook
*/
	function fetchFriendsInfo() {
		FB.api('/me/friends', function(response) {
			if(response.data) {
				var friends	= response.data;
				for(x in friends) {
					console.log(friends[x].name+'-'+friends[x].id);
				}
			} else {
				console.log("Error!");
			}
		});
	}

/*	Function: fbLogin
	Form: Login with Facebook
*/
	function fblogin() {
		FB.login(function(response) {
			fetchFBUserInfo();
			fetchFriendsInfo();
		}, {scope: 'email,user_likes,read_stream,publish_stream'});
	}

/*	Function: loginWithFB
	Form: Login with Facebook
*/
	function loginWithFB(appID, returnURL, protocolName) {
		var url	= protocolName+"://www.facebook.com/dialog/oauth/?client_id="+appID+"&redirect_uri="+returnURL+"&scope=email,user_activities,user_likes,publish_actions,read_stream,publish_stream";
		window.open(url, 'Login With Facebook', 'location=1,scrollbars=0,toolbar=no,width=500,height=300,top=50,left=50');
	}

/*	Function: loadVideos
	Page: To load videos in Homepage
*/
	function loadVideos(option) {
		showOverlay();
		$.post('/front/index/list-videos/'+option, function(newdata){
			hideOverlay();
			if(option == 0) {
				$('#list_video').html(newdata);
			} else {
				$('#more_videos').remove();
				$('#list_video').append(newdata);
			}
		});
	}

/*	Function: loadRecommended
	Page: To load videos in View Recommended page
*/
	function loadRecommended(option) {
		showOverlay();
		$.post('/front/search/list-recommended/'+option, function(newdata){
			hideOverlay();
			if(option == 0) {
				$('#list_video').html(newdata);
			} else {
				$('#more_videos').remove();
				$('#list_video').append(newdata);
			}
		});
	}

/*	Function: loadWatched
	Page: To load videos in View Watched page
*/
	function loadWatched(option, pagenav) {
		showOverlay();
		$.post('/list-watched/'+option+'/'+pagenav, function(newdata){
			hideOverlay();
			//alert(newdata);
			if(option == 0) {
				$('#list_video').html(newdata);
				if(newdata != '') {
					$('.fb_comments_p').each(function(){
						var url	= $(this).attr('data');
						
						$(this).after('<div class="fb-comments" data-href="'+url+'" data-numposts="5" data-colorscheme="light" data-mobile="false"></div>');
						//$(this).after('<fb:comments href="'+url+'" numposts="5" colorscheme="light"></fb:comments>');
						FB.XFBML.parse();
					});
				}
			} else {
				$('#more_videos').remove();
				$('#list_video').html(newdata);
				if(newdata != '') {
					$('.fb_comments_p').each(function(){
						var url	= $(this).attr('data');
						
						$(this).after('<div class="fb-comments" data-href="'+url+'" data-numposts="5" data-colorscheme="light" data-mobile="false"></div>');
						//$(this).after('<fb:comments href="'+url+'" numposts="5" colorscheme="light"></fb:comments>');
						FB.XFBML.parse();
					});
				}
			}
		});
	}

/*	Function: loadTopVideos
	Page: To load top videos
*/
	function loadTopVideos(option) {
		showOverlay();
		$.post('/front/search/top-voted-videos/'+option, function(newdata){
			hideOverlay();
			if(option == 0) {
				$('#list_video').html(newdata);
			} else {
				$('#more_videos').remove();
				$('#list_video').append(newdata);
			}
		});
	}
	
	$(document).ajaxComplete(function() {
		(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=620939677972746";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
		
		$('.fb_iframe_widget').each(function(){
			$('.fb_iframe_widget').css('width', '100%');
			$('.fb_iframe_widget span').css('width', '100%');
		});
		$('.fb_ltr').each(function(){
			$('.fb_ltr').css('width', '100%');
		});
	});
	
	
/*	Function: loadFriends
	Page: To load friends in Homepage
*/
	function loadFriends(option) {
		showOverlay();
		$.post('/front/friends/list-friends/'+option, function(newdata){
			hideOverlay();
			if(option == 0) {
				$('#friends_list').html(newdata);
			} else {
				$('#more_videos').remove();
				$('#friends_list').append(newdata);
			}
		});
	}

/*	Function: loginWithFB
	Form: Login with Facebook
*/
	function doSearch(option) {
		var search_form;
		if(option == 1) {
			search_field	= $('#navsearch');
		} else if(option == 2) {
			search_field	= $('#navsearch2');
		}
		if($.trim(search_field.val()) == '') {
			//loadVideos(1);
		}
	}

/*	Function: validateEditProfile
	Form: Edit Profile
*/
	function validateEditProfile() {
		var error_flag		= 0,
			msg				= '',
			form			= $('#edit_profile_form'),
			user_photo		= $('#user_photo'),
			first_name		= $('#user_fname'),
			last_name		= $('#user_lname'),
			email_address	= $('#user_email'),
			gender			= $('#user_gender'),
			dob				= $('#user_dob');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		user_photo.removeClass('error_field');
		first_name.removeClass('error_field');
		last_name.removeClass('error_field');
		email_address.removeClass('error_field');
		gender.removeClass('error_field');
		dob.removeClass('error_field');
		
		if(first_name.val() == '') {	// First Name
			msg	= 'Enter your First Name';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(first_name.val().length < 3) {
			msg	= 'First Name must be atleast 3 characters';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alpha_mustbe.test(first_name.val())) {
			msg	= 'First Name contains invalid character';
			first_name.addClass('error_field');
			first_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(last_name.val() == '') {	// Last Name
			msg	= 'Enter your Last Name';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(last_name.val().length < 3) {
			msg	= 'Last Name must be atleast 3 characters';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alpha_mustbe.test(last_name.val())) {
			msg	= 'Last Name contains invalid character';
			last_name.addClass('error_field');
			last_name.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(email_address.val() == '') {	// Email Address
			msg	= 'Enter your Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(email_address.val())) {
			msg	= 'Enter a valid Email Address';
			email_address.addClass('error_field');
			email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(gender.val() == '0') {	// Gender
			msg	= 'Select your Gender';
			gender.addClass('error_field');
			gender.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		/*	if(dob.val() == '' || dob.val() == "Date of Birth") {	// DOB
			msg	= 'Date of birth is invalid';
			registration_confirm_password.addClass('error_field');
			registration_confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}	*/
		
		if(error_flag) {
			return false;
		} else {
			if($('#user_photo').val() != '') {
				if($('#user_photo')[0].files[0].size >= 2097152) {
					//console.log($('#user_photo')[0].files[0].size);
					msg	= 'Photo should not exceed 2MB.';
					user_photo.addClass('error_field');
					user_photo.parent().append("<div class='error_msg'>"+msg+"</div>");
					error_flag	= 1;
					return false;
				}
				var ext = $('#user_photo').val().split('.').pop().toLowerCase();
				if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
				    msg	= 'Invalid extension.';
					user_photo.addClass('error_field');
					user_photo.parent().append("<div class='error_msg'>"+msg+"</div>");
					error_flag	= 1;
					return false;
				}
			
				$.ajaxFileUpload({
					url:'/front/index/profile-photo',
					secureuri:false,
					fileElementId:'user_photo',
					dataType: 'json',
					data:'',
					success: function (data, status) {
						if(typeof(data.error) != 'undefined') {
							
							if($.trim(data.error) != '') {
								msg	= data.error;
								user_photo.addClass('error_field');
								user_photo.parent().append("<div class='error_msg'>"+msg+"</div>");
								error_flag	= 1;
								return false;
							} else {
								//	success
								$('#user_photo_name').val(data.filename);
								showOverlay();
								$.post('/front/index/validate-editprofile', form.serialize(), function(data){
									hideOverlay();
									data	= $.trim(data);
									if(data == 2) {
										msg	= "User session is expired. Please do login & try again.";
										$('#edit_profile_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
									} else if(data == -1) {	// Email is already exist
										msg	= 'Email Address is already registered';
										email_address.addClass('error_field');
										email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
									} else if(data == 0) {	//	Improper request
										msg	= "Oops!..Please try again later";
										$('#edit_profile_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
									} else if(data != '') {
									
										$('#ses_firstname').html($('#user_fname').val());
										$('#ses_lastname').html($('#user_lname').val());
										$('#ses_email').html($('#user_email').val());
										$('#ses_dob').html($('#user_dob').val());
										$('#ses_gender').html($('#user_gender').val());
										$('#ses_photo').html('/Front/users/photo/thumb/'+$('#user_photo_name').val());
										
										msg	= "Your profile has been updated.";
										$('#edit_profile_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
										$("#edit_profile_popup").animate({ scrollTop: 0 }, 600);
									}
								});
								return false;
							}
						}
					}, error: function (data, status, e) {
						msg	= e;
						user_photo.addClass('error_field');
						user_photo.parent().append("<div class='error_msg'>"+msg+"</div>");
						error_flag	= 1;
						return false;
					}
				});
				return false;
			} else {
				showOverlay();
				$.post('/front/index/validate-editprofile', form.serialize(), function(data){
					hideOverlay();
					data	= $.trim(data);
					if(data == 2) {
						msg	= "User session is expired. Please do login & try again.";
						$('#edit_profile_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					} else if(data == -1) {	// Email is already exist
						msg	= 'Email Address is already registered';
						email_address.addClass('error_field');
						email_address.parent().append("<div class='error_msg'>"+msg+"</div>");
					} else if(data == 0) {	//	Improper request
						msg	= "Oops!..Please try again later";
						$('#edit_profile_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
					} else if(data != '') {
		
						$('#ses_firstname').html($('#user_fname').val());
						$('#ses_lastname').html($('#user_lname').val());
						$('#ses_email').html($('#user_email').val());
						$('#ses_dob').html($('#user_dob').val());
						$('#ses_gender').html($('#user_gender').val());
						if($('#user_photo_name').val() != '') {
							$('#ses_photo').html('/Front/users/photo/thumb/'+$('#user_photo_name').val());
						} else {
							$('#ses_photo').html('/Front/img/no_photo.png');
						}
						msg	= "Your profile has been updated.";
						$('#edit_profile_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
						$("#edit_profile_popup").animate({ scrollTop: 0 }, 600);
					}
				});
				return false;
			}
		}
	}
	
/*	Event	*/

/*	$('#edit_profile_form').click(function(){
	window.location.reload();
});	*/
	
	
/*	Method: resetProfileForm
	Form: Edit Profile Form
*/
	
	function resetProfileForm() {
		var	user_firstname	= $('#user_fname'),
			user_lastname	= $('#user_lname'),
			user_email		= $('#user_email'),
			user_dob		= $('#user_dob'),
			user_gender		= $('#user_gender'),
			user_photo		= $('#user_photo'),
			user_avatar		= $('#user_avatar'),
			
			ses_user_firstname	= $('#ses_firstname'),
			ses_user_lastname	= $('#ses_lastname'),
			ses_user_email		= $('#ses_email'),
			ses_user_dob		= $('#ses_dob'),
			ses_user_gender		= $('#ses_gender'),
			ses_user_photo		= $('#ses_photo');
			
			$('.error_msg').remove();
			$('.success_msg').remove();
			
			user_firstname.removeClass('error_field');
			user_lastname.removeClass('error_field');
			user_email.removeClass('error_field');
			user_dob.removeClass('error_field');
			user_gender.removeClass('error_field');
			user_photo.removeClass('error_field');
			user_avatar.removeClass('error_field');
			
			user_firstname.val(ses_user_firstname.html());
			user_lastname.val(ses_user_lastname.html());
			user_email.val(ses_user_email.html());
			user_dob.val(ses_user_dob.html());
			user_gender.val(ses_user_gender.html());
			user_avatar.attr('src', ses_user_photo.html());
			user_photo.val('');
	}
	
/*	Method: validateChangePasswordForm
	Form: Change Password
*/
	 
	 function validateChangePasswordForm() {
	 	var error_flag			= 0,
			msg					= '',
			form				= $('#change_password_form'),
			current_password	= $('#current_password'),
			new_password		= $('#new_password'),
			confirm_password	= $('#new_confirm_password');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		current_password.removeClass('error_field');
		new_password.removeClass('error_field');
		confirm_password.removeClass('error_field');
		
		if(current_password.val() == '' || current_password.val().length < 6) {	// Password
			msg	= 'Current Password must be atleast 6 characters';
			current_password.addClass('error_field');
			current_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		if(new_password.val() == '' || new_password.val().length < 6) {	// Password
			msg	= 'New Password must be atleast 6 characters';
			new_password.addClass('error_field');
			new_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alphanumeric_alteast.test(new_password.val())) {
			msg	= 'New Password must contain atleast 1 alphanumeric character';
			new_password.addClass('error_field');
			new_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		if(confirm_password.val() == '') {	// Confirm Password
			msg	= 'Confirm Password must be confirmed';
			confirm_password.addClass('error_field');
			confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(confirm_password.val() != new_password.val()) {
			msg	= 'Passwords do not match';
			confirm_password.addClass('error_field');
			confirm_password.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			return false;
		} else {
			showOverlay();
			$.post('/front/index/validate-change-password', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == -1) {	// Current Password does not match
					msg	= 'Current Password does not match';
					current_password.addClass('error_field');
					current_password.parent().append("<div class='error_msg'>"+msg+"</div>");
					$('#login').addClass('ind-pop-h_new');
					$('#login').removeClass('ind-pop-h');
				} else if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#change_password_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == '1') {
					resetChangePasswordForm();
					msg	= "Password has been changed.";
					$('#change_password_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
					//$('#change_password').modal('hide');
					//showOverlay();
					//window.location.href	= '/';
				}
			});
			return false;
		}
	 }
	 
/*	Function: resetChangePasswordForm
	Form: ChangePassword
*/
	function resetChangePasswordForm() {
		var current_password	= $('#current_password'),
			new_password		= $('#new_password'),
			confirm_password	= $('#new_confirm_password');
		
		$('.error_msg').remove();
		current_password.removeClass('error_field');
		new_password.removeClass('error_field');
		confirm_password.removeClass('error_field');
		current_password.val('');
		new_password.val('');
		confirm_password.val('');
	}
	
/*	Method: shareonfb
	Page: Video View Page
*/
	function shareonfb(APP_ID, share_name, share_caption, share_link, share_description, share_picture, redirect_uri) {
		var popupWidth=500;
		var popupHeight=300;
		var xPosition=($(window).width()-popupWidth)/2;
		var yPosition=($(window).height()-popupHeight)/2;
		var shareUrl="https://www.facebook.com/dialog/feed?"+
					"app_id="+APP_ID+
					"&display=popup"+
					"&name="+share_name+
					"&caption="+share_caption+
					"&link="+share_link+
					"&description="+share_description+
					"&picture="+share_picture+
					"&redirect_uri="+redirect_uri+"/fbreturn/";

		window.open(shareUrl, "Share Window", "location=1, scrollbars=1, width="+popupWidth+", height="+popupHeight+", left="+xPosition+", top="+yPosition);
	}
	
/*	Method: dovote
	Page: Video View Page
*/
	function dovote(type, videoId, title) {
		showOverlay();
		$.post('/front/search/vote-video', {type:type, videoId: videoId}, function(data){
			data	= $.trim(data);
			title	= '"'+title+'"';
			if(data == -1) {	// Oops Technical issue
				hideOverlay();
				alert(data);
			} else if(data == 0) {
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Snapstate.com is free and open to everyone, so we request you to register and get extra features like voting, add to playlist & etc.');
				$('#confirmation_modal').click();
				hideOverlay();
			} else if(data == 1 || data == 2) {
				if(type == 1) {
					var like	= $('#likebar').html();
					like		= parseInt(like, 10);
					like++;
					$('#likebar').html(like);
				} else if(type == 2) {
					var dislike	= $('#dislikebar').html();
					dislike		= parseInt(dislike, 10);
					dislike++;
					$('#dislikebar').html(dislike);
				}
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Thanks for voting the video - '+title);
				$('#confirmation_modal').click();
				hideOverlay();
			} else if(data == -2) {
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('You have already voted the video - '+title);
				$('#confirmation_modal').click();
				hideOverlay();
			}
		});
	}

/*	Function: inviteViaFB
	Page: Invite Friends
*/
	function inviteViaFB(APP_ID, message, redirectURI) {
		$('#invite').modal('hide');
		FB.ui({method: 'apprequests',
				title  : 'Invite Friends',
				//filters: ['app_non_users'],
				message: 'Friends, join in Snapstate.com now and get more benifits & have fun - Snap yourself into state.'
		},
		function (response) {
   	        
        });
        return false;
		
		/*	
		$('#invite').modal('hide');
		var popupWidth=500;
		var popupHeight=300;
		var xPosition=($(window).width()-popupWidth)/2;
		var yPosition=($(window).height()-popupHeight)/2;
		var inviteUrl	= "https://www.facebook.com/dialog/apprequests?app_id="+APP_ID+"&message="+message+"&redirect_uri="+redirectURI+"&display=popup";

		window.open(inviteUrl, "Invite Friends", "location=1, scrollbars=1, width="+popupWidth+", height="+popupHeight+", left="+xPosition+", top="+yPosition);
		*/
	}
	
	var email_counter	= 0;
	var emailArray		= [];
	
/*	Function: addEmail
	Form: Invite Friends - Email Form
*/
	function addEmail() {
		var email_address	= $('#add_email').val();
		var msg	= "";
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		if(email_counter >= 10) {
			msg	= "Maximum 10 email addresses will be allowed to Invite per form";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
			$('#add_email').val('');
			return false;
		}
		
		if(email_address == '' || email_address == undefined) {
			msg	= "Enter the email address";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
		} else if($.inArray(email_address, emailArray) != -1) {	
			msg	= "Email address is already added";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
		} else if(email_address != undefined && emailRegex.test(email_address)) {
			emailArray.push(email_address);
			var html_content	= '<div class="row newlyadded" id="div_add_email'+email_counter+'">'+
										'<div class="col-md-10 col-xs-10 clearfix">'+
											'<input type="text" id="add_email'+email_counter+'" name="add_email'+email_counter+'" value="'+email_address+'" readonly class="wid100">'+
										'</div>'+
										'<div class="col-md-2 col-xs-2 clearfix">'+
											'<button class="minus-btn" onclick="removeEmail('+email_counter+');return false;">&#120;</button>'+
										'</div>'+
									'</div>';
			$('#new_email').append(html_content);
			email_counter++;
			$('#add_email').val('');
		} else {
			msg	= "Email address is invalid";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
		}
		return false;
	}
	
/*	Function: removeEmail
	Form: Invite Friends - Email Form
*/
	function removeEmail(id) {
		var msg	= "";
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		if($('#div_add_email'+id).length > 0) {
			var index = emailArray.indexOf($('#add_email'+id).val());
			if (index > -1) {
			    emailArray.splice(index, 1);
			}
			$('#div_add_email'+id).remove();
			email_counter--;
		} else {
			msg	= "Oops! Please refresh & try again";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
		}
	}

/*	Function: resetEmailInvitation
	Form: Invite Friends - Email Form
*/
	function resetEmailInvitation() {
		$('.error_msg').remove();
		$('.success_msg').remove();
		$('.newlyadded').remove();
		$('#add_email').val('');
		email_counter	= 0;
		emailArray		= [];
	}
	
/*	Function: sendInvitation
	Form: Invite Friends - Email Form
*/
	function sendInvitation() {
		var email_address	= $('#add_email').val();
		var msg	= "";
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		if(email_counter >= 10 && email_address != '') {
			
			msg	= "Maximum 10 email addresses will be allowed to Invite per form";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
			$('#add_email').val('');
			return false;
			
		} else if(email_counter < 10 && email_address != '') {
			if($.inArray(email_address, emailArray) != -1) {	
				msg	= "Email address is already added";
				$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
				return false;
			} else if(!emailRegex.test(email_address)) {
				msg	= "Email address is invalid";
				$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
				return false;
			} else {
				emailArray.push(email_address);
				var html_content	= '<div class="row newlyadded" id="div_add_email'+email_counter+'">'+
											'<div class="col-md-10 col-xs-10 clearfix">'+
												'<input type="text" id="add_email'+email_counter+'" name="add_email'+email_counter+'" value="'+email_address+'" readonly class="wid100">'+
											'</div>'+
											'<div class="col-md-2 col-xs-2 clearfix">'+
												'<button class="minus-btn" onclick="removeEmail('+email_counter+');return false;">&#120;</button>'+
											'</div>'+
										'</div>';
				$('#new_email').append(html_content);
				email_counter++;
				$('#add_email').val('');
				triggerInvitation();
			}
			return false;
			
		} else if(email_counter > 0) {
			triggerInvitation();
		} else {
			msg	= "Enter the email address";
			$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
			return false;
		}
	}

/*	Function: triggerInvitation
	Form: Invite Friends - Email Form
*/
	function triggerInvitation() {
		showOverlay();
		var msg	= '';
		$.post('/front/friends/invite-via-email', {emails : JSON.stringify( emailArray )}, function(data){
			hideOverlay();
			if(data == 1) {
				resetEmailInvitation();
				msg	= "Congrats! Your invitation has been sent.";
				$('#new_email').children().first().before("<div class='success_msg'>"+msg+"</div>");
				return false;
			} else {
				msg	= "Oops! Please refresh the page & try again";
				$('#new_email').children().first().before("<div class='error_msg'>"+msg+"</div>");
				return false;
			}
		});
	}
	
/*	Function: showPreview
	Form: Show Preview
*/
	function showPreview(input) {
		var user_photo		= $('#user_photo');
		var msg				= '',
			error_flag		= 0;
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		user_photo.removeClass('error_field');
		
		if($('#user_photo')[0].files[0].size > 2097152) {
			msg	= 'Photo should not exceed 2MB.';
			user_photo.addClass('error_field');
			user_photo.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
			return false;
		}
		var ext = $('#user_photo').val().split('.').pop().toLowerCase();
		if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
		    msg	= 'Invalid extension.';
			user_photo.addClass('error_field');
			user_photo.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
			return false;
		}
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function (e) {
								var f = input.files[0]; 
								if(f.type != 'image/jpeg' && f.type != 'image/jpg' && f.type != 'image/gif' && f.type != 'image/png') {
									//alert("Site Logo must be JPG, GIF or PNG");
									return false;
								}
								$('#user_avatar').attr('src', e.target.result).width(100).height(100);
								//$('#user_avatar').val(e.target.result);
							};
			reader.readAsDataURL(input.files[0]);
		}
	}
	
/*	Function: filterSearch
	Form: Search
*/

	function filterSearch(option) {
		if(option == 1) {
			$('#category').val($('#filter_category').val());
			$('#ranking').val($('#filter_ranking').val());
			$('#length').val($('#filter_length').val());
			$('#friend').val($('#filter_friend').val());
			$('#seen').val($('#filter_seen').val());
		} else if(option == 2) {
			$('#category').val($('#small_filter_category').val());
			$('#ranking').val($('#small_filter_ranking').val());
			$('#length').val($('#small_filter_length').val());
			$('#friend').val($('#small_filter_friend').val());
			$('#seen').val($('#small_filter_seen').val());
		}
		$('#filter_form').submit();
	}
	
	function doNotify() {
		$('#myModalLabel2').html('Notification');
		$('#message_content').html('Snapstate.com is free and open to everyone, so we request you to register and get extra features like voting, add to playlist & etc.');
		$('#confirmation_modal').click();
	}
	
/*	Method: validateContributeForm
	Form: Contribute Video Form
*/
	 
	function validateContributeForm(option) {
		var error_flag				= 0,
			msg						= '',
			form					= $('#contribution-form'),
			contribution_url		= $('#contribute_video_url'),
			contribution_title		= $('#contribute_video_title'),
			contribution_desc		= $('#contribute_video_desc'),
			contribution_category	= $('#contribute_video_category'),
			contribution_tags		= $('#contribute_video_tags');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		contribution_url.removeClass('error_field');
		contribution_title.removeClass('error_field');
		contribution_category.removeClass('error_field');
		
		if($.trim(contribution_url.val()) == '') {	// Url
			msg	= 'Please Enter the Video URL';
			contribution_url.addClass('error_field');
			contribution_url.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!validYoutubeURL($.trim(contribution_url.val()))) {
			msg	= 'Video URL is invalid';
			contribution_url.addClass('error_field');
			contribution_url.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		if($.trim(contribution_title.val()) == '') {	// Title
			msg	= 'Please Enter the Media Title';
			contribution_title.addClass('error_field');
			contribution_title.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if($.trim(contribution_title.val()).length < 3) {
			msg	= 'Media Title must be at least 3 characters long';
			contribution_title.addClass('error_field');
			contribution_title.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if($.trim(contribution_title.val()).length > 300) {
			msg	= 'Media Title should not exceed 300 characters';
			contribution_title.addClass('error_field');
			contribution_title.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		if($.trim(contribution_category.val()) == '' || $.trim(contribution_category.val()) == '0') {	// Category
			msg	= 'Please select the Category';
			contribution_category.addClass('error_field');
			contribution_category.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			return false;
		} else {
			showOverlay();
			$.post('/front/search/post-contributed-video', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#contributed_video_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == '1') {
					
					$(".tags").val('').trigger("chosen:updated");
					
					resetContributeForm();
					msg	= "Success, your video has been posted.";
					$('#contributed_video_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
				} else if(data == '-1') {	//	Session expired
					msg	= "Your Session has expired!. Please do login & try again.";
					$('#contributed_video_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == 3) {
					msg	= "Video Length is exceeded.(Accepted Length: Max. 10mins)";
					$('#contributed_video_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				}
			});
			return false;
		}
	}
	
/*	Method: validateContributeForm
	Form: Contribute Video Form
*/

	function validYoutubeURL(url) {
		var p = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/;
		return (url.match(p)) ? true : false;
	}
	
/*	Function: resetContributeForm
	Form: Contribute Videos
*/
	function resetContributeForm() {
		var form					= $('#contribution-form'),
			contribution_url		= $('#contribute_video_url'),
			contribution_title		= $('#contribute_video_title'),
			contribution_desc		= $('#contribute_video_desc'),
			contribution_category	= $('#contribute_video_category'),
			contribution_tags		= $('#contribute_video_tags');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		contribution_url.removeClass('error_field');
		contribution_title.removeClass('error_field');
		contribution_category.removeClass('error_field');
		
		contribution_url.val('');
		contribution_title.val('');
		contribution_desc.val('');
		contribution_category.val('');
		contribution_tags.val('');
	}
	
/*	Function: loadContributed
	Page: To load videos in View Contributed page
*/
	function loadContributed(option, pagenav) {
		showOverlay();
		$.post('/list-contributed/'+option+'/'+pagenav, function(newdata){
			hideOverlay();
			if(option == 0) {
				$('#list_video').html(newdata);
			} else {
				$('#more_videos').remove();
				$('#list_video').html(newdata);
			}
		});
	}
	
/*	Function: approveVideo
	Page: Contribute Video Listing
*/
	function approveVideo(mediaId, option) {
		var flag	= $('#flag_'+mediaId).val();
		showOverlay();
		$.post('/front/search/video-approval', {option: option, mediaId: mediaId, flag: flag}, function(data){
			hideOverlay();
			if(option == 1) {
				loadContributed(0, 1);
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Video has been approved successfully.');
				$('#confirmation_modal').click();
			} else if(option == 2) {
				loadContributed(0, 1);
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Video has been disapproved.');
				$('#confirmation_modal').click();
			} else if(option == 0) {
				//	session expired
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Your session has expired. Please do login & try again.');
				$('#confirmation_modal').click();
			} else {
				//	failure
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Oops! Please try again later.');
				$('#confirmation_modal').click();
			}
		});
	}
	
/*	Method: validateFeedback
	Form: Feedback Form
*/
	 
	function validateFeedback() {
		var error_flag			= 0,
			msg					= '',
			form				= $('#feedback_form'),
			feedback_email		= $('#feedback_email'),
			feedback_category	= $('#feedback_category'),
			feedback_message	= $('#feedback_message');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		feedback_email.removeClass('error_field');
		feedback_category.removeClass('error_field');
		feedback_message.removeClass('error_field');
		
		if($.trim(feedback_email.val()) == '') {	// Email
			msg	= 'Enter your email address';
			feedback_email.addClass('error_field');
			feedback_email.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(feedback_email.val())) {
			msg	= 'Enter a valid email address';
			feedback_email.addClass('error_field');
			feedback_email.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		if($.trim(feedback_category.val()) == '') {	// Category
			msg	= 'Select the category';
			feedback_category.addClass('error_field');
			feedback_category.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} 
		if($.trim(feedback_message.val()) == '') {	// Message
			msg	= 'Enter your comments';
			feedback_message.addClass('error_field');
			feedback_message.parent().append("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			return false;
		} else {
			showOverlay();
			$.post('/front/index/post-feedback', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					$('#feedback_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == 1) {
					resetFeedbackForm();
					msg	= "Success, your comments has been sent.";
					$('#feedback_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
				}
			});
			return false;
		}
	}

/*	Function: resetFeedbackForm
	Form: Feedback Form
*/
	function resetFeedbackForm() {
		var form				= $('#feedback_form'),
			feedback_email		= $('#feedback_email'),
			feedback_category	= $('#feedback_category'),
			feedback_message	= $('#feedback_message');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		feedback_email.removeClass('error_field');
		feedback_category.removeClass('error_field');
		feedback_message.removeClass('error_field');
		if($.trim($('#hidden_feedback_email').val()) != '') {
			feedback_email.val($('#hidden_feedback_email').val());
		} else {
			feedback_email.val('');
		}
		feedback_category.val('');
		feedback_message.val('');
	}
	
/*	Function: validateNewsletter
	Form: Newsletter Subscription Form
*/
	function validateNewsletter() {
		var error_flag			= 0,
			msg					= '',
			form				= $('#newsletter_form'),
			newsletter_email	= $('#newsletter_email');
		
		$('.error_msg').remove();
		newsletter_email.css('border', '');
		newsletter_email.removeClass('error_field');
		
		if($.trim(newsletter_email.val()) == '') {	// Email
			msg	= 'Enter your email address';
			newsletter_email.addClass('error_field');
			newsletter_email.css('border', '1px solid red');
			form.parent().append("<div class='error_msg' style='padding-left:10px;color:#FD924E;'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!emailRegex.test(newsletter_email.val())) {
			msg	= 'Enter a valid email address';
			newsletter_email.addClass('error_field');
			newsletter_email.css('border', '1px solid red');
			form.parent().append("<div class='error_msg' style='padding-left:10px;color:#FD924E;'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			return false;
		} else {
			showOverlay();
			$.post('/front/index/subscribe-newsletter', form.serialize(), function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					form.parent().append("<div class='error_msg' style='padding-left:10px;color:#FD924E;'>"+msg+"</div>");
				} else if(data == 1) {
					newsletter_email.val('');
					$('#message_content').html('Thanks for subscription!  A confirmation email has been sent to your email address.');
					$('#confirmation_modal').click();
				} else if(data == -1) {	//	Already subscribed
					msg	= "Email is already subscribed";
					form.parent().append("<div class='error_msg' style='padding-left:10px;color:#FD924E;'>"+msg+"</div>");
				}
			});
			return false;
		}
	}
	
/*	UI aspects 
	Login Form
*/
	$('#login_email, #login_password').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			validateLoginForm();
		}
	});

/*	Methos: createPlaylist
	Form: Playlist
*/
	var rand_playlist_id	= 0;
	function createPlaylist(option) {
		var error_flag			= 0,
			msg					= '',
			new_playlist_name	= $('#new_playlist_name'),
			playlist_name		= $('#playlist_name'),
			new_modal_name		= $('#create_playlist_modal'),
			modal_name			= $('#playlist_modal');
		
		if(option == 1) {
			playlist_name	= new_playlist_name;
			modal_name		= new_modal_name;
		} else {
			playlist_name	= playlist_name;
			modal_name		= modal_name;
		}
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		playlist_name.removeClass('error_field');
		
		if($.trim(playlist_name.val()) == '') {
			msg	= 'Enter the Playlist name';
			playlist_name.addClass('error_field');
			modal_name.children().first().before("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		} else if(!alphanumeric_mustbe_ws.test(playlist_name.val())) {
			msg	= 'Playlist name must be alphanumeric character';
			playlist_name.addClass('error_field');
			modal_name.children().first().before("<div class='error_msg'>"+msg+"</div>");
			error_flag	= 1;
		}
		
		if(error_flag) {
			return false;
		} else {
			showOverlay();
			var video_id	= $('#pl_video_id').val();	// option 2
			$.post('/front/search/create-playlist', {playlist: $.trim(playlist_name.val()), video_id: video_id}, function(data){
				hideOverlay();
				data	= $.trim(data);
				if(data == 0) {	//	Improper request
					msg	= "Oops!..Please try again later";
					modal_name.children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == -1) {
					resetFeedbackForm();
					msg	= "Your session has expired. Please do login & try again.";
					modal_name.children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else {
					resetFeedbackForm();
					
					if(option == 1) {
						msg	= "Playlist has been created";
					} else {
						msg	= "Playlist "+'"'+playlist_name.val()+'"'+" has been created & also added the video to the playlist";
					}
					modal_name.children().first().before("<div class='success_msg'>"+msg+"</div>");
					
					//	Add to playlist popup
					$( "<li class='cc"+data+"'><div class='radio'><label><input type='radio' id='newoptionsRadios"+rand_playlist_id+"' name='optionsRadios' class='playlist_radio'>"+playlist_name.val()+"</label></div></li>" ).insertAfter( "#playlist_listing li:nth-last-child(3)" );
					$("#newoptionsRadios"+rand_playlist_id).attr('onchange', 'addToPlaylist(1, "'+data+'", "'+playlist_name.val()+'");');
					$("#newoptionsRadios"+rand_playlist_id).val(playlist_name.val());
					
					//	playlist popup
					$( "<li class='c"+data+" hasplaylists'><a id='playlist_link' href='#'>"+playlist_name.val()+"</a><a class='icon-delete' href='#' id='xnewoptionsRadios"+rand_playlist_id+"'><img style='height:16px; width:16px;margin-left:10px;' src='/Front/img/btn-delete.png' title='Delete' alt='Delete'></a>").insertAfter( "#top-nav-playlist li:nth-last-child(2)" );
					$("#xnewoptionsRadios"+rand_playlist_id).attr('onclick', 'deletePlaylist("'+data+'", "'+playlist_name.val()+'");');
					$('#playlist_link').attr('href', '/playlist/'+data);
					
					rand_playlist_id++;
					
					new_playlist_name.val('');
					playlist_name.val('');
					
					if($('.noplaylists').length > 0) {
						$('#noplaylists').hide();
						$('#page_noplaylists').hide();
					}
				} 
			});
			return false;
		}
	}
	
/*	Function: resetPlaylistForm
	Form: Playlist Form
*/
	function resetPlaylistForm() {
		var new_playlist_name	= $('#new_playlist_name'),
			playlist_name		= $('#playlist_name'),
			new_modal_name		= $('#create_playlist_modal'),
			modal_name			= $('#playlist_modal');
		
		$('.error_msg').remove();
		$('.success_msg').remove();
		new_playlist_name.removeClass('error_field');
		playlist_name.removeClass('error_field');
		new_playlist_name.val('');
		playlist_name.val('');
	}
	
/*	Function: addToPlaylist
	Page: To add a video to playlist
*/
	function addToPlaylist(option, playlist_id, playlist_name) {
		showOverlay();
		$('.error_msg').remove();
		$('.success_msg').remove();
		
		var new_playlist_name_element	= $('#new_playlist_name'),
			playlist_name_element		= $('#playlist_name');
		
		new_playlist_name_element.removeClass('error_field');
		playlist_name_element.removeClass('error_field');
		
		var video_id	= $('#pl_video_id').val();
		if(video_id != '' && playlist_id != '') {
			$.post('/add-to-playlist', {playlist_id: playlist_id, video_id: video_id}, function(data){
				hideOverlay();
				if(data == 0) {
					msg	= "Oops!..Please try again later";
					$('#playlist_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == -1) {
					msg	= "User session is expired.";
					$('#playlist_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
				} else if(data == 1) {
					msg	= "Video has been added to the playlist - "+'"'+playlist_name+'"';
					$('#playlist_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
					$('.playlist_radio').prop('checked', false);
				}
			});
		}
	}
	
/*	Function: deletePlaylist
	Page: To delete a playlist
*/
	function deletePlaylist(playlist_id, playlist_name) {
		showOverlay();
		$('.error_msg').remove();
		$('.success_msg').remove();
		$.post('/front/search/delete-playlist', {playlist_id: playlist_id}, function(data){
			hideOverlay();
			if(data == 0) {
				msg	= "Oops!..Please try again later";
				$('#create_playlist_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
			} else if(data == -1) {
				msg	= "User session is expired.";
				$('#create_playlist_modal').children().first().before("<div class='error_msg'>"+msg+"</div>");
			} else if(data == 1) {
				$('.c'+playlist_id).remove();
				$('.cc'+playlist_id).remove();
				msg	= '"'+playlist_name+'"'+" Playlist has been deleted";
				$('#create_playlist_modal').children().first().before("<div class='success_msg'>"+msg+"</div>");
				
				if($('.hasplaylists').length <= 0 && $('.noplaylists').length <= 1) {
					$('#noplaylists').show();
					$('#page_noplaylists').show();
				}
			}
		});
	}

/*	Function: loadPlaylistVideos
	Page: To load videos in Homepage
*/
	function loadPlaylistVideos(option) {
		showOverlay();
		$.post('/front/search/list-playlist-videos/'+option, function(data){
			hideOverlay();
			if(option == 0) {
				$('#list_video').html(data);
			} else {
				$('#more_videos').remove();
				$('#list_video').append(data);
			}
		});
	}

/*	Function: deletePlaylistMedia
	Page: To delete video from the playlist
*/
	function deletePlaylistMedia(id) {
		showOverlay();
		$.post('/front/search/delete-playlist-videos/', {id:id}, function(data){
			hideOverlay();
			var videoCount	= $('#video_count').html();
			videoCount	= parseInt(videoCount.replace(/[^0-9-]/,""),10);
			videoCount	= videoCount-1;
			$('#video_count').html(videoCount+' videos found');
			$('#myModalLabel2').html('Notification');
			if(data == 1) {
				$('#message_content').html('Video has been removed from Playlist.');
				$('#confirmation_modal').click();
			} else if(data == -1) {
				$('#message_content').html('User session is expired');
				$('#confirmation_modal').click();
			} else {
				$('#message_content').html('Oops!..Please try again later.');
				$('#confirmation_modal').click();
			}
			$('#dd'+id).remove();
			loadPlaylistVideos(0);
		});
	}

/*	Function: showExtendedFriends
	Page: To fetch the extended friends
*/
	function showExtendedFriends(mediaId) {
		showOverlay();
		$.post('/front/search/show-extended-friends/', {media_id: mediaId}, function(data){
			hideOverlay();
			if($.trim(data) == '0') {
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('Oops!..Please try again later.');
				$('#confirmation_modal').click();
			} else if($.trim(data) == '-1') {
				$('#myModalLabel2').html('Notification');
				$('#message_content').html('User session is expired. Please do login & try again.');
				$('#confirmation_modal').click();
			} else {
				$('#extended_friends_modal_list').html(data);
				$('#fri-pop-trigger').click();
			}
		});
	}