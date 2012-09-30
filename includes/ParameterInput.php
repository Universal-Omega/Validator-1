<?php

/**
 * Simple class to get a HTML input for the parameter.
 * Usable for when creating a GUI from a parameter list.
 * 
 * Based on 'addOptionInput' from Special:Ask in SMW 1.5.6.
 * 
 * TODO: nicify HTML
 * 
 * @since 0.4.6
 * 
 * @file ParameterInput.php
 * @ingroup Validator
 * 
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ParameterInput {
	
	/**
	 * The parameter to print an input for.
	 * 
	 * @since O.5
	 * 
	 * @var IParamDefinition
	 */
	protected $param;
	
	/**
	 * The current value for the parameter. When provided,
	 * it'll be used as value for the input, otherwise the
	 * parameters default value will be used.
	 * 
	 * @since 0.4.6
	 * 
	 * @var mixed: string or false
	 */
	protected $currentValue;
	
	/**
	 * Name for the input.
	 * 
	 * @since 0.6.4
	 * 
	 * @var string
	 */
	protected $inputName;
	
	/**
	 * Constructor.
	 * 
	 * @since 0.4.6
	 * 
	 * @param IParamDefinition|Parameter|array $param
	 * @param mixed $currentValue
	 */
	public function __construct( $param, $currentValue = false ) {
		if ( $param instanceof IParamDefinition ) {
			$this->param = $param;
		}
		elseif ( is_array( $param ) ) {
			$this->param = ParamDefinition::newFromArray( $param );
		}
		else { // Compat code, will go in 1.1
			$this->param = ParamDefinition::newFromParameter( $param );
		}

		$this->currentValue = $currentValue;
		$this->inputName = $param->getName();
	}
	
	/**
	 * Sets the current value.
	 * 
	 * @since 0.4.6
	 * 
	 * @param mixed $currentValue
	 */	
	public function setCurrentValue( $currentValue ) {
		$this->currentValue = $currentValue;
	}
	
	/**
	 * Sets the name for the input; defaults to the name of the parameter.
	 * 
	 * @since 0.6.4
	 * 
	 * @param string $name
	 */
	public function setInputName( $name ) {
		$this->inputName = $name;
	}
	
	/**
	 * Returns the HTML for the parameter input.
	 * 
	 * @since 0.4.6
	 * 
	 * @return string
	 */
	public function getHtml() {
		$valueList = array();

		if ( is_array( $this->param->getAllowedValues() ) ) {
			$valueList[] = $this->param->getAllowedValues();
		}

        if ( count( $valueList ) > 0 ) {
        	$html = $this->param->isList() ? $this->getCheckboxListInput( $valueList ) : $this->getSelectInput( $valueList );
        }
        else {
			switch ( $this->param->getType() ) {
				case 'char':
				case 'float':
				case 'integer':
				case 'number':
					$html = $this->getNumberInput();
					break;
				case 'boolean':
					$html = $this->getBooleanInput();
					break;
				case 'string':
				default:
					$html = $this->getStrInput();
					break;					
			}
        }
        
        return $html;
	}
	
	/**
	 * Returns the value to initially display with the input.
	 * 
	 * @since 0.4.6
	 * 
	 * @return string
	 */
	protected function getValueToUse() {
		$value = $this->currentValue === false ? $this->param->getDefault() : $this->currentValue;
		
		if ( $this->param->isList() && is_array( $value ) ) {
			$value = implode( $this->param->getDelimiter(), $value );
		}
		
		return $value;
	}
	
	/**
	 * Gets a short text input suitable for numbers.
	 * 
	 * @since 0.4.6
	 * 
	 * @return string
	 */		
	protected function getNumberInput() {
		return Html::input(
			$this->inputName,
			$this->getValueToUse(),
			'text',
			array(
				'size' => 6
			)
		);
	}
	
	/**
	 * Gets a text input for a string.
	 * 
	 * @since 0.4.6
	 * 
	 * @return string
	 */		
	protected function getStrInput() {
		return Html::input(
			$this->inputName,
			$this->getValueToUse(),
			'text',
			array(
				'size' => 32
			)
		);
	}
	
	/**
	 * Gets a checkbox.
	 * 
	 * @since 0.4.6
	 * 
	 * @return string
	 */	
	protected function getBooleanInput() {
		return Xml::check(
			$this->inputName,
			$this->getValueToUse()
		);
	}	
	
	/**
	 * Gets a select menu for the provided values.
	 * 
	 * @since 0.4.6
	 * 
	 * @param array $valueList
	 * 
	 * @return string
	 */	
	protected function getSelectInput( array $valueList ) {
		$options = array();
		$options[] = '<option value=""></option>';
		
		$currentValues = (array)$this->getValueToUse();
		if ( is_null( $currentValues ) ) $currentValues = array(); 
		
		foreach ( $valueList as $value ) {
			$options[] =
				'<option value="' . htmlspecialchars( $value ) . '"' .
					( in_array( $value, $currentValues ) ? ' selected' : '' ) . '>' . htmlspecialchars( $value ) .
				'</option>';
		}

		return Html::rawElement(
			'select',
			array(
				'name' => $this->inputName
			),
			implode( "\n", $options )
		);
	}
	
	/**
	 * Gets a list of input boxes for the provided values.
	 * 
	 * @since 0.4.6
	 * 
	 * @param array $valueList
	 * 
	 * @return string
	 */
	protected function getCheckboxListInput( array $valueList ) {
		$boxes = array();

		$currentValues = (array)$this->getValueToUse();
		if ( is_null( $currentValues ) ) $currentValues = array(); 		
		
		foreach ( $valueList as $value ) {
			$boxes[] = Html::rawElement(
				'span',
				array(
					'style' => 'white-space: nowrap; padding-right: 5px;'
				),
				Xml::check(
					$this->inputName . '[' . htmlspecialchars( $value ). ']',
					in_array( $value, $currentValues )
				) .
				Html::element( 'tt', array(), $value )
			);
		}
		
		return implode( "\n", $boxes );
	}
	
}
