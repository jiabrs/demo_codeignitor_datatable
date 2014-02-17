<?php echo '<?xml version="1.0" encoding="ISO-8859-1" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title><?php echo $tab_title; ?></title>
		<?php foreach ($css_files as $css_file): ?>
			<link rel="stylesheet" href="<?php echo base_url().'resource/css/'.$css_file; ?>" type="text/css" />
		<?php endforeach; ?>
		<link rel="stylesheet" href="<?php echo base_url(); ?>resource/css/ctm_cma.css" type="text/css" />
		<?php foreach ($js_files as $js_file): ?>
			<script type="text/javascript" src="<?php echo base_url()."resource/js/".$js_file; ?>"></script>
		<?php endforeach; ?>
	</head>
	<body>
		<div id="header">
			<?php if ($logged_in): ?>
			<ul id="user_opts_list">
				<li><span id="username"><?php echo $fullname; ?></span></li>
				<?php if ($env_set): ?>
				<li class="header_dropdown ui-corner-all">
					<div class="switch_menu"><?php echo $this->authr->role->get_role_nm(); ?><span class="ui-icon ui-icon-triangle-1-s drop_down_icon"></span>
						<div class="tooltip_menu ui-widget ui-corner-all">
							<div class="tooltip-pointer-tr-border"></div>
							<div class="tooltip-pointer-tr"></div>
							<ul class="switch_list ui-widget ui-corner-all">
							<?php foreach ($this->authr->get_roles() as $role): ?>
								<li class="switch_elem">
									<?php echo anchor('user/set_role/'.$role->get_role_id(), $role->get_role_nm()); ?>
								</li>
							<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</li>
				<li class="header_dropdown ui-corner-all">
					<div class="switch_menu"><?php echo $code['app'][$this->session->userdata('app')]; ?><span class="ui-icon ui-icon-triangle-1-s drop_down_icon"></span>
						<div class="tooltip_menu ui-widget ui-corner-all">
							<div class="tooltip-pointer-tr-border"></div>
							<div class="tooltip-pointer-tr"></div>
							<ul class="switch_list">
							<?php foreach ($this->authr->get_apps() as $app_code): ?>
								<li class="switch_elem">
									<?php echo anchor('user/set_app/'.$app_code, $code['app'][$app_code]); ?>
								</li>
							<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</li>
				<?php endif; ?>
				<li class="header_dropdown ui-corner-all"><?php echo anchor('user/logout', 'Logout'); ?></li>
			</ul>
			<?php endif; ?>
			<div id="app_title"><?php echo $app_title; ?></div>
		</div>
		<?php if ($logged_in && $env_set): ?>
		<div id="app_nav">			
			<h3><?php echo anchor('#','Contracts'); ?></h3>
			<div>
				<ul>
					<li><?php echo anchor('contract', 'Contracts'); ?></li>
					<?php if ($this->authr->authorize('MC', NULL, FALSE)): ?>
					<li><?php echo anchor('contract/setup_info', 'Add Contract'); ?></li>
					<?php endif; ?>
				</ul>
			</div>
			<?php if ($this->authr->authorize('MC', NULL, FALSE)): ?>
			<h3><?php echo anchor('#', 'Elements'); ?></h3>
			<div>
				<ul>
					<li><?php echo anchor('element', 'Elements'); ?></li>
					<li><?php echo anchor('element/setup_info', 'Add Element'); ?></li>
				</ul>
			</div>
			<h3><?php echo anchor('#', 'Programs'); ?></h3>
			<div>
				<ul>
					<li><?php echo anchor('program', 'Programs'); ?></li>
					<li><?php echo anchor('program/setup', 'Add Program'); ?></li>
				</ul>
			</div>
			<?php endif; ?> 
			<h3><?php echo anchor('#','Reports'); ?></h3>
			<div>
				<ul>
					<li><?php echo anchor('report/detailed_accrual','Detailed Accrual'); ?></li>
				</ul>
			</div>
			<?php if ($this->session->userdata('app') == 'CM'): ?>
			<h3><?php echo anchor('#','Check Requests'); ?></h3>
			<div>
				<ul>
					<li><?php echo anchor('approval','Approvals'); ?></li>
					<li><?php echo anchor('approval/setup_approval','Add Approval'); ?></li>
				</ul>
			</div>
			<?php endif; ?>
			<?php if ($this->authr->authorize('MU', NULL, FALSE)): ?>
			<h3><?php echo anchor('#','Users'); ?></h3>
			<div>
				<ul>
					<li><?php echo anchor('user', 'Users'); ?></li>
					<li><?php echo anchor('user/lookup', 'Add User'); ?></li>
					<li><?php echo anchor('role', 'Roles'); ?></li>
					<li><?php echo anchor('role/setup', 'Add Role'); ?></li>
				</ul>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>