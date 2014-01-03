	var reg = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/;
	
	function validateLogin() {
		var username = $.trim($('#login_email').val());
		var password = $('#password').val();
		if((username == '') && (password == '')) {
			$('.alert-error').html('Please enter the Email Address and Password.');
			$('.alert-error').show();
			$('#login_email').focus();
			return false;
		} else if(username == '') {
			$('.alert-error').html('Please enter the Email Address.');
			$('.alert-error').show();
			$('#login_email').focus();
			return false;
		} else if(!reg.test(username)) {
			$('.alert-error').html('Please enter the Valid Email Address.');
			$('.alert-error').show();
			$('#login_email').focus();
			return false;
		} else if(password == '') {
			$('.alert-error').html('Please enter the Password.');
			$('.alert-error').show();
			$('#password').focus();
			return false;
		} else if(password.length < 6) {
			$('.alert-error').html('Password must be at least 6 characters long.');
			$('.alert-error').show();
			$('#password').focus();
			return false;
		}
	}
	function showForgetPassword() {
		$('.alert-error').hide();
		$('#loginDiv').hide();
		$('#forgetPasswordDiv').show();
		$('#resetbuttonpart').show();
		$('#emailaddress').show();
		$('#email').val('');
		$('#login_info').html('Enter the email address that you used when you signed up. We will then send you a link to reset your password.');
	}
	function showLogin() {
		$('.alert-error').hide();
		$('.alert-success').hide();
		$('#forgetPasswordDiv').hide();
		$('.alert-info').show();
		$('#loginDiv').show();
		$('#login_email, #password').val('');
		$('#login_info').html('Please login with your Email Address and Password.');
	}
	function validateEmail() {
		var email = $.trim($('#email').val());
		if(email == '') {
			$('.alert-error').html('Please enter the Email address.');
			$('.alert-error').show();
			$('#email').focus();
			return false;
		} else if(!reg.test(email)){
			$('.alert-error').html('Please enter the Valid Email address.');
			$('.alert-error').show();
			$('#email').focus();
			return false;
		}
		
		$.post('/cms/index/forget-password', {email : email}, function(data) {
			data	= $.trim(data);
			if(data == 1) {
				$('#emailaddress').hide();
				$('.alert-error').hide();
				$('.alert-info').hide();
				$('#resetbuttonpart').hide();
				$('.alert-success').html('We have sent you a mail. Please check your inbox.');	/*	We have sent you a link to reset your password.	*/
				$('.alert-success').show();
			} else if(data == 0) {
				$('.alert-error').html('Email address does not exist. Please enter the Valid Email address.');
				$('.alert-error').show();
				$('#email').focus();
				return false;
			}
		});
		return false;
	}
	$('#email').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			validateEmail();
			return false;
		}
	});
	
	
	$(document).ready(function() {
		$("#addUser").validate({
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "user_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element) {
		 		$(element).closest('div.control-group').addClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		unhighlight: function(element) {
	    		$(element).closest('div.control-group').removeClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		rules: {
	       		user_firstname: {
		   		required: true,
				minlength: 3,
				maxlength: 200
		 		},
	     		user_lastname: {
	       		required: true,
				minlength: 3,
				maxlength: 200
	     		},
				user_email: {
				required: true,
				email: true
				},
				user_password: {
				required: true,
				minlength: 6,
				maxlength: 200
				},
				user_group: {
				required: true
				},
				/*	user_dob: {
				required: true,
				validdate: true,
				date: true
				},	*/
				/*	user_gender: {
				required: true
				},	*/
				user_status: {
				required: true
				}
	   		},
	   		messages: {
	     		user_firstname: {
	                        required: "Please Enter First Name",
	                        minlength: "First Name must be at least 3 characters long",
							maxlength: "First Name should not exceed 200 characters"
	                    },
	     		user_lastname: {
	                        required: "Please Enter Last Name",
	                        minlength: "Last Name must be at least 3 characters long",
							maxlength: "Last Name should not exceed 200 characters"
	                    },
				user_email: {
							required: "Please Enter the Email Address",
							email: "Please Enter Valid Email Address"
				},
				user_password: {
							required: "Please Enter User Password",
							minlength: "Password must be at least 6 characters long",
							maxlength: "Password should not exceed 200 characters"
				},
				user_group: {
							required: "Please Select User Group"
				},
				/*	user_dob: {
							required: "Please Enter the Date of Birth",
							validdate: "Please Enter the Valid Date"
				},	*/
				/*	user_gender: {
							required: "Please Select Gender"
				},	*/
				user_status: {
							required: "Please Select User Status"
				}
						
	   		}
		})
		$('#addRole').validate({
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "role_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element) {
		 		$(element).closest('div.control-group').addClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		unhighlight: function(element) {
	    		$(element).closest('div.control-group').removeClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		rules: {
				role_name: {
				required: true
				},
	       		role_status: {
				required: true
				}
	   		},
	   		messages: {
				role_name: {
							required: "Please Enter the Role name"
				},
				role_status: {
							required: "Please Select Role Status"
				}
						
	   		}
		})
		$.validator.addMethod('validdate', function(value, element, param) {
		    var matches = value.split('/');
		    if (!matches) return;
		    
		    // convert pieces to numbers
		    // make a date object out of it
		    var month = matches[0];
		    var day = matches[1];
		    var year = matches[2];
			if(year.length != 4) {
				return false;
			}
		    var date = new Date(year, month - 1, day);
		    if (!date || !date.getTime()) return;
		    if (date.getMonth() + 1 != month ||
		        date.getFullYear() != year ||
		        date.getDate() != day) {
		            return false;
		        }
		    
		    if(date != undefined) {
				return true;
			} else {
				return false;
			}
			//}
		});
		
		$("#editUser").validate({
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "user_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element) {
		 		$(element).closest('div.control-group').addClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		unhighlight: function(element) {
	    		$(element).closest('div.control-group').removeClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		rules: {
	       		user_firstname: {
		   		required: true,
				minlength: 3,
				maxlength: 200
		 		},
	     		user_lastname: {
	       		required: true,
				minlength: 3,
				maxlength: 200
	     		},
				user_email: {
				required: true,
				email: true
				},
				user_password: {
				//required: true,
				minlength: 6,
				maxlength: 200
				},
				cuserPassword: {
				//required: true,
				minlength: 6,
				maxlength: 200,
				equalTo: "#userPassword"
				},
				user_carrier_id: {
				required: true
				},
				user_status: {
				required: true
				}
	   		},
	   		messages: {
	     		user_firstname: {
	                        required: "Please Enter First Name",
	                        minlength: "First Name must be at least 3 characters long",
							maxlength: "First Name should not exceed 200 characters"
	                    },
	     		user_lastname: {
	                        required: "Please Enter Last Name",
	                        minlength: "Last Name must be at least 3 characters long",
							maxlength: "Last Name should not exceed 200 characters"
	                    },
				user_email: {
							required: "Please Enter the Email Address",
							email: "Please Enter Valid Email Address"
				},
				user_password: {
							//required: "Please Enter User Password",
							minlength: "Password must be at least 6 characters long",
							maxlength: "Password should not exceed 200 characters"
				},
				cuserPassword: {
							required: "Please Enter User Confirm Password",
							minlength: "Confirm Password must be at least 6 characters long",
							maxlength: "Confirm Password should not exceed 200 characters",
							equalTo: "Confirm Password does not match with the Password"
				},
				user_carrier_id: {
				required: "Please Select Carrier"
				},
				user_status: {
				required: "Please Select User Status"
				}
						
	   		}
		})
		$("#Filter").validate({
		 	debug: false,
		    ignore: '',
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "carrierStatus") {
	       		error.insertAfter("#radioError");
				} else if(element.attr("name") == "carrierDescription"){
				error.insertAfter("#descError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element, errorClass, validClass) {
		 		$(element).closest('div.control-group').addClass("error");
				if(element.id == 'carrierDescription') {
					$('.cleditorMain').css('border', '1px solid #B94A48');
				}
	  			},
	   		unhighlight: function(element, errorClass, validClass) {
	    		$(element).closest('div.control-group').removeClass("error");
				if(element.id == 'carrierDescription') {
					$('.cleditorMain').css('border', '1px solid #999999');
				}
	  			},
	   		rules: {
	       		Keyword: {
		   		required: false,
				maxlength: 50
		 		},
				selectOption: {
				required: {
				depends: function(element) {
				var keyExist =  $.trim($('#Keyword').val());
				if((keyExist == 'Enter Keyword')||(keyExist == ''))  {
				return false;
				} else {
				return true; }
				}
				}
				},
				selectLanguageOption: {
				required: {
				depends: function(element) {
				var keyExist =  $.trim($('#Keyword').val());
				if((keyExist == 'Enter Keyword')||(keyExist == ''))  {
				return false;
				} else {
				return true; }
				}
				}
				}
	   		},
	   		messages: {
	     		Keyword: {
	            required: "Please Enter the Keyword",
				maxlength: "Keyword should not exceed 50 characters"
	            },
				selectOption: {
				required: "Please Select the Option"
				},
				selectLanguageOption: {
				required: "Please Select the Option"
				}		
	   		}
		})
		
		$("#changePasswordForm").validate({
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				error.insertAfter(element);
			},	
	     	highlight: function(element, errorClass, validClass) {
		 		$(element).closest('div.control-group').addClass("error");
	  			},
	   		unhighlight: function(element, errorClass, validClass) {
	    		$(element).closest('div.control-group').removeClass("error");
	  			},
	   		rules: {
	       		password: {
		   		required: true,
				minlength: 6,
				maxlength: 50
		 		},
				newpassword: {
				required: true,
				minlength: 6,
				maxlength: 50
				},
				confirmpassword: {
				required: true,
				minlength: 6,
				maxlength: 50,
				equalTo: "#newpassword"
				}
	   		},
	   		messages: {
	     		password: {
	                        required: "Please Enter the Old Password",
	                        minlength: "Old Password must be at least 6 characters long",
							maxlength: "Old Password should not exceed 50 characters"
	            },
				newpassword: {
	                        required: "Please Enter the New Password",
	                        minlength: "New Password must be at least 6 characters long",
							maxlength: "New Password should not exceed 50 characters"
	            },
				confirmpassword: {
	                        required: "Please Enter the Confirm Password",
	                        minlength: "Confirm Password must be at least 6 characters long",
							maxlength: "Confirm Password should not exceed 50 characters",
							equalTo: "Confirm Password should be the same as New Password"
	            }
	   		}
		})
		//	Group
		$("#createGroupForm").validate({
		    ignore: '',
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "group_status") {
	       		error.insertAfter("#radioError");
				} else if(element.attr("name") == "carrierDescription"){
				error.insertAfter("#descError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element, errorClass, validClass) {
		 		$(element).closest('div.control-group').addClass("error");
				if(element.id == 'carrierDescription') {
					$('.cleditorMain').css('border', '1px solid #B94A48');
				}
	  			},
	   		unhighlight: function(element, errorClass, validClass) {
	    		$(element).closest('div.control-group').removeClass("error");
				if(element.id == 'carrierDescription') {
					$('.cleditorMain').css('border', '1px solid #999999');
				}
	  			},
	   		rules: {
	       		group_name: {
		   		required: true,
				minlength: 3,
				maxlength: 50
		 		},
				group_role: {
				required: true
				},
	     		group_status: {
				required: true
				}
	   		},
	   		messages: {
	     		group_name: {
	            required: "Please Enter the Group Name",
	            minlength: "Group Name must be at least 3 characters long",
				maxlength: "Group Name should not exceed 50 characters"
	            },
				group_role: {
				required: "Please Select the Role"
				},
				group_status: {
				required: "Please Select the Group Status"
				}
						
	   		}
		})
		//	Category
		$("#createCategoryForm").validate({
		    ignore: '',
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "category_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element, errorClass, validClass) {
		 		$(element).closest('div.control-group').addClass("error");
	  			},
	   		unhighlight: function(element, errorClass, validClass) {
	    		$(element).closest('div.control-group').removeClass("error");
	  			},
	   		rules: {
	       		category_name: {
		   		required: true,
				minlength: 3,
				maxlength: 50
		 		},
	     		category_status: {
				required: true
				}
	   		},
	   		messages: {
	     		category_name: {
	            required: "Please Enter the Category Name",
	            minlength: "Category Name must be at least 3 characters long",
				maxlength: "Category Name should not exceed 200 characters"
	            },
				category_status: {
				required: "Please Select the Category Status"
				}
	   		}
		})
		//	Tag
		$("#createTagForm").validate({
		    ignore: '',
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "tag_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element, errorClass, validClass) {
		 		$(element).closest('div.control-group').addClass("error");
	  			},
	   		unhighlight: function(element, errorClass, validClass) {
	    		$(element).closest('div.control-group').removeClass("error");
	  			},
	   		rules: {
	       		tag_name: {
		   		required: true,
				minlength: 3,
				maxlength: 50
		 		},
	     		tag_status: {
				required: true
				}
	   		},
	   		messages: {
	     		tag_name: {
	            required: "Please Enter the Tag Name",
	            minlength: "Tag Name must be at least 3 characters long",
				maxlength: "Tag Name should not exceed 200 characters"
	            },
				tag_status: {
				required: "Please Select the Tag Status"
				}
	   		}
		})
		//	Media
		$("#createMediaForm").validate({
			debug: false,
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "media_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element, errorClass, validClass) {
		 		$(element).closest('div.control-group').addClass("error");
	  			},
	   		unhighlight: function(element, errorClass, validClass) {
	    		$(element).closest('div.control-group').removeClass("error");
	  			},
	   		rules: {
	       		media_title: {
		   		required: true,
				minlength: 3,
				maxlength: 300
		 		},
				media_url: {
		   		required: true,
				url: true
		 		},
				media_category: {
				required: true
				},
	     		media_status: {
				required: true
				}
	   		},
	   		messages: {
	     		media_title: {
	            required: "Please Enter the Media Title",
	            minlength: "Media Title must be at least 3 characters long",
				maxlength: "Media Title should not exceed 300 characters"
	            },
				media_url: {
	            required: "Please Enter the Media URL",
				url: "Media URL is invalid"
	            },
				media_category: {
	            required: "Please select the Category"
	            },
				media_status: {
				required: "Please Select the Visibility Status"
				}
	   		},
			/*	submitHandler: function(form) {
				var result	= validateDesc();
				if(result) {
					form.submit();
				}
			}	*/
			submitHandler: function(form) {
				var result	= validateDesc();
				if(result) {
					form.submit();
				}
			}
		})
		
		$.validator.addMethod('accept', function(value, element, param) {
	        var truncate = value.split(".");
	        value = truncate[1];
	        param = typeof param == "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
	        return this.optional(element) || value.match(new RegExp("^(" + param + ")$", "i"));
	    });
	  	$.validator.addMethod('filesize', function(value, element, param) {
		    return this.optional(element) || (element.files[0].size <= param) 
		});
		
		$.validator.addMethod('accept', function(value, element, param) {
	        var truncate = value.split(".");
	        value = truncate[1];
	        param = typeof param == "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
	        return this.optional(element) || value.match(new RegExp("^(" + param + ")$", "i"));
	    });
	  	$.validator.addMethod('filesize', function(value, element, param) {
		    return this.optional(element) || (element.files[0].size <= param) 
		});
		
		
	      $("#siteSettings").validate({
		  	ignore: '',
			errorClass: "help-inline",
			errorElement: "span",
			errorPlacement: function(error, element) {
				if (element.attr("name") == "media_status") {
	       		error.insertAfter("#radioError");
				}
				else {
				error.insertAfter(element);
				}
				},	
	     	highlight: function(element) {
		 		$(element).closest('div.control-group').addClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		unhighlight: function(element) {
	    		$(element).closest('div.control-group').removeClass("error");
				$("#userSuccessmsg").hide();
	  			},
	   		rules: {
	     		fbappid: {
				required: true
				},
	       		fbkey: {
	       		required: true,
	     		},
				fbapp_name: {
				required: true
				},
				fb_page: {
				url: true
				},
				timezone: {
				required: true
				}
	   		},
	   		messages: {
	     		fbappid: {
				required: "Please Enter the Facebook AppID"
				},
				fbkey: {
				required: "Please Enter the Facebook Secret Key"
				},
				fbapp_name: {
				required: "Please Enter the Facebook App Name"
				},
				fb_page: {
				required: "Please Enter the Facebook Page URL"
				},
				timezone: {
				required: "Please Select the Time Zone"
				}	
	   		}
		})
	});
	
	function setValues(group, firstname, lastname, email, fbuid, gender, status, dob) {
		$('#view_group').html(group);
		$('#view_firstname').html(firstname);
		$('#view_lastname').html(lastname);
		$('#view_email').html(email);
		$('#fbuid_tr').show();
		$('#view_dob_tr').show();
		$('#view_gender_tr').show();
		
		if($.trim(fbuid) != '') {
			$('#fbuid').html(fbuid);
		} else {
			$('#fbuid_tr').hide();
		}
		if($.trim(dob) != '') {
			$('#view_dob').html(dob);
		} else {
			$('#view_dob_tr').hide();
		}
		if($.trim(gender) != '') {
			$('#view_gender').html(gender);
		} else {
			$('#view_gender_tr').hide();
		}
		$('#view_status').html(status);
		$('#innerViewButton').click();
	}
	
	function setMediaValues(title, url, category, description, approval_status, status, approved_user, approved_date, added_date, added_user, tags) {
		$('#view_title').html(title);
		$('#view_url').html(url);
		$('#view_category').html(category);
		$('#view_description').html(description);
		$('#view_tags').html(tags);
		$('#view_approval_status').html(approval_status);
		$('#view_approved_user').html(approved_user);
		$('#view_approved_date').html(approved_date);
		$('#view_added_user').html(added_user);
		$('#view_added_date').html(added_date);
		$('#view_status').html(status);
		
		if(approval_status == 'Pending') {
			$('#view_approved_user_tr').hide();
			$('#view_approved_date_tr').hide();
		} else {
			$('#view_approved_user_tr').show();
			$('#view_approved_date_tr').show();
		}
		if($.trim(tags) != '') {
			$('#view_tags_tr').show();
		} else {
			$('#view_tags_tr').hide();
		}
		$('#innerViewButton').click();
	}
	
	function setGroupValues(name, role, status) {
		$('#view_name').html(name);
		$('#view_role').html(role);
		$('#view_status').html(status);
		$('#innerViewButton').click();
	}
	
	function setRoleValues(name, activity_1, activity_2, activity_3, activity_4, activity_5, activity_6, activity_7, activity_8, activity_9, status) {
		$('#view_name').html(name);
		$('#view_activity_1').html((activity_1 == 0) ? 'No' : 'Yes');
		$('#view_activity_2').html((activity_2 == 0) ? 'No' : 'Yes');
		$('#view_activity_3').html((activity_3 == 0) ? 'No' : 'Yes');
		$('#view_activity_4').html((activity_4 == 0) ? 'No' : 'Yes');
		$('#view_activity_5').html((activity_5 == 0) ? 'No' : 'Yes');
		$('#view_activity_6').html((activity_6 == 0) ? 'No' : 'Yes');
		$('#view_activity_7').html((activity_7 == 0) ? 'No' : 'Yes');
		$('#view_activity_8').html((activity_8 == 0) ? 'No' : 'Yes');
		$('#view_activity_9').html((activity_9 == 0) ? 'No' : 'Yes');
		$('#view_status').html(status);
		$('#innerViewButton').click();
	}
	
	$(window).load(function(){
		if($('#DataTables_Table_0_filter').length > 0) {
			$('#DataTables_Table_0_filter label').css('display', 'none');
		}
	});
	
	function loadDiv(divId, url, sortBy, sortType) {
		if(sortBy != '' && sortBy != 'undefined') {
			url	= url+'/'+sortBy;
		}
		if(sortType != '' && sortType != 'undefined') {
			url	= url+'/'+sortType;
		}
		$.post(url, function(data){
			$('#'+divId).html(data);
		});
	}
	
	function deleteUser(userId, pageNum) {
		if(confirm('Are you sure to delete?')) {
			$.post('/cms/user/delete-user/'+userId, function(data){
				pageNum	= (pageNum == 0) ? 1 : pageNum;
				loadDiv('listingDiv', '/cms/user/view-user/'+pageNum, '', '');
				$('#userSuccessmsg').html('User deleted successfully.');
				$('#userSuccessmsg').show();
				setTimeout("$('#userSuccessmsg').hide('slow')", 3000);
			});
		}
	}
	
	function deleteGroup(groupId, pageNum) {
		if(confirm('Are you sure to delete?')) {
			$.post('/cms/user/delete-group/'+groupId, function(data){
				pageNum	= (pageNum == 0) ? 1 : pageNum;
				loadDiv('listingDiv', '/cms/user/view-group/'+pageNum, '', '');
				$('#userSuccessmsg').html('User Group deleted successfully.');
				$('#userSuccessmsg').show();
				setTimeout("$('#userSuccessmsg').hide('slow')", 3000);
			});
		}
	}
	
	function deleteRole(roleId, pageNum, status) {
		if($.trim(status) == '1') {
			alert('This Role is being assigned to Groups. So, you can\'t delete it.');
		} else {
			if(confirm('Are you sure to delete?')) {
				$.post('/cms/user/delete-role/'+roleId, function(data){
					pageNum	= (pageNum == 0) ? 1 : pageNum;
					loadDiv('listingDiv', '/cms/user/view-role/'+pageNum, '', '');
					$('#userSuccessmsg').html('Role deleted successfully.');
					$('#userSuccessmsg').show();
					setTimeout("$('#userSuccessmsg').hide('slow')", 3000);
				});
			}
		}
	}
	
	function deleteCategory(categoryId, pageNum) {
		if(confirm('Are you sure to delete?')) {
			$.post('/cms/media/delete-category/'+categoryId, function(data){
				pageNum	= (pageNum == 0) ? 1 : pageNum;
				loadDiv('listingDiv', '/cms/media/view-category/'+pageNum, '', '');
				$('#userSuccessmsg').html('Category deleted successfully.');
				$('#userSuccessmsg').show();
				setTimeout("$('#userSuccessmsg').hide('slow')", 3000);
			});
		}
	}
	
	function deleteTag(tagId, pageNum) {
		if(confirm('Are you sure to delete?')) {
			$.post('/cms/media/delete-tag/'+tagId, function(data){
				pageNum	= (pageNum == 0) ? 1 : pageNum;
				loadDiv('listingDiv', '/cms/media/view-tag/'+pageNum, '', '');
				$('#userSuccessmsg').html('Tag deleted successfully.');
				$('#userSuccessmsg').show();
				setTimeout("$('#userSuccessmsg').hide('slow')", 3000);
			});
		}
	}
	
	function deleteMedia(mediaId, pageNum) {
		if(confirm('Are you sure to delete?')) {
			$.post('/cms/media/delete-media/'+mediaId, function(data){
				pageNum	= (pageNum == 0) ? 1 : pageNum;
				loadDiv('listingDiv', '/cms/media/view-media/'+pageNum, '', '');
				$('#userSuccessmsg').html('Media deleted successfully.');
				$('#userSuccessmsg').show();
				setTimeout("$('#userSuccessmsg').hide('slow')", 3000);
			});
		}
	}
	
	function hideElement(id) {
		$('#' + id).hide();
	}
	
	function callPerPage(action, count) {
		action	= '/' + action + '/' + count;
		loadDiv('listingDiv', action, '', '');
	}