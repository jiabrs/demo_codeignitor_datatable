<?php

/**
 * Wraps contents in std field row container
 *
 * @param string $contents Contents of field row (label, input, field error, etc.)
 * @return string
 */
function std_field_row($contents = '')
{
	return '<div class="field_row">'.$contents.'<div class="flt_clr"></div></div>';
}

/**
 * Wraps field content in fieldset for radio and checkboxes
 *
 * @param string $contents
 * @param string $legend
 *
 * @return string
 */
function std_fieldset_row($contents = '', $legend = '')
{
	$form = form_fieldset($legend, array('class'=>'field_row'));
	$form .= $contents;
	return $form.'</fieldset>';
}

/**
 * Wraps group of fields (radio or checkbox) in div container
 *
 * @param string $content
 *
 * @return string
 */
function std_field_grouping($content = '')
{
	return '<div class="field_grouping">'.$content.'</div>';
}
/**
 * Creates field error for std field template
 *
 * @param string $error Field error
 * @return string
 */
function std_field_error($error = '')
{
	return '<br />'.$error;
}

/**
 * Creates input field wrapped in std form markup
 *
 * @param string $name field name
 * @param string $label field label
 * @param string $value field value
 * @param string $error field validation error
 * @param array $attr custom attributes for input field
 * @return string
 */
function std_form_input($name = '', $label = '', $value = '', $error = '', $attr = array())
{
	// init $form var
	$form = '';

	// Add field label
	if ($label != '' && $name != '') $form .= form_label($label, $name, array('class'=>'preField'));

	// Add input field
	if ($name != '')
	{
		$form .= form_input(
		array(
				'name' => $name,
				'id' => $name,
				'value' => $value
		) + $attr // add in optional attributes
		);
	}

	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field row container and return
	return std_field_row($form);
}

/**
 * Creates password field wrapped in std form markup
 *
 * @param string $name
 * @param string $label
 * @param string $value
 * @param string $error
 * @param array $attr
 * @return string
 */
function std_form_password($name = '', $label = '', $value = '', $error = '', $attr = array())
{
	// init $form var
	$form = '';

	// Add field label
	if ($label != '' && $name != '') $form .= form_label($label, $name, array('class'=>'preField'));

	// Add input field
	if ($name != '')
	{
		$form .= form_password(
		array(
				'name' => $name,
				'id' => $name,
				'value' => $value
		) + $attr // add in optional attributes
		);
	}

	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field row container and return
	return std_field_row($form);
}

/**
 * Creates textarea field wrapped in std form markup
 *
 * @param string $name field name
 * @param string $label field label
 * @param string $value field value
 * @param string $error field validation error
 * @param array $attr custom attributes for input field
 * @return string
 */
function std_form_textarea($name = '', $label = '', $value = '', $error = '', $attr = array())
{
	// init $form var
	$form = '';

	// Add field label
	if ($label != '' && $name != '') $form .= form_label($label, $name, array('class'=>'preField'));

	// Add input field
	if ($name != '')
	{
		$form .= form_textarea(
		array(
				'name' => $name,
				'id' => $name,
				'value' => $value
		) + $attr // add in option attributes
		);
	}

	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field row container and return
	return std_field_row($form);
}

/**
 * Creates dropdown wrapped in std form markup
 *
 * @param string $name field name
 * @param string $label field label
 * @param array $options associative array of options
 * @param string|array $selected dropdown options
 * @param string $error field validation error
 * @param array $attr custom attributes for input field
 * @return string
 */
function std_form_dropdown($name = '', $label = '', $options = array(), $selected = '', $error = '', $attr = array())
{
	// init $form var
	$form = '';

	// Add field label
	if ($label != '' && $name != '') $form .= form_label($label, $name, array('class'=>'preField'));

	$attr_str = 'id="'.$name.'"';

	// parse out optional attributes into string
	if (is_array($attr))
	{
		foreach ($attr as $key => $val)
		{
			$attr_str .= ' '.$key.'="'.$val.'"';
		}
	}

	// Add dropdown
	if ($options !== FALSE)
	{
		$form .= form_dropdown($name, $options, $selected, $attr_str);
	}

	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field row container and return
	return std_field_row($form);
}

/**
 * Creates fieldset of radio options
 *
 * @param string $name Field name.  Do NOT include braces ([])!
 * @param string $label Label for field row
 * @param array $options Radio input options
 * @param string $selected Selected option
 * @param string $error
 *
 * @return string
 */
