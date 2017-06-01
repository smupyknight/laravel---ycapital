<html style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;" >
	<body class="notification-email-body" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:14px;line-height:1.42857143;color:#333;background-color:#F9F9F9;" >
			<div class="header" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;background-color:#6697c8;line-height:30px;min-height:60px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
				<div class="container" style="max-width:1170px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
					<div class="row" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;margin-right:-15px;margin-left:-15px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
						<div class="col-xs-4" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;margin-top:10px;position:relative;min-height:1px;padding-right:15px;padding-left:15px;float:left;width:33.33333333%;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
							<img src="{{url('assets/frontend/images/alares-logo.png')}}" height="65px" alt="logo" style="height:65px">
						</div>
						<div class="col-xs-5 pull-right header-text" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;position:relative;min-height:1px;padding-right:15px;padding-left:15px;width:41.66666667%;float:right !important;color:#ffffff;text-align:right;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
							Alares Daily Update<br style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" ><?php echo date('l, d F Y'); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="container" style="max-width:1170px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
				<div class="row" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;margin-right:-15px;margin-left:-15px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
					<div class="col-xs-12 header-icon" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;position:relative;min-height:1px;padding-right:15px;padding-left:15px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;width:90px;margin-top:10px;margin-bottom:10px;margin-right:auto;margin-left:auto;float:none;" >
						<img src="{{url('assets/frontend/images/icon2.png')}}" style="width:100%;margin:auto">
					</div>
					<div class="email-content well" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;min-height:20px;margin-bottom:20px;border-width:1px;border-style:solid;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0, 0, 0, .05);box-shadow:inset 0 1px 1px rgba(0, 0, 0, .05);font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;color:#000;background-color:#fff;padding-top:40px;padding-bottom:40px;padding-left:60px;padding-right:60px;border-color:#fff;" >
						@if (isset($data['name']))
						<i style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" ><small style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >Having trouble viewing this email? <a href="{{url('/notification-email')}}" class="email-link" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;color:#2ba6cb;text-decoration:none;" >Click here</a> to view it in your web browser.</small></i>
						<h2 style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-weight:500;line-height:1.1;color:inherit;margin-top:20px;margin-bottom:10px;font-size:30px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >Hi {{$data['name']}},</h2>
						@endif
						<h3 style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-weight:500;line-height:1.1;color:inherit;margin-top:20px;margin-bottom:10px;font-size:24px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
							Your daily court list has been updated. Please <a href="{{url('/client')}}" class="email-link" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;color:#2ba6cb;text-decoration:none;" >click here</a> to view the full list filtered by your custom preferences.
						</h3>
						<div class="btn-container" style="max-width:1170px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;padding-top:20px;padding-bottom:20px;text-align:center;" >
							<a href="{{url('/client')}}" class="btn btn-lg btn-email" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding-top:10px;padding-bottom:10px;padding-right:16px;padding-left:16px;font-size:18px;line-height:1.3333333;border-radius:6px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;background-color:#6697c8;color:#fff;" >View Custom List</a>
						</div>
						<p style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;margin-top:0;margin-bottom:10px;margin-right:0;margin-left:0;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
							Remember you can purchase court lists all states <a href="{{url('contact-us')}}" class="email-link" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;color:#2ba6cb;text-decoration:none;" >here</a>.
						</p>
					</div>
				</div>
			</div>
			<div class="container" style="max-width:1170px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
				<div class="row" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;margin-right:-15px;margin-left:-15px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
					<div class="footer-text" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;text-align:center;margin-bottom:10px;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
							@if (isset($data['name']))
							To unsubscribe, <a href="{{url('contact-us')}}" class="email-link" style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;color:#2ba6cb;text-decoration:none;" >click here</a><br style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
							@endif
							admin@alares.com.au<br style="-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:'HelveticaNeue-Light','Helvetica Neue Light','Helvetica Neue',Helvetica,Arial,'Lucida Grande',sans-serif;" >
					</div>
				</div>
			</div>
	</body>
</html>