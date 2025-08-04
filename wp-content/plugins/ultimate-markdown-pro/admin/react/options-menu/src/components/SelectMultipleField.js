import Select from 'react-select';
const useState = wp.element.useState;

const { __ } = wp.i18n;

const SelectMultipleField = (props) => {

  let options = [];
  props.selectOptions.map((selectOption, index) => {
    options.push({value: selectOption.value, label: selectOption.text})
  })

  /**
   *
   * Note: This should be done because the "value" attribute of the react-select
   * component should be an object with the following structure: (and not just
   * a string like the "value" attribute of the "select" HTML element)
   *
   * {
   *  value: 'value',
   *  label: 'label'
   * }
   *
   * @param value
   * @returns {*}
   */
  function getOptionsObject(value){

    let selectedItems = [];

    //check if "value" is an array
    if(Array.isArray(value)){
      value.forEach(function(item, index){
        const selectedItem = options.find(function(item2) {
          if(item2.value === item){
            return item2;
          }
        });
        selectedItems.push(selectedItem);
      });
    }

    return selectedItems;

  }

    /**
     * Customize the style of the react-select component.
     *
     * References for react-select style customizations:
     *
     * - https://stackoverflow.com/questions/54218351/changing-height-of-react-select-component
     * - https://react-select.com/styles
     * - https://react-select.com/styles#inner-components
     *
     * @type {{input: (function(*, *): *&{margin: string}), valueContainer: (function(*, *): *&{padding: string, height: string}), indicatorSeparator: (function(*): {display: string}), control: (function(*, *): *&{minHeight: string, boxShadow: null, height: string}), indicatorsContainer: (function(*, *): *&{height: string})}}
     */
  const styles = {
    control: (provided, state) => ({
      ...provided,
      width: 440, // Set your custom width here
    }),
  };

  return (
      <div className={'react-select-container'}>
        <Select
            value={getOptionsObject(props.value)}
            onChange={(event) => {
              props.onChange(event, props.name)
            }}
            options={options}
            isMulti={true}
            styles={styles}
            placeholder={__('Choose an Option ...', 'ultimate-markdown-pro')}
        >
        </Select>
        <p className="components-base-control__help">{props.help}</p>
      </div>
  );

};

export default SelectMultipleField;