<?php

declare(strict_types=1);

namespace atk4\ui\FormField;

use atk4\ui\Form;
use atk4\ui\jsExpression;
use atk4\ui\jsFunction;

/**
 * Input element for a form field.
 */
class DropDown extends Input
{
    /**
     * Values need for the dropdown.
     *  Note: Now possible to display icon with value in dropdown by passing the
     *        icon class with your values.
     * ex: 'values'  => [
     *          'tag'        => ['Tag', 'icon' => 'tag icon'],
     *          'globe'      => ['Globe', 'icon' => 'globe icon'],
     *          'registered' => ['Registered', 'icon' => 'registered icon'],
     *          'file'       => ['File', 'icon' => 'file icon']
     *          ].
     *
     *
     * @var array
     */
    public $values = [];

    /**
     * The string to set as an empty values.
     *
     * @var string
     */
    public $empty = '...';

    /**
     * Whether or not this dropdown required a value.
     *  when set to true, $empty is shown on page load
     *  but is not selectable once a value has been choosen.
     *
     * @var bool
     */
    public $isValueRequired = false;

    /**
     * The html template associate whit this dropdown.
     *
     * @var string
     */
    public $defaultTemplate = 'form/control/dropdown.html';

    /**
     * The css class associate with this dropdown.
     *
     * @var string
     */
    public $defaultClass = 'fluid search selection dropdown';

    /**
     * The icon to display at the dropdown menu.
     *  The template default is set to: 'dropdown icon'.
     *  Note: dropdown icon is show on the right side of the menu
     *  while other icon are usually display on the left side.
     *
     * @var string|null
     */
    public $dropIcon = null;

    /**
     * Dropdown options as per semantic-ui dropdown options.
     *
     * @var array
     */
    public $dropdownOptions = [];

    /**
     * Whether or not to accept multiple value.
     *   Multiple values are sent using a string with comma as value delimiter.
     *   ex: 'value1,value2,value3'.
     *
     * @var bool
     */
    public $isMultiple = false;


    /**
     * Default settings for Dropdown and Autocomplete Semantic UI components.
     */
    public static function getDefaultDropdownSettings(bool $forAutocomplete): array {
        $options = [
            'selectOnKeydown' => false,
            'forceSelection' => false,
            // fix: remove search term after dropdown close, needed with forceSelection = false
            'onHide' => new JsFunction([], [new JsExpression('$(this).dropdown(\'remove searchTerm\');')]),
            // do not force direction, otherwise the content can be shown below the actual viewport ('direction' => 'downward')
            'duration' => 100];

        if (!$forAutocomplete) {
            $options = array_merge($options, [
                'minCharacters' => 0,
                'fullTextSearch' => true, // needed for diacritics, exact is strictly exact 'exact'
                'match' => 'text'
            ]);
        }

        return $options;
    }
    
    /**
     * Initialization.
     */
    public function init()
    {
        parent::init();

        $this->ui = ' ';
        $this->inputType = 'hidden';

        if (isset($this->field) && $this->field->required) {
            $this->isValueRequired = true;
        }

        $this->dropdownOptions = static::getDefaultDropdownSettings(false);
    }

    /**
     * returns <input .../> tag.
     *
     * @return string
     */
    public function getInput()
    {
        return $this->app->getTag('input', [
            'name'        => $this->short_name,
            'type'        => $this->inputType,
            'id'          => $this->id.'_input',
            'value'       => $this->getValue(),
            'readonly'    => $this->readonly ? 'readonly' : false,
            'disabled'    => $this->disabled ? 'disabled' : false,
        ]);
    }

    /**
     * Set js dropdown() specific option;.
     *
     * @param string $option
     * @param mixed  $value
     */
    public function setDropdownOption($option, $value)
    {
        $this->dropdownOptions[$option] = $value;
    }

    /**
     * Set js dropdown() options.
     *
     * @param array $options
     */
    public function setDropdownOptions($options)
    {
        $this->dropdownOptions = $options;
    }

    /**
     * Renders view.
     */
    protected function renderView(): void
    {
        if ($this->isMultiple) {
            $this->defaultClass = $this->defaultClass.' multiple';
        }

        $this->addClass($this->defaultClass);

        if ($this->readonly || $this->disabled) {
            $this->setDropdownOption('showOnFocus', false);
            $this->setDropdownOption('allowTab', false);
            $this->removeClass('search');
        }

        if ($this->readonly) {
            $this->setDropdownOption('allowTab', false);
            $this->setDropdownOption('onShow', new jsFunction([new jsExpression('return false')]));
        }

        $this->js(true)->dropdown($this->dropdownOptions);

        if ($this->dropIcon) {
            $this->template->trySet('DropIcon', $this->dropIcon);
        }

        $this->template->trySet('DefaultText', $this->empty);

        $options = [];
        if (!$this->isValueRequired && !$this->isMultiple) {
            $options[] = ['div',  'class' => 'item', 'data-value' => '', $this->empty || is_numeric($this->empty) ? [(string) $this->empty] : []];
        }

        if (isset($this->model)) {
            foreach ($this->model as $key => $row) {
                $title = $row->getTitle();
                $item = ['div', 'class' => 'item', 'data-value' => (string) $key, $title || is_numeric($title) ? [(string) $title] : []];
                $options[] = $item;
            }
        } else {
            foreach ($this->values as $key => $val) {
                if (is_array($val)) {
                    if (array_key_exists('icon', $val)) {
                        $val = "<i class='{$val['icon']}'></i>{$val[0]}";
                    }
                }
                $item = ['div', 'class' => 'item', 'data-value' => (string) $key, $val || is_numeric($val) ? [(string) $val] : []];
                $options[] = $item;
            }
        }

        $items = $this->app->getTag('div', [
            'class'       => 'menu',
        ], $options ? [[$options]] : []);

        $this->template->trySetHtml('Items', $items);

        parent::renderView();
    }
}