function std_form_radio($name = '', $label = '', $options = array(), $selected = '', $error = '')
{
	// init $form var
	$form = '';

	$lbl_len = strlen(implode('',$options));

	foreach ($options as $val => $dsp)
	{
		// add radio for input
		$form .= form_radio(array(
				'name' => $name,
				'id' => $name.'_'.$val,
				'value' => $val,
				'checked' => ($val == $selected) ? TRUE : FALSE
		)
		);

		// add label for radio option
		$form .= form_label($dsp, $name.'_'.$val);

		if ($lbl_len > 40)
		{
			$form .= '<br />';
		}
	}

	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field grouping and then in fieldset.  return
	return std_fieldset_row(std_field_grouping($form), $label);
}

/**
 * Creates fieldset of checkbox options
 *
 * @param string $name Field name.  Do NOT include braces ([])!
 * @param string $label label for fieldset row
 * @param array $options Checkbox input options
 * @param array $selected Selected checkbox input options
 * @param string $error field error
 * @param bool $sel_ctrls TRUE adds js select all/unselect all options
 *
 * @return string
 */
function std_form_checkbox($name = '', $label = '', $options = array(), $selected = array(), $error = '', $sel_ctrls = FALSE)
{
	// init $form var
	$form = '';

	// add js controls
	if ($sel_ctrls)
	{
		$form .= '<a class="ui_uncheck" title="Unselect All" href="#">Unselect All</a><a class="ui_check" title="Select All" href="#">Select All</a>';
	}

	foreach ($options as $val => $dsp)
	{
		// add radio for input
		$form .= form_checkbox(array(
				'name' => $name.'[]',
				'id' => $name.'_'.$val,
				'value' => $val,
				'checked' => (is_array($selected) && in_array($val, $selected)) ? TRUE : FALSE
		)
		);

		// add label for radio option
		$form .= form_label($dsp, $name.'_'.$val);

		if (count($options) > 2)
		{
			$form .= '<br />';
		}
	}
	
	$form = std_field_grouping($form);
	
	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field grouping and then in fieldset.  return
	return std_fieldset_row($form, $label);
}

/**
 * Creates std form upload control
 *
 * @param string $name Field name
 * @param string $label Field label
 * @param string $error Field validation error
 * @param string $class css class
 *
 * @return string
 */
function std_form_upload($name = '', $label = '', $error = '', $class = '')
{
	// init $form var
	$form = '';

	// Add field label
	if ($label != '' && $name != '') $form .= form_label($label, $name, array('class'=>'preField'));

	// Add input field
	if ($name != '')
	{
		$form .= form_upload(
		array(
				'name' => $name,
				'id' => $name,
				'class' => $class
		)
		);
	}

	// Add field error
	if ($error != '') $form .= std_field_error($error);

	// wrap in field row container and return
	return std_field_row($form);
}

/**
 * Creates action submit buttons and returns them wrapped in
 * action container.
 *
 * @param string $name Field names
 * @param string $pri_action Primary action value
 * @param string|array $sec_actions array or string of secondary action values
 * @param string $error
 * @return string
 */
function std_form_actions($name = '', $pri_action = '', $sec_actions = '', $error = '')
{
	$form = '<div class="actions">';
	$form .= form_submit(array(
			'name' => $name,
			'value' => $pri_action,
			'class' => 'primaryAction'
		)
	);

	if (is_array($sec_actions))
	{
		foreach ($sec_actions as $sec_action)
		{
			$form .= form_submit(array(
					'name' => $name,
					'value' => $sec_action,
					'class' => 'secondAction'
				)
			);
		}
	}
	elseif ($sec_actions != '')
	{
		$form .= form_submit(array(
				'name' => $name,
				'value' => $sec_actions,
				'class' => 'secondAction'
			)
		);
	}

	if ($error != '') $form .= '<br /><span class="form_error">'.$error.'</span>';

	$form .= '</div>';

	return $form;
}

/**
 * Adds a field that just shows text and contains value in 
 * hidden text
 * 
 * @param string $name
 * @param string $label
 * @param string $value
 * @param string $display
 * @return string
 */
function std_form_hiddentext($name = '', $label = '', $value = '', $display = '')
{
	// init $form var
	$form = '';

	// Add field label
	if ($label != '' && $name != '') $form .= form_label($label, $name, array('class'=>'preField'));
	
	// Add Field display text
	if ($display != '') $form .= '<span class="dsp_value">'.$display.'</span>';
	
	// Add hidden field
	if ($name != '')
	{
		$form .= form_hidden($name, $value);
	}

	// wrap in field row container and return
	return std_field_row($form);
}

function std_form_hiddenmulti($name = '', $label = '', $values = array())
{
	// init $form var
	$form = '';

	foreach ($values as $value => $display)
	{
		
		// add radio for input
		$form .= form_hidden($name.'[]',$value);

		// add label for radio option
		$form .= form_label($display, $name.'_'.$value).'<br />';
	}

	// wrap in field grouping and then in fieldset.  return
	return std_fieldset_row(std_field_grouping($form), $label);
}