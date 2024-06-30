<?php

/**
 * @package Are You Human
 * @version 1.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2021, SMF Tricks
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 */

if (!defined('SMF'))
	die('No direct access...');

class AreYouHuman
{
	/**
	 * @var array Used to store the referral mostly.
	 */
	private array $options = [
		'human',
		'bot',
		'spammer'
	];

	/**
	 * @var array The answers to the question when using select.
	 */
	private array $select_options = [
		'yes' => 0,
		'no' => 1,
		'maybe' => 2,
	];

	/**
	 * @var bool Use either a select or a checkbox.
	 */
	private bool $use_select = false;

	/**
	 * Load the hooks
	 */
	public function initialize() : void
	{
		add_integration_function('integrate_load_custom_profile_fields', __CLASS__ . '::custom_fields#', false);
		add_integration_function('integrate_register_check', __CLASS__ . '::registerCheck#', false);
	}

	/**
	 * Adds the custom fields to the register view.
	 * @param string $area The area where the custom fields are being added.
	 */
	public function custom_fields(int $user, string $area) : void
	{
		loadLanguage('AreYouHuman/');

		if ($area === 'register') {
			$this->registerFields();
		}
	}

	/**
	 * Inserts a fake custom field in the register/signup view.
	 */
	public function registerFields() : void
	{
		global $context, $txt;

		$this->use_select = (bool) mt_rand(0, 1);
		$_SESSION['areyouhuman'] = $this->options[array_rand($this->options)];
		$_SESSION['areyouhuman_select'] = $this->use_select;

		uksort($this->select_options, function() {
			return mt_rand(-1, 1);
		});

		// Add fake custom field
		$context['custom_fields'][] = [
			'name' => $txt['areyou_' . $_SESSION['areyouhuman']],
			'desc' => $txt['areyou_description'],
			'input_html' => $this->htmlInput(),
			'show_reg' => 1,
		];
	}

	/**
	 * Checks if the user is a human.
	 * @param array $reg_errors The errors, add one if the user is not a human.
	 */
	public function registerCheck(&$regOptions, &$reg_errors) : void
	{
		loadLanguage('AreYouHuman/');

		if (empty($_SESSION['areyouhuman_select']) && (($_SESSION['areyouhuman'] === 'human' && !isset($_POST['areyou_human'])) || isset($_POST['areyou_bot']) || isset($_POST['areyou_spammer']))) {
			$reg_errors[] = ['lang', 'areyou_nothuman'];
		}

		// Now the checkings for the select
		if (!empty($_SESSION['areyouhuman_select'])) {
			if ((isset($_POST['areyou_human']) && $_POST['areyou_human'] != 0) || (isset($_POST['areyou_bot']) && $_POST['areyou_bot'] != 1) || (isset($_POST['areyou_spammer']) && $_POST['areyou_spammer'] != 1)) {
				$reg_errors[] = ['lang', 'areyou_nothuman'];
			}
		}

		unset($_SESSION['areyouhuman']);
		unset($_SESSION['areyouhuman_select']);
	}

	/**
	 * Returns the HTML for the input.
	 */
	private function htmlInput() : string
	{
		global $txt;

		if ($this->use_select) {
			$html = '
			<select  name="areyou_' . $_SESSION['areyouhuman'] . '" id="areyou_' . $_SESSION['areyouhuman'] . '">';

			foreach ($this->select_options as $value => $option) {
				$html .= '
				<option value="' . $option . '">' . $txt['areyou_' . $value] . '</option>';
			}

			$html .= '</select>';

			return $html;
		}

		return '<input type="checkbox" name="areyou_' . $_SESSION['areyouhuman'] . '" id="areyou_' . $_SESSION['areyouhuman'] . '" value="1"> <label for="areyou_' . $_SESSION['areyouhuman'] . '">' . $txt['areyou_yes'] . '</label>';
	}
}