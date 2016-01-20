<?php namespace Bllim\Laravalid;
/**
 * This class is extending \Collective\Html\FormBuilder to make 
 * validation easy for both client and server side. Package convert 
 * laravel validation rules to javascript validation plugins while 
 * using laravel FormBuilder.
 *
 * USAGE: Just pass $rules to Form::open($options, $rules) and use.
 * You can also pass by using Form::setValidation from controller or router
 * for coming first form::open.
 * When Form::close() is used, $rules are reset.
 *
 * NOTE: If you use min, max, size, between and type of input is different from string
 * don't forget to specify the type (by using numeric, integer).
 *
 * @package    Laravel Validation For Client-Side
 * @author     Bilal Gultekin <bilal@bilal.im>
 * @license    MIT
 * @see        Collective\Html\FormBuilder
 * @version    0.9
 */
use Lang;
use App;

class FormBuilder extends \Collective\Html\FormBuilder {

	protected $converter;

	public static $requiredLabel = ' <span class="is-form-required-label">*</span>';

	public function __construct(\Collective\Html\HtmlBuilder $html, \Illuminate\Routing\UrlGenerator $url, $csrfToken, Converter\Base\Converter $converter, \Illuminate\Contracts\View\Factory $view)
	{
		parent::__construct($html, $url, $view, $csrfToken);
		$plugin = \Config::get('laravalid.plugin');
		$this->converter = $converter;
	}

	/**
	 * Set rules for validation
	 *
	 * @param array $rules 		Laravel validation rules
	 *
	 */
	public function setValidation($rules, $formName = null)
	{
		$this->converter()->set($rules, $formName);
	}

	/**
	 * Get binded converter class
	 *
	 * @param array $rules 		Laravel validation rules
	 *
	 */
	public function converter()
	{
		return $this->converter;
	}

	/**
	 * Reset validation rules
	 *
	 */
	public function resetValidation()
	{
		$this->converter()->reset();
	}

	/**
	 * Opens form, set rules
	 *
	 * @param array $rules 		Laravel validation rules
	 *
	 * @see Illuminate\Html\FormBuilder
	 */
	public function open(array $options = array(), $rules = null)
	{
		$this->setValidation($rules);

		if(isset($options['name']))
		{
			$this->converter->setFormName($options['name']);
		}
		else
		{
			$this->converter->setFormName(null);
		}
		
		return parent::open($options);
	}

	/**
	 * Create a new model based form builder.
	 *
	 * @param array $rules 		Laravel validation rules
	 *
	 * @see Illuminate\Html\FormBuilder
	 */
	public function model($model, array $options = array(), $rules = null)
	{
		$this->setValidation($rules);
		return parent::model($model, $options);
	}

	/**
	 * @see Illuminate\Html\FormBuilder
	 */
	public function input($type, $name, $value = null, $options = [])
	{
		$options = $this->converter->convert(Helper::getFormAttribute($name)) + $options;
		return parent::input($type, $name, $value, $options);
	}




	public function postalcode($name, $value = null, $options = [])
	{
		if(isset($options['class'])){
			$options['class'] .= ' postalcodeNL';
		}
		else{
			$options['class'] = 'postalcodeNL';
		}
		$options = $this->converter->convert(Helper::getFormAttribute($name)) + $options;
		return parent::text($name, $value, $options);
	}


	public function makeSet( $label, $input ){
		return '
		<div class="row is-form-row">
			<div class="is-form-label-holder">
				'.$label.'
			</div>
			<div class="is-form-widget-holder">
				'.$input.'
			</div>
        </div>';
	}


	public function textSet($name, $options = [], $value = null){
		return self::inputSet('text', $name, $value, $options);
	}
	public function passwordSet($name, $options = [], $value = null){
		return self::inputSet('password', $name, $value, $options);
	}
	public function emailSet($name, $options = [], $value = null){
		return self::inputSet('email', $name, $value, $options);
	}
	public function telSet($name, $options = [], $value = null){
		return self::inputSet('tel', $name, $value, $options);
	}
	public function numberSet($name, $options = [], $value = null){
		return self::inputSet('number', $name, $value, $options);
	}

	public function dateSet($name, $options = [], $value = null){
		return self::inputSet('date', $name, $value, $options);
	}
	public function datetimeSet($name, $options = [], $value = null){
		return self::inputSet('datetime', $name, $value, $options);
	}
	public function datetimeLocalSet($name, $options = [], $value = null){
		return self::inputSet('datetime-local', $name, $value, $options);
	}

	public function timeSet($name, $options = [], $value = null){
		return self::inputSet('time', $name, $value, $options);
	}
	public function urlSet($name, $options = [], $value = null){
		return self::inputSet('url', $name, $value, $options);
	}
	public function fileSet($name, $options = [], $value = null){
		return self::inputSet('file', $name, $value, $options);
	}

	public function postalcodeSet($name, $options = [], $value = null){
		$customInput = self::postalcode($name, $value, $options);
		return self::inputSet('text', $name, $value, $options, $customInput);
	}


	public function inputSet($type, $name, $value = null, $options = [], $customInput = null){
		$options = $this->converter->convert(Helper::getFormAttribute($name)) + $options;

		$labelText = isset($options['label']) ? $options['label'] : null;
		$requiredLabel = isset($options['data-rule-required']) ? self::$requiredLabel : '';

		$label = self::label($name, $labelText, [], $requiredLabel);
		$input = isset($customInput) ? $customInput : parent::input($type, $name, $value, $options);
		return self::makeSet($label, $input);
	}



	/**
	 * @see Illuminate\Html\FormBuilder
	 */
	public function label($name, $value = null, $options = [], $requiredLabel = ''){
		$this->labels[] = $name;

		$options = $this->html->attributes($options);

		$value = e($this->formatLabel($name, $value));

		return $this->toHtmlString('<label for="' . $name . '"' . $options . ' class="is-form-label">' . $value . $requiredLabel . '</label>');
	}


	/**
	 * @see Illuminate\Html\FormBuilder
	 */
	public function textarea($name, $value = null, $options = [])
	{
		$options = $this->converter->convert(Helper::getFormAttribute($name)) + $options;
		return parent::textarea($name, $value, $options);
	}

	/**
	 * @see Illuminate\Html\FormBuilder
	 */
	public function select($name, $list = [], $selected = null, $options = [])
	{
		$options = $this->converter->convert(Helper::getFormAttribute($name)) + $options;
		return parent::select($name, $list, $selected, $options);
	}

	protected function checkable($type, $name, $value, $checked, $options)
	{
		$options = $this->converter->convert(Helper::getFormAttribute($name)) + $options;
		return parent::checkable($type, $name, $value, $checked, $options);
	}

	/**
	 * Closes form and reset $this->rules
	 * 
	 * @see Illuminate\Html\FormBuilder
	 */
	public function close()
	{
		$this->resetValidation();
		return parent::close();
	}


}