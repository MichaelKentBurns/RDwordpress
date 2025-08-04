const InputField = (props) => {
  return (
        <input
            value={props.value}
            onChange={(event) => props.onChange(event)}
            name={props.name}
            className="text-input"
        />
  );
};

export default InputField;