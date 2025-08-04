import SelectField from './SelectField';
import SelectMultipleField from './SelectMultipleField';
import TooltipIcon from './TooltipIcon';
import { HexColorPicker, HexColorInput } from "react-colorful";
import useClickOutside from "./useClickOutside";

const useState = wp.element.useState;
const useRef = wp.element.useRef;
const useCallback = wp.element.useCallback;
const {TextControl, TextareaControl, RangeControl, ToggleControl} = wp.components;

const CardBody = (props) => {

  const popover = useRef();
  const [popOverData, setPopOverData] = useState([]);

  const close = useCallback(() => handlePopOverData(null, null), []);
  useClickOutside(popover, close);

  //Used by the current component
  function handlePopOverData(name, value) {

    if(name === null && value === null){
      setPopOverData([]);
      return;
    }

    setPopOverData(prevPopOverData => {
      return {
        ...prevPopOverData,
        [name]: value,
      };
    });
  }

  return (
      <div className={'settings-card-body'}>

        {props.card.options.map((option, index) => {

          switch(option.type){

            case 'text':
              return (
                    <div className={'option-container'} key={index}>
                      <div className={'option-container-left'}>
                        <label>{option.label}</label>
                        <TextControl
                            value={props.formData[option.name]}
                            onChange={(value) => props.handleChanges(value, option.name)}
                        name={option.name}
                            help={option.help}
                            __nextHasNoMarginBottom={ true }
                            __next40pxDefaultSize={ true }
                        />
                      </div>
                      <div className={'option-container-right'}>
                        <TooltipIcon
                            text={option.tooltip}
                            tooltipLink={option.tooltipLink}
                            name={option.name}
                        />
                      </div>
                    </div>
              );
              break;

            case 'textarea':
              return (
                    <div className={'option-container'} key={index}>
                      <div className={'option-container-left'}>
                        <label>{option.label}</label>
                        <TextareaControl
                            value={props.formData[option.name]}
                            onChange={(value) => props.handleChanges(value, option.name)}
                            name={option.name}
                            help={option.help}
                            rows={6}
                            __nextHasNoMarginBottom={ true }
                        >{props.formData[option.name]}</TextareaControl>
                      </div>
                      <div className={'option-container-right'}>
                        <TooltipIcon
                            text={option.tooltip}
                            tooltipLink={option.tooltipLink}
                            name={option.name}
                        />
                      </div>
                    </div>
              );
              break;

            case 'select':

              return (
                  <div className={'option-container'} key={index}>
                    <div className={'option-container-left'}>
                      <label>{option.label}</label>
                      <SelectField
                          label={option.label}
                          value={props.formData[option.name]}
                          onChange={props.handleReactSelectChanges}
                          name={option.name}
                          selectOptions={option.selectOptions}
                          help={option.help}
                      />
                    </div>
                    <div className={'option-container-right'}>
                      <TooltipIcon
                          text={option.tooltip}
                          tooltipLink={option.tooltipLink}
                          name={option.name}
                      />
                    </div>
                  </div>
              );
              break;

            case 'select-multiple':

              return (
                  <div className={'option-container'} key={index}>
                    <div className={'option-container-left'}>
                      <label>{option.label}</label>
                      <SelectMultipleField
                          label={option.label}
                          value={props.formData[option.name]}
                          onChange={props.handleReactSelectMultipleChanges}
                          name={option.name}
                          selectOptions={option.selectOptions}
                          help={option.help}
                      />
                    </div>
                    <div className={'option-container-right'}>
                      <TooltipIcon
                          text={option.tooltip}
                          tooltipLink={option.tooltipLink}
                          name={option.name}
                      />
                    </div>
                  </div>
              );
              break;

            case 'toggle':
              return (
                    <div className={'option-container'} key={index}>
                      <div className={'option-container-left'}>
                        <label>{option.label}</label>
                        <ToggleControl
                            checked={Number(props.formData[option.name])}
                            onChange={(value) => props.handleToggleChanges(value, option.name)}
                            help={option.help}
                            name={option.name}
                            __nextHasNoMarginBottom={ true }
                        />
                      </div>
                      <div className={'option-container-right'}>
                        <TooltipIcon
                            text={option.tooltip}
                            tooltipLink={option.tooltipLink}
                            name={option.name}
                        />
                      </div>
                    </div>
              );
              break;

            case 'range':
              return (
                    <div className={'option-container'} key={index}>
                      <div className={'option-container-left'}>
                        <label>{option.label}</label>
                        <RangeControl
                            value={props.formData[option.name]}
                            onChange={( value ) => props.handleRangeControlChanges( value, option.name )}
                            name={option.name}
                            min={option.rangeMin}
                            max={option.rangeMax}
                            step={option.rangeStep}
                            initialPosition={Number(props.formData[option.name])}
                            help={option.help}
                            trackColor={'#8D1E77'}
                            __next40pxDefaultSize={true}
                            __nextHasNoMarginBottom={ true }
                        />
                      </div>
                      <div className={'option-container-right'}>
                        <TooltipIcon
                            text={option.tooltip}
                            tooltipLink={option.tooltipLink}
                            name={option.name}
                        />
                      </div>
                    </div>
              );
              break;

            case 'color-picker':
              return (
                    <div className={'option-container'} key={index}>
                      <div className={'option-container-left'}>

                        <label>{option.label}</label>
                        <div className={'color-picker-container'}>

                          <div className={'color-picker-field-container'}>

                            <HexColorInput
                                className="color-picker-input"
                                color={props.formData[option.name]}
                                name={option.name}
                                onChange={(e,a) => {
                                  props.handleReactColorfulChanges(option.name, e)
                                }}
                            />
                            <div className="swatch-container">
                              <div
                                  className="swatch"
                                  style={{ backgroundColor: props.formData[option.name] }}
                                  onClick={() => handlePopOverData(option.name, true)}
                                  name={option.name}
                              />
                            </div>
                            {popOverData[option.name] && (
                                <div className="popover" ref={popover}>
                                  <HexColorPicker
                                      color={props.formData[option.name]}
                                      name={option.name}
                                      onChange={(e, a) => {
                                        props.handleReactColorfulChanges(option.name,
                                            e);
                                      }}
                                  />
                                </div>
                            )}

                          </div>

                          <p className="components-base-control__help">{option.help}</p>

                        </div>

                      </div>
                      <div className={'option-container-right'}>
                        <TooltipIcon
                            text={option.tooltip}
                            tooltipLink={option.tooltipLink}
                            name={option.name}
                        />
                      </div>
                    </div>
              );
              break;

            default:
              break;

          }

        })}

      </div>
  );
};

export default CardBody;