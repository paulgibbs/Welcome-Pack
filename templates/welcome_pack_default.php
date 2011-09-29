<?php
/*
Email Template: BP Default
*/
?>
 <html lang="en">
  <head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<style type="text/css">
	a:hover { text-decoration: none !important; }
	.header h1{color: #ffffff !important; font: bold 32px Arial, Tahoma, Verdana, sans-serif; margin: 0; padding: 0; line-height: 40px;}
	.header a, .header a:link, .header a:visited {color: #ffffff !important; text-decoration:none;}
	.header p {color: #eeeeee; font: normal 12px Arial, Tahoma, Verdana, sans-serif; margin: 0; padding: 0; line-height: 18px;}
	.content h2 {color:#666 !important; font-weight: bold; margin: 0; padding: 0; line-height: 26px; font-size: 18px; font-family: Arial, Tahoma, Verdana, sans-serif;  }
	.content p {color:#555; font-weight: normal; margin: 0; padding: 0; line-height: 20px; font-size: 12px;font-family: Arial, Tahoma, Verdana, sans-serif;}
	.content a {color: #1FB3DD; text-decoration: none;}
	.footer p {font-size: 11px; color:#555; margin: 0; padding: 0; font-family: Arial, Tahoma, Verdana, sans-serif;}
	.footer a {color: #1FB3DD; text-decoration: none;}
	</style>
  </head>
  <body style="margin: 0; padding: 0; background: #EAEAEA;" bgcolor="#EAEAEA">
  		<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" style="padding: 0 0 35px 0; background: #eaeaea;">
		  <tr>
		  	<td align="center" style="margin: 0; padding: 0; background: #EAEAEA;" >
			    <table cellpadding="0" cellspacing="0" border="0" align="center" width="600" style="font-family: Arial, Tahoma, Verdana, sans-serif; background:#1FB3DD url('<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-headerbackground.jpg' ); ?>') no-repeat;height:150px;margin-bottom:20px;" class="header">
			 		<td width="560" align="left" style="padding: 20px;">
						<h1 style="color: #ffffff; font: bold 32px Arial, Tahoma, Verdana, sans-serif; margin: 0; padding: 0; line-height: 40px;"><a href="<?php echo esc_attr( get_site_url() ); ?>" style="text-decoration:none;" target="_blank"><?php bloginfo( 'name' ); ?></a></h1>
						<p style="color: #c6c6c6; font: normal 12px Arial, Tahoma, Verdana, sans-serif; margin: 0; padding: 0; line-height: 18px;"><?php bloginfo( 'description' ); ?></p>
			        </td>
			      </tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" align="center" width="600" style="font-family: Arial, Tahoma, Verdana, sans-serif;">
			      	<tr>	
			      		<td width="600" valign="top" align="left" style="font-family: Arial, Tahoma, Verdana, sans-serif; background:url('<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-bodytop.jpg' ); ?>') no-repeat;height:10px;" class="content">
			      		</td>
			      	</tr>
					<tr>
			        <td width="600" valign="top" align="left" style="font-family: Arial, Tahoma, Verdana, sans-serif; padding: 0 0;background:#ffffff url('<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-bodymiddle.jpg' ); ?>') repeat-y;" class="content">
						<table cellpadding="0" cellspacing="0" border="0" style="color: #717171; font: normal 12px Arial, Tahoma, Verdana, sans-serif; margin: 0; padding: 0 20px;" width="560">
						<tr>
							<td style="padding: 15px 0 15px;"  valign="top">
								<p style="color:#767676; font-weight: normal; margin: 0; padding: 0; line-height: 20px; font-size: 12px;font-family: Arial, Tahoma, Verdana, sans-serif; ">DPW_CONTENT</p><br>
							</td>
						</tr>
						</table>	
					</td>
			      </tr>
				      <tr>	
				      	<td width="600" valign="top" align="left" style="font-family: Arial, Tahoma, Verdana, sans-serif; background:url('<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-bodybottom.jpg' ); ?>') no-repeat;height:10px;" class="content">
				      	</td>
				      </tr>
				</table>
				<table cellpadding="0" cellspacing="0" border="0" align="center" width="600" style="font-family: Arial, Tahoma, Verdana, sans-serif; line-height: 10px;" class="footer"> 
				<tr>
					<td align="left" style="padding: 5px 0 10px; font-size: 11px; color:#666666; margin: 0; font-family: Arial, Tahoma, Verdana, sans-serif;" valign="top">
						<img style="padding:15px 0 0 0" height="32" width="32" src="<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-buddypresslogo.jpg' ); ?>" align="right" alt="<?php esc_attr_e( 'Powered by BuddyPress', 'dpw' ); ?>" />
						<div style="padding:15px 0 10px 0"><img height="13" width="13" style="vertical-align: middle;" src="<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-dateicon.jpg' ); ?>" alt="<?php esc_attr_e( 'Date', 'dpw' ); ?>" /> <?php printf( __( 'Email sent on %s', 'dpw' ), date_i18n( get_option( 'date_format' ) ) ); ?></div>
						<div><img height="12" width="12" style="vertical-align: middle;" src="<?php echo plugins_url( '/welcome-pack/templates/default/bpdefault-contacticon.jpg' ); ?>" alt="<?php esc_attr_e( 'Contact', 'dpw' ); ?>" /> <?php printf( __( 'For any support, please contact <a href="mailto:%1$s">%1$s</a>', 'dpw' ), esc_attr( get_option( 'admin_email' ) ) ); ?></div>
					</td>
			      </tr>
				</table>
		  	</td>
		  	</td>
		</tr>
    </table>
  </body>
</html>