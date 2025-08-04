import Select from 'react-select';

const SelectField = (props) => {

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

    //get the item in the array "options" that has the value of "value"
    const selectedItem = options.find(function(item) {
      if(item.value === value){
        return item;
      }
    });

    return selectedItem;

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
      borderColor: state.isFocused ? '#0783BE !important' : '#e1e1e1',
      boxShadow: state.isFocused ? '0 0 0 1px #0783BE !important' : '#e1e1e1',
    }),
    input: (baseStyles, state) => ({
      ...baseStyles,
      height: '32px',
    }),
    singleValue: (provided, state) => ({
      ...provided,
    }),
    option: (provided, state) => ({
      ...provided,
      background: state.isFocused ? '#0783BE' : '#ffffff',
      color: state.isFocused ? '#ffffff' : '#3c434a',
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
            styles={styles}
        >
        </Select>
        <p className="components-base-control__help">{props.help}</p>
      </div>
  );

};

export default SelectField;